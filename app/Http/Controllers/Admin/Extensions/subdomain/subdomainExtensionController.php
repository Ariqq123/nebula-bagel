<?php

namespace Pterodactyl\Http\Controllers\Admin\Extensions\subdomain;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Http\Requests\Admin\Extensions\Subdomain\SubdomainPostRequest;
use Pterodactyl\Http\Requests\Admin\Extensions\Subdomain\SubdomainPutRequest;
use Pterodactyl\Http\Requests\Admin\Extensions\Subdomain\SubdomainUpdateRequest;
use Pterodactyl\BlueprintFramework\Libraries\ExtensionLibrary\Console\BlueprintConsoleLibrary;
use Pterodactyl\BlueprintFramework\Extensions\subdomain\CloudflareService;
use Pterodactyl\BlueprintFramework\Extensions\subdomain\CloudflareServiceException;
use Pterodactyl\BlueprintFramework\Extensions\subdomain\EncryptionService;
use Pterodactyl\BlueprintFramework\Extensions\subdomain\InputValidator;
use Pterodactyl\BlueprintFramework\Extensions\subdomain\AuditLogger;
use Pterodactyl\BlueprintFramework\Extensions\subdomain\RateLimiter;
use Pterodactyl\Models\Server;
use Illuminate\Contracts\Encryption\DecryptException;

class subdomainExtensionController extends Controller
{
    /**
     * The database table name used by the extension.
     */
    private const TABLE = 'subdomain';

    /**
     * Default cache TTL in seconds (5 minutes).
     */
    private const DEFAULT_CACHE_TTL = 300;

    /**
     * Default maximum subdomains per server.
     */
    private const DEFAULT_MAX_SUBDOMAINS_PER_SERVER = 5;

    /**
     * Number of records per page for subdomain listing.
     */
    private const RECORDS_PER_PAGE = 20;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        private BlueprintConsoleLibrary $blueprint,
        private CloudflareService $cloudflare,
        private EncryptionService $encryption,
        private InputValidator $validator,
        private AuditLogger $auditLogger,
        private RateLimiter $rateLimiter,
    ) {}

    /**
     * Display the main admin view with tokens, zones, subdomains, and settings.
     *
     * Retrieves configured tokens, loads zone cache (refreshing if expired),
     * loads subdomain mappings with pagination (20/page), and passes all data
     * to the Blade view.
     */
    public function index(): View
    {
        // Retrieve tokens
        $tokensIndex = $this->blueprint->dbGet(self::TABLE, 'cf_tokens_index', []);
        $tokens = [];
        $tokenData = [];

        if (!empty($tokensIndex) && is_array($tokensIndex)) {
            foreach ($tokensIndex as $tokenId) {
                $data = $this->blueprint->dbGet(self::TABLE, 'cf_token_' . $tokenId);
                if ($data && is_array($data)) {
                    // Mask the token for display (show only last 4 characters)
                    $data['masked_token'] = $this->maskToken($tokenId, $data);
                    $tokens[] = $data;
                    $tokenData[$tokenId] = $data;
                }
            }
        }

        // If no tokens configured, show setup-required view
        if (empty($tokens)) {
            return view('admin.extensions.subdomain.index', [
                'tokens' => [],
                'zones' => [],
                'records' => [],
                'settings' => $this->getSettings(),
                'setup_required' => true,
                'pagination' => ['current_page' => 1, 'total_pages' => 0, 'total_records' => 0],
                'cache_stale' => false,
                'cache_age' => 0,
            ]);
        }

        // Zone cache logic: check TTL, serve cached or refresh
        $zones = [];
        $cacheStale = false;
        $cacheAge = 0;
        $zoneCacheResult = $this->loadZoneCache($tokenData);
        $zones = $zoneCacheResult['zones'];
        $cacheStale = $zoneCacheResult['stale'];
        $cacheAge = $zoneCacheResult['age'];

        // Load subdomain mappings with pagination
        $page = max(1, (int) request()->input('page', 1));
        $paginatedRecords = $this->loadSubdomainRecords($page);

        return view('admin.extensions.subdomain.index', [
            'tokens' => $tokens,
            'zones' => $zones,
            'records' => $paginatedRecords['records'],
            'settings' => $this->getSettings(),
            'setup_required' => false,
            'pagination' => [
                'current_page' => $paginatedRecords['current_page'],
                'total_pages' => $paginatedRecords['total_pages'],
                'total_records' => $paginatedRecords['total_records'],
            ],
            'cache_stale' => $cacheStale,
            'cache_age' => $cacheAge,
        ]);
    }

    /**
     * Add a new Cloudflare API token.
     *
     * Validates token format, verifies with Cloudflare, checks permissions,
     * detects duplicates, encrypts, stores with UUID key, updates tokens index,
     * and logs the action.
     */
    public function post(SubdomainPostRequest $request): RedirectResponse
    {
        $token = $request->input('token');
        $label = $request->input('label');
        $adminId = $request->user()->id;

        // Validate token format using InputValidator
        $formatResult = $this->validator->validateApiToken($token);
        if (!$formatResult->valid) {
            return redirect()->route('admin.extensions.subdomain.index')
                ->withErrors(['token' => $formatResult->message]);
        }

        // Verify with Cloudflare
        try {
            $verificationResult = $this->cloudflare->verifyToken($token);
        } catch (\Exception $e) {
            $this->auditLogger->log('TOKEN_ADDED', $adminId, [
                'resource_type' => 'token',
                'resource_id' => 'unknown',
                'result' => 'failure',
                'reason' => 'Cloudflare verification service unavailable: ' . $e->getMessage(),
            ]);

            return redirect()->route('admin.extensions.subdomain.index')
                ->withErrors(['token' => 'Cloudflare verification service is unavailable. Please try again later.']);
        }

        if (!$verificationResult->valid) {
            $this->auditLogger->log('TOKEN_ADDED', $adminId, [
                'resource_type' => 'token',
                'resource_id' => 'unknown',
                'result' => 'failure',
                'reason' => $verificationResult->message,
            ]);

            return redirect()->route('admin.extensions.subdomain.index')
                ->withErrors(['token' => $verificationResult->message]);
        }

        // Check for duplicate token (requirement 1.9)
        $tokensIndex = $this->blueprint->dbGet(self::TABLE, 'cf_tokens_index', []);
        if (is_array($tokensIndex)) {
            foreach ($tokensIndex as $existingTokenId) {
                $existingData = $this->blueprint->dbGet(self::TABLE, 'cf_token_' . $existingTokenId);
                if ($existingData && is_array($existingData) && isset($existingData['encrypted_token'])) {
                    try {
                        $decryptedExisting = $this->encryption->decrypt($existingData['encrypted_token']);
                        if ($decryptedExisting === $token) {
                            return redirect()->route('admin.extensions.subdomain.index')
                                ->withErrors(['token' => 'This token is already configured. Duplicate tokens are not allowed.']);
                        }
                    } catch (DecryptException $e) {
                        // Skip corrupted tokens during duplicate check
                        continue;
                    }
                }
            }
        }

        // Encrypt the token
        $encryptedToken = $this->encryption->encrypt($token);

        // Generate UUID for token ID
        $tokenId = (string) Str::uuid();

        // Fetch zones accessible by this token for storage
        $zoneIds = [];
        try {
            $zones = $this->cloudflare->listZones($token);
            $zoneIds = array_column($zones, 'id');
        } catch (CloudflareServiceException $e) {
            // Token is valid but zone listing failed - store anyway with empty zones
            $zoneIds = [];
        }

        // Store token data
        $tokenRecord = [
            'token_id' => $tokenId,
            'encrypted_token' => $encryptedToken,
            'label' => $label,
            'zone_ids' => $zoneIds,
            'permissions' => $verificationResult->permissions,
            'added_by' => $adminId,
            'added_at' => gmdate('Y-m-d\TH:i:s\Z'),
            'last_verified' => gmdate('Y-m-d\TH:i:s\Z'),
        ];

        $this->blueprint->dbSet(self::TABLE, 'cf_token_' . $tokenId, $tokenRecord);

        // Update tokens index
        $tokensIndex = $this->blueprint->dbGet(self::TABLE, 'cf_tokens_index', []);
        if (!is_array($tokensIndex)) {
            $tokensIndex = [];
        }
        $tokensIndex[] = $tokenId;
        $this->blueprint->dbSet(self::TABLE, 'cf_tokens_index', $tokensIndex);

        // Audit log
        $this->auditLogger->log('TOKEN_ADDED', $adminId, [
            'resource_type' => 'token',
            'resource_id' => $tokenId,
            'result' => 'success',
            'label' => $label,
        ]);

        return redirect()->route('admin.extensions.subdomain.index')
            ->with('success', 'Cloudflare API token has been successfully configured.');
    }

    /**
     * Create a new subdomain DNS record.
     *
     * Rate limit check, validate all inputs (subdomain, record_type, target,
     * zone_id, server_id), check per-server subdomain limit, create DNS record
     * via CloudflareService, store mapping, update zone/server indexes, audit log.
     */
    public function put(SubdomainPutRequest $request): RedirectResponse
    {
        $adminId = $request->user()->id;

        // Rate limit check
        $rateLimitResult = $this->rateLimiter->checkLimit($adminId, 'subdomain_create');
        if (!$rateLimitResult->allowed) {
            return redirect()->route('admin.extensions.subdomain.index')
                ->withErrors(['subdomain' => 'Too many requests. Please try again in ' . $rateLimitResult->secondsUntilReset . ' seconds.'])
                ->setStatusCode(429);
        }

        $subdomain = $request->input('subdomain');
        $recordType = $request->input('record_type');
        $target = $request->input('target');
        $zoneId = $request->input('zone_id');
        $serverId = $request->input('server_id');
        $proxied = (bool) $request->input('proxied', false);
        $ttl = (int) $request->input('ttl', 1);

        // Validate zone_id format
        $zoneIdResult = $this->validator->validateZoneId($zoneId);
        if (!$zoneIdResult->valid) {
            return redirect()->route('admin.extensions.subdomain.index')
                ->withErrors(['zone_id' => $zoneIdResult->message]);
        }

        // Validate record type
        $recordTypeResult = $this->validator->validateRecordType($recordType);
        if (!$recordTypeResult->valid) {
            return redirect()->route('admin.extensions.subdomain.index')
                ->withErrors(['record_type' => $recordTypeResult->message]);
        }

        // Validate target based on record type
        $targetResult = $this->validator->validateTarget($target, $recordType);
        if (!$targetResult->valid) {
            return redirect()->route('admin.extensions.subdomain.index')
                ->withErrors(['target' => $targetResult->message]);
        }

        // Get zone name for FQDN validation
        $zoneName = $this->getZoneNameById($zoneId);

        // Validate subdomain name (with sanitization applied first per requirement 9.8)
        $wildcardAllowed = (bool) $this->blueprint->dbGet(self::TABLE, 'settings_wildcard_allowed', false);
        $subdomainResult = $this->validator->validateSubdomainName($subdomain, $wildcardAllowed, $zoneName);
        if (!$subdomainResult->valid) {
            return redirect()->route('admin.extensions.subdomain.index')
                ->withErrors(['subdomain' => $subdomainResult->message]);
        }

        // Sanitize subdomain name for storage
        $subdomain = $this->validator->sanitizeSubdomainName($subdomain);

        // Validate server_id exists in Pterodactyl panel (requirement 10.6, 10.7)
        if ($serverId !== null) {
            $server = Server::find($serverId);
            if (!$server) {
                return redirect()->route('admin.extensions.subdomain.index')
                    ->withErrors(['server_id' => 'The specified server was not found in the panel.']);
            }
            $serverName = $server->name;

            // Check per-server subdomain limit (default 5)
            $maxSubdomainsPerServer = (int) $this->blueprint->dbGet(
                self::TABLE,
                'settings_max_subdomains_per_server',
                self::DEFAULT_MAX_SUBDOMAINS_PER_SERVER
            );
            $serverRecords = $this->blueprint->dbGet(self::TABLE, 'server_' . $serverId . '_records', []);
            if (is_array($serverRecords) && count($serverRecords) >= $maxSubdomainsPerServer) {
                return redirect()->route('admin.extensions.subdomain.index')
                    ->withErrors(['server_id' => 'The per-server subdomain limit of ' . $maxSubdomainsPerServer . ' has been reached for this server.']);
            }
        } else {
            $serverName = null;
        }

        // Find the token that has access to this zone
        $tokenId = $this->findTokenForZone($zoneId);
        if ($tokenId === null) {
            return redirect()->route('admin.extensions.subdomain.index')
                ->withErrors(['zone_id' => 'No configured token has access to the selected zone.']);
        }

        // Decrypt the token
        $tokenData = $this->blueprint->dbGet(self::TABLE, 'cf_token_' . $tokenId);
        if (!$tokenData || !is_array($tokenData) || !isset($tokenData['encrypted_token'])) {
            return redirect()->route('admin.extensions.subdomain.index')
                ->withErrors(['zone_id' => 'The token associated with this zone is unavailable.']);
        }

        try {
            $plainToken = $this->encryption->decrypt($tokenData['encrypted_token']);
        } catch (DecryptException $e) {
            return redirect()->route('admin.extensions.subdomain.index')
                ->withErrors(['zone_id' => 'The token associated with this zone is corrupted and cannot be decrypted. Please remove and re-add the token.']);
        }

        // Build the full DNS record name
        $fullName = $subdomain . '.' . $zoneName;

        // Create DNS record via CloudflareService
        $this->rateLimiter->recordApiCall();
        $dnsResult = $this->cloudflare->createDnsRecord(
            $plainToken,
            $zoneId,
            $fullName,
            $recordType,
            $target,
            $proxied,
            $ttl
        );

        if (!$dnsResult->success) {
            // Check if it's a conflict (record already exists)
            if ($dnsResult->isConflict()) {
                $this->auditLogger->log('SUBDOMAIN_CREATED', $adminId, [
                    'resource_type' => 'subdomain',
                    'resource_id' => $fullName,
                    'result' => 'failure',
                    'reason' => 'DNS record conflict - subdomain already exists in zone',
                ]);
                return redirect()->route('admin.extensions.subdomain.index')
                    ->withErrors(['subdomain' => 'This subdomain already exists in the selected zone.']);
            }

            // Check for 401/403 — mark token as needs re-verification (requirement 13.5)
            if (str_contains($dnsResult->message, 'HTTP 401') || str_contains($dnsResult->message, 'HTTP 403')) {
                $this->markTokenNeedsReVerification($tokenId);
            }

            $this->auditLogger->log('SUBDOMAIN_CREATED', $adminId, [
                'resource_type' => 'subdomain',
                'resource_id' => $fullName,
                'result' => 'failure',
                'reason' => $dnsResult->message,
            ]);

            return redirect()->route('admin.extensions.subdomain.index')
                ->withErrors(['subdomain' => 'Failed to create DNS record: ' . $dnsResult->message]);
        }

        // Record the subdomain creation for rate limiting
        $this->rateLimiter->recordSubdomainCreation($adminId);

        // Store subdomain record mapping
        $recordId = $dnsResult->recordId;
        $recordMapping = [
            'record_id' => $recordId,
            'zone_id' => $zoneId,
            'zone_name' => $zoneName,
            'subdomain' => $subdomain,
            'full_name' => $fullName,
            'record_type' => $recordType,
            'target' => $target,
            'proxied' => $proxied,
            'ttl' => $ttl,
            'server_id' => $serverId,
            'server_name' => $serverName,
            'token_id' => $tokenId,
            'created_by' => $adminId,
            'created_at' => gmdate('Y-m-d\TH:i:s\Z'),
        ];

        $this->blueprint->dbSet(self::TABLE, 'record_' . $recordId, $recordMapping);

        // Update zone record index
        $zoneRecords = $this->blueprint->dbGet(self::TABLE, 'zone_' . $zoneId . '_records', []);
        if (!is_array($zoneRecords)) {
            $zoneRecords = [];
        }
        $zoneRecords[] = $recordId;
        $this->blueprint->dbSet(self::TABLE, 'zone_' . $zoneId . '_records', $zoneRecords);

        // Update server record index
        if ($serverId !== null) {
            $serverRecords = $this->blueprint->dbGet(self::TABLE, 'server_' . $serverId . '_records', []);
            if (!is_array($serverRecords)) {
                $serverRecords = [];
            }
            $serverRecords[] = $recordId;
            $this->blueprint->dbSet(self::TABLE, 'server_' . $serverId . '_records', $serverRecords);
        }

        // Audit log
        $this->auditLogger->log('SUBDOMAIN_CREATED', $adminId, [
            'resource_type' => 'subdomain',
            'resource_id' => $recordId,
            'result' => 'success',
            'subdomain' => $subdomain,
            'full_name' => $fullName,
            'zone_id' => $zoneId,
            'zone_name' => $zoneName,
            'record_type' => $recordType,
            'target' => $target,
            'server_id' => $serverId,
            'server_name' => $serverName,
        ]);

        return redirect()->route('admin.extensions.subdomain.index')
            ->with('success', 'Subdomain record "' . $fullName . '" has been successfully created.');
    }

    /**
     * Update extension settings.
     *
     * Validates all submitted settings values, rejects the entire batch if any
     * value is invalid, stores valid settings, and audit logs changes with
     * previous and new values.
     */
    public function update(SubdomainUpdateRequest $request): RedirectResponse
    {
        $adminId = $request->user()->id;

        // Define settings constraints
        $settingsConstraints = [
            'rate_limit_per_minute' => ['type' => 'integer', 'min' => 1, 'max' => 120, 'default' => 30],
            'cache_ttl' => ['type' => 'integer', 'min' => 60, 'max' => 3600, 'default' => 300],
            'max_subdomains_per_server' => ['type' => 'integer', 'min' => 1, 'max' => 50, 'default' => 5],
            'wildcard_allowed' => ['type' => 'boolean', 'default' => false],
        ];

        $submittedSettings = $request->only(array_keys($settingsConstraints));
        $errors = [];
        $changes = [];

        // Validate all submitted settings
        foreach ($submittedSettings as $key => $value) {
            if ($value === null) {
                continue;
            }

            $constraint = $settingsConstraints[$key];

            if ($constraint['type'] === 'integer') {
                if (!is_numeric($value)) {
                    $errors[$key] = "The {$key} must be a numeric value.";
                    continue;
                }
                $intValue = (int) $value;
                if ($intValue < $constraint['min']) {
                    $errors[$key] = "The {$key} must be at least {$constraint['min']}.";
                    continue;
                }
                if ($intValue > $constraint['max']) {
                    $errors[$key] = "The {$key} must not exceed {$constraint['max']}.";
                    continue;
                }
                $changes[$key] = $intValue;
            } elseif ($constraint['type'] === 'boolean') {
                if (!in_array($value, [true, false, 0, 1, '0', '1', 'true', 'false', 'enabled', 'disabled'], true)) {
                    $errors[$key] = "The {$key} must be a boolean value (enabled or disabled).";
                    continue;
                }
                $changes[$key] = in_array($value, [true, 1, '1', 'true', 'enabled'], true);
            }
        }

        // Reject entire batch if any invalid value (requirement 7.5)
        if (!empty($errors)) {
            $this->auditLogger->log('SETTINGS_UPDATED', $adminId, [
                'resource_type' => 'settings',
                'resource_id' => 'batch',
                'result' => 'failure',
                'reason' => 'Validation failed for settings: ' . implode(', ', array_keys($errors)),
                'errors' => $errors,
            ]);

            return redirect()->route('admin.extensions.subdomain.index')
                ->withErrors($errors);
        }

        // If no changes to apply
        if (empty($changes)) {
            return redirect()->route('admin.extensions.subdomain.index')
                ->with('info', 'No settings changes were submitted.');
        }

        // Store valid settings and log changes with previous/new values
        $previousValues = [];
        $newValues = [];

        foreach ($changes as $key => $newValue) {
            $settingsKey = 'settings_' . $key;
            $previousValue = $this->blueprint->dbGet(self::TABLE, $settingsKey, $settingsConstraints[$key]['default']);
            $previousValues[$key] = $previousValue;
            $newValues[$key] = $newValue;

            $this->blueprint->dbSet(self::TABLE, $settingsKey, $newValue);
        }

        // Audit log with previous and new values
        $this->auditLogger->log('SETTINGS_UPDATED', $adminId, [
            'resource_type' => 'settings',
            'resource_id' => 'batch',
            'result' => 'success',
            'changed_keys' => array_keys($changes),
            'previous_values' => $previousValues,
            'new_values' => $newValues,
        ]);

        return redirect()->route('admin.extensions.subdomain.index')
            ->with('success', 'Extension settings have been updated successfully.');
    }

    /**
     * Delete a token or subdomain record.
     *
     * Handles both token deletion (target = "token") with active record check
     * and subdomain deletion (target = "record") with Cloudflare API cleanup.
     * Handles 404 from Cloudflare gracefully, updates indexes, audit logs.
     *
     * @param string $target The type of resource to delete ("token" or "record")
     * @param string $id The resource identifier (token UUID or record ID)
     */
    public function delete(string $target, string $id): RedirectResponse
    {
        $adminId = auth()->user()->id ?? 0;

        if ($target === 'token') {
            return $this->deleteToken($id, $adminId);
        }

        if ($target === 'record' || $target === 'subdomain') {
            return $this->deleteRecord($id, $adminId);
        }

        return redirect()->route('admin.extensions.subdomain.index')
            ->withErrors(['delete' => 'Invalid deletion target specified.']);
    }

    /**
     * Delete a Cloudflare API token.
     *
     * Checks for active subdomain records using this token, removes token data
     * and index entry, invalidates zone cache, and audit logs.
     */
    private function deleteToken(string $tokenId, int $adminId): RedirectResponse
    {
        // Verify the token exists
        $tokenData = $this->blueprint->dbGet(self::TABLE, 'cf_token_' . $tokenId);
        if (!$tokenData || !is_array($tokenData)) {
            return redirect()->route('admin.extensions.subdomain.index')
                ->withErrors(['delete' => 'The specified token was not found.']);
        }

        // Check for active subdomain records using this token (requirement 2.4)
        $hasActiveRecords = $this->tokenHasActiveRecords($tokenId);
        if ($hasActiveRecords) {
            $this->auditLogger->log('TOKEN_REMOVED', $adminId, [
                'resource_type' => 'token',
                'resource_id' => $tokenId,
                'result' => 'failure',
                'reason' => 'Token has active subdomain records',
            ]);

            return redirect()->route('admin.extensions.subdomain.index')
                ->withErrors(['delete' => 'This token cannot be removed while active subdomain records depend on it. Please delete all associated subdomain records first.']);
        }

        // Remove token data
        $this->blueprint->dbForget(self::TABLE, 'cf_token_' . $tokenId);

        // Update tokens index
        $tokensIndex = $this->blueprint->dbGet(self::TABLE, 'cf_tokens_index', []);
        if (is_array($tokensIndex)) {
            $tokensIndex = array_values(array_filter($tokensIndex, fn($id) => $id !== $tokenId));
            $this->blueprint->dbSet(self::TABLE, 'cf_tokens_index', $tokensIndex);
        }

        // Invalidate zone cache (requirement 2.5)
        $this->blueprint->dbForget(self::TABLE, 'zone_cache');
        $this->blueprint->dbForget(self::TABLE, 'zone_cache_time');

        // Audit log
        $this->auditLogger->log('TOKEN_REMOVED', $adminId, [
            'resource_type' => 'token',
            'resource_id' => $tokenId,
            'result' => 'success',
            'label' => $tokenData['label'] ?? '',
        ]);

        return redirect()->route('admin.extensions.subdomain.index')
            ->with('success', 'Cloudflare API token has been successfully removed.');
    }

    /**
     * Delete a subdomain DNS record.
     *
     * Deletes the DNS record from Cloudflare, removes local mapping, updates
     * zone and server indexes, and audit logs. Handles 404 gracefully.
     */
    private function deleteRecord(string $recordId, int $adminId): RedirectResponse
    {
        // Verify the record exists locally
        $recordData = $this->blueprint->dbGet(self::TABLE, 'record_' . $recordId);
        if (!$recordData || !is_array($recordData)) {
            return redirect()->route('admin.extensions.subdomain.index')
                ->withErrors(['delete' => 'The specified subdomain record was not found.']);
        }

        $zoneId = $recordData['zone_id'] ?? '';
        $serverId = $recordData['server_id'] ?? null;
        $tokenId = $recordData['token_id'] ?? '';
        $fullName = $recordData['full_name'] ?? '';

        // Get the token to make the Cloudflare API call
        $tokenData = $this->blueprint->dbGet(self::TABLE, 'cf_token_' . $tokenId);
        if (!$tokenData || !is_array($tokenData) || !isset($tokenData['encrypted_token'])) {
            // Token unavailable - requirement 5.8
            $this->auditLogger->log('SUBDOMAIN_DELETED', $adminId, [
                'resource_type' => 'subdomain',
                'resource_id' => $recordId,
                'result' => 'failure',
                'reason' => 'Associated token is unavailable',
                'full_name' => $fullName,
            ]);

            return redirect()->route('admin.extensions.subdomain.index')
                ->withErrors(['delete' => 'The API token associated with this subdomain record is unavailable. The local record has been preserved.']);
        }

        try {
            $plainToken = $this->encryption->decrypt($tokenData['encrypted_token']);
        } catch (DecryptException $e) {
            $this->auditLogger->log('SUBDOMAIN_DELETED', $adminId, [
                'resource_type' => 'subdomain',
                'resource_id' => $recordId,
                'result' => 'failure',
                'reason' => 'Token decryption failed',
                'full_name' => $fullName,
            ]);

            return redirect()->route('admin.extensions.subdomain.index')
                ->withErrors(['delete' => 'The API token associated with this subdomain record is corrupted and cannot be decrypted. The local record has been preserved.']);
        }

        // Delete the DNS record from Cloudflare
        $cloudflareSuccess = false;
        $cloudflare404 = false;

        try {
            $this->rateLimiter->recordApiCall();
            $cloudflareSuccess = $this->cloudflare->deleteDnsRecord($plainToken, $zoneId, $recordId);
        } catch (CloudflareServiceException $e) {
            // Check if it's a 401/403 — mark token as needs re-verification
            if (str_contains($e->getMessage(), 'HTTP 401') || str_contains($e->getMessage(), 'HTTP 403')) {
                $this->markTokenNeedsReVerification($tokenId);
            }

            // If it's a non-404 failure, return error and preserve local mapping (requirement 5.5)
            $this->auditLogger->log('SUBDOMAIN_DELETED', $adminId, [
                'resource_type' => 'subdomain',
                'resource_id' => $recordId,
                'result' => 'failure',
                'reason' => $e->getMessage(),
                'full_name' => $fullName,
                'zone_id' => $zoneId,
            ]);

            return redirect()->route('admin.extensions.subdomain.index')
                ->withErrors(['delete' => 'Failed to delete DNS record from Cloudflare: ' . $e->getMessage()]);
        }

        // CloudflareService::deleteDnsRecord returns true even for 404 (already handles gracefully)
        // So if we get here, either the record was deleted or it didn't exist on Cloudflare

        // Remove local record mapping
        $this->blueprint->dbForget(self::TABLE, 'record_' . $recordId);

        // Update zone record index
        $zoneRecords = $this->blueprint->dbGet(self::TABLE, 'zone_' . $zoneId . '_records', []);
        if (is_array($zoneRecords)) {
            $zoneRecords = array_values(array_filter($zoneRecords, fn($id) => $id !== $recordId));
            $this->blueprint->dbSet(self::TABLE, 'zone_' . $zoneId . '_records', $zoneRecords);
        }

        // Update server record index
        if ($serverId !== null) {
            $serverRecords = $this->blueprint->dbGet(self::TABLE, 'server_' . $serverId . '_records', []);
            if (is_array($serverRecords)) {
                $serverRecords = array_values(array_filter($serverRecords, fn($id) => $id !== $recordId));
                $this->blueprint->dbSet(self::TABLE, 'server_' . $serverId . '_records', $serverRecords);
            }
        }

        // Audit log
        $this->auditLogger->log('SUBDOMAIN_DELETED', $adminId, [
            'resource_type' => 'subdomain',
            'resource_id' => $recordId,
            'result' => 'success',
            'full_name' => $fullName,
            'zone_id' => $zoneId,
            'zone_name' => $recordData['zone_name'] ?? '',
            'record_type' => $recordData['record_type'] ?? '',
            'server_id' => $serverId,
        ]);

        return redirect()->route('admin.extensions.subdomain.index')
            ->with('success', 'Subdomain record "' . $fullName . '" has been successfully deleted.');
    }

    /**
     * Load zone cache, refreshing if expired.
     *
     * Checks TTL (default 300 seconds), serves cached data or fetches fresh
     * zone data from Cloudflare for all configured tokens.
     *
     * @param array $tokenData Associative array of token ID => token data
     * @return array Contains 'zones', 'stale', and 'age' keys
     */
    private function loadZoneCache(array $tokenData): array
    {
        $cacheTtl = (int) $this->blueprint->dbGet(self::TABLE, 'settings_cache_ttl', self::DEFAULT_CACHE_TTL);
        $cachedZones = $this->blueprint->dbGet(self::TABLE, 'zone_cache');
        $cacheTime = $this->blueprint->dbGet(self::TABLE, 'zone_cache_time');

        $now = time();
        $cacheAge = 0;
        $isStale = false;

        // Check if cache is valid
        if ($cachedZones !== null && is_array($cachedZones) && $cacheTime !== null) {
            $cacheAge = $now - (int) $cacheTime;
            if ($cacheAge < $cacheTtl) {
                // Cache is valid, serve it
                return ['zones' => $cachedZones, 'stale' => false, 'age' => $cacheAge];
            }
        }

        // Cache is expired or missing — fetch fresh zones
        $allZones = [];
        $zonesSeen = []; // For deduplication by zone ID
        $fetchFailed = false;

        foreach ($tokenData as $tokenId => $data) {
            if (!isset($data['encrypted_token'])) {
                continue;
            }

            try {
                $plainToken = $this->encryption->decrypt($data['encrypted_token']);
            } catch (DecryptException $e) {
                continue; // Skip corrupted tokens
            }

            try {
                $this->rateLimiter->recordApiCall();
                $zones = $this->cloudflare->listZones($plainToken);

                foreach ($zones as $zone) {
                    $zId = $zone['id'] ?? '';
                    if ($zId !== '' && !isset($zonesSeen[$zId])) {
                        $zonesSeen[$zId] = true;
                        $allZones[] = $zone;
                    }
                }
            } catch (CloudflareServiceException $e) {
                $fetchFailed = true;

                // Check for 401/403 — mark token as needs re-verification
                if (str_contains($e->getMessage(), 'HTTP 401') || str_contains($e->getMessage(), 'HTTP 403')) {
                    $this->markTokenNeedsReVerification($tokenId);
                }

                continue;
            }
        }

        // If fetch failed but we have cached data, serve stale cache
        if ($fetchFailed && empty($allZones) && $cachedZones !== null && is_array($cachedZones)) {
            $cacheAge = $cacheTime !== null ? ($now - (int) $cacheTime) : 0;
            return ['zones' => $cachedZones, 'stale' => true, 'age' => $cacheAge];
        }

        // Update zone cache with fresh data
        if (!empty($allZones)) {
            $this->blueprint->dbSet(self::TABLE, 'zone_cache', $allZones);
            $this->blueprint->dbSet(self::TABLE, 'zone_cache_time', $now);
        }

        return ['zones' => $allZones, 'stale' => false, 'age' => 0];
    }

    /**
     * Load subdomain records with pagination (20 per page).
     *
     * Retrieves all record IDs, sorts by creation timestamp descending,
     * and returns a paginated subset.
     *
     * @param int $page The current page number
     * @return array Contains 'records', 'current_page', 'total_pages', 'total_records'
     */
    private function loadSubdomainRecords(int $page): array
    {
        // Get all tokens index to find all records
        $tokensIndex = $this->blueprint->dbGet(self::TABLE, 'cf_tokens_index', []);
        if (!is_array($tokensIndex)) {
            $tokensIndex = [];
        }

        // Collect all record IDs from all zone indexes
        $allRecordIds = [];
        $zones = $this->blueprint->dbGet(self::TABLE, 'zone_cache', []);
        if (is_array($zones)) {
            foreach ($zones as $zone) {
                $zoneId = $zone['id'] ?? '';
                if ($zoneId !== '') {
                    $zoneRecords = $this->blueprint->dbGet(self::TABLE, 'zone_' . $zoneId . '_records', []);
                    if (is_array($zoneRecords)) {
                        $allRecordIds = array_merge($allRecordIds, $zoneRecords);
                    }
                }
            }
        }

        // Also gather records from tokens that might reference zones not in cache
        foreach ($tokensIndex as $tokenId) {
            $tokenData = $this->blueprint->dbGet(self::TABLE, 'cf_token_' . $tokenId);
            if ($tokenData && is_array($tokenData) && isset($tokenData['zone_ids'])) {
                foreach ($tokenData['zone_ids'] as $zoneId) {
                    $zoneRecords = $this->blueprint->dbGet(self::TABLE, 'zone_' . $zoneId . '_records', []);
                    if (is_array($zoneRecords)) {
                        $allRecordIds = array_merge($allRecordIds, $zoneRecords);
                    }
                }
            }
        }

        // Deduplicate
        $allRecordIds = array_unique($allRecordIds);

        // Fetch all record data
        $records = [];
        if (!empty($allRecordIds)) {
            $recordKeys = array_map(fn($id) => 'record_' . $id, $allRecordIds);
            $recordsData = $this->blueprint->dbGetMany(self::TABLE, $recordKeys);

            foreach ($recordsData as $key => $data) {
                if ($data && is_array($data)) {
                    $records[] = $data;
                }
            }
        }

        // Sort by creation timestamp descending (newest first) - requirement 6.1
        usort($records, function ($a, $b) {
            $timeA = $a['created_at'] ?? '';
            $timeB = $b['created_at'] ?? '';
            return strcmp($timeB, $timeA);
        });

        // Pagination
        $totalRecords = count($records);
        $totalPages = $totalRecords > 0 ? (int) ceil($totalRecords / self::RECORDS_PER_PAGE) : 0;
        $page = min($page, max(1, $totalPages));
        $offset = ($page - 1) * self::RECORDS_PER_PAGE;
        $paginatedRecords = array_slice($records, $offset, self::RECORDS_PER_PAGE);

        return [
            'records' => $paginatedRecords,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_records' => $totalRecords,
        ];
    }

    /**
     * Mask a token for display, showing only the last 4 characters.
     *
     * @param string $tokenId The token UUID
     * @param array $tokenData The stored token data
     * @return string The masked token display string
     */
    private function maskToken(string $tokenId, array $tokenData): string
    {
        if (!isset($tokenData['encrypted_token'])) {
            return '****';
        }

        try {
            $plainToken = $this->encryption->decrypt($tokenData['encrypted_token']);
            if (strlen($plainToken) >= 4) {
                return str_repeat('*', strlen($plainToken) - 4) . substr($plainToken, -4);
            }
            return '****';
        } catch (DecryptException $e) {
            return '****[corrupted]';
        }
    }

    /**
     * Get the current extension settings with defaults.
     *
     * @return array The settings array with current and default values
     */
    private function getSettings(): array
    {
        return [
            'rate_limit_per_minute' => [
                'value' => $this->blueprint->dbGet(self::TABLE, 'settings_rate_limit_per_minute', 30),
                'default' => 30,
                'min' => 1,
                'max' => 120,
            ],
            'cache_ttl' => [
                'value' => $this->blueprint->dbGet(self::TABLE, 'settings_cache_ttl', 300),
                'default' => 300,
                'min' => 60,
                'max' => 3600,
            ],
            'max_subdomains_per_server' => [
                'value' => $this->blueprint->dbGet(self::TABLE, 'settings_max_subdomains_per_server', 5),
                'default' => 5,
                'min' => 1,
                'max' => 50,
            ],
            'wildcard_allowed' => [
                'value' => $this->blueprint->dbGet(self::TABLE, 'settings_wildcard_allowed', false),
                'default' => false,
            ],
        ];
    }

    /**
     * Find a token that has access to a specific zone ID.
     *
     * @param string $zoneId The Cloudflare zone ID
     * @return string|null The token ID if found, null otherwise
     */
    private function findTokenForZone(string $zoneId): ?string
    {
        $tokensIndex = $this->blueprint->dbGet(self::TABLE, 'cf_tokens_index', []);
        if (!is_array($tokensIndex)) {
            return null;
        }

        foreach ($tokensIndex as $tokenId) {
            $tokenData = $this->blueprint->dbGet(self::TABLE, 'cf_token_' . $tokenId);
            if ($tokenData && is_array($tokenData) && isset($tokenData['zone_ids'])) {
                if (in_array($zoneId, $tokenData['zone_ids'], true)) {
                    return $tokenId;
                }
            }
        }

        return null;
    }

    /**
     * Get the zone name for a given zone ID from the cache.
     *
     * @param string $zoneId The Cloudflare zone ID
     * @return string The zone name, or empty string if not found
     */
    private function getZoneNameById(string $zoneId): string
    {
        $zones = $this->blueprint->dbGet(self::TABLE, 'zone_cache', []);
        if (is_array($zones)) {
            foreach ($zones as $zone) {
                if (($zone['id'] ?? '') === $zoneId) {
                    return $zone['name'] ?? '';
                }
            }
        }

        return '';
    }

    /**
     * Check if a token has any active subdomain records associated with it.
     *
     * @param string $tokenId The token UUID
     * @return bool True if the token has active records
     */
    private function tokenHasActiveRecords(string $tokenId): bool
    {
        // Check all zone indexes for records using this token
        $tokensIndex = $this->blueprint->dbGet(self::TABLE, 'cf_tokens_index', []);
        if (!is_array($tokensIndex)) {
            return false;
        }

        // Get the token data to find its zone IDs
        $tokenData = $this->blueprint->dbGet(self::TABLE, 'cf_token_' . $tokenId);
        if (!$tokenData || !is_array($tokenData) || !isset($tokenData['zone_ids'])) {
            return false;
        }

        foreach ($tokenData['zone_ids'] as $zoneId) {
            $zoneRecords = $this->blueprint->dbGet(self::TABLE, 'zone_' . $zoneId . '_records', []);
            if (is_array($zoneRecords)) {
                foreach ($zoneRecords as $recordId) {
                    $recordData = $this->blueprint->dbGet(self::TABLE, 'record_' . $recordId);
                    if ($recordData && is_array($recordData) && ($recordData['token_id'] ?? '') === $tokenId) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Mark a token as needing re-verification.
     *
     * Updates the token status when Cloudflare returns 401/403, indicating
     * the token may have been revoked or permissions changed.
     *
     * @param string $tokenId The token UUID to mark
     */
    private function markTokenNeedsReVerification(string $tokenId): void
    {
        $tokenData = $this->blueprint->dbGet(self::TABLE, 'cf_token_' . $tokenId);
        if ($tokenData && is_array($tokenData)) {
            $tokenData['needs_re_verification'] = true;
            $tokenData['re_verification_reason'] = 'Cloudflare returned 401/403 - token may be revoked or permissions changed';
            $tokenData['re_verification_at'] = gmdate('Y-m-d\TH:i:s\Z');
            $this->blueprint->dbSet(self::TABLE, 'cf_token_' . $tokenId, $tokenData);
        }
    }
}
