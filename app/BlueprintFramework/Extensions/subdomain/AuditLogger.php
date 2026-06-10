<?php

namespace Pterodactyl\BlueprintFramework\Extensions\subdomain;

use Pterodactyl\BlueprintFramework\Libraries\ExtensionLibrary\Console\BlueprintConsoleLibrary;

class AuditLogger
{
    /**
     * The database table used for this extension's data persistence.
     */
    private const TABLE = 'subdomain';

    /**
     * The key used to store the audit log index.
     */
    private const AUDIT_INDEX_KEY = 'audit_index';

    /**
     * Maximum number of audit entries retained in the index.
     */
    private const MAX_ENTRIES = 500;

    /**
     * Valid action types for audit logging.
     */
    private const VALID_ACTIONS = [
        'TOKEN_ADDED',
        'TOKEN_REMOVED',
        'TOKEN_VERIFIED',
        'SUBDOMAIN_CREATED',
        'SUBDOMAIN_DELETED',
        'SETTINGS_UPDATED',
        'ZONE_REFRESHED',
    ];

    /**
     * The BlueprintExtensionLibrary instance for data persistence.
     */
    private BlueprintConsoleLibrary $library;

    /**
     * Create a new AuditLogger instance.
     *
     * @param BlueprintConsoleLibrary $library The Blueprint extension library for data persistence
     */
    public function __construct(BlueprintConsoleLibrary $library)
    {
        $this->library = $library;
    }

    /**
     * Log a security-relevant action.
     *
     * Stores an audit entry with ISO 8601 timestamp, admin email, action type,
     * resource type, resource ID, IP address, and result. Maintains the audit
     * index capped at 500 entries, removing the oldest on overflow.
     *
     * @param string $action The action type (e.g., TOKEN_ADDED, SUBDOMAIN_CREATED)
     * @param int $adminId The admin user ID performing the action
     * @param array $details Additional action-specific details. Expected keys:
     *   - 'resource_type' (string): The type of resource affected (e.g., 'token', 'subdomain', 'settings')
     *   - 'resource_id' (string): The identifier of the affected resource
     *   - 'result' (string): 'success' or 'failure'
     *   - Any additional context-specific details
     */
    public function log(string $action, int $adminId, array $details): void
    {
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');
        $randomSuffix = bin2hex(random_bytes(4));
        $entryKey = "audit_{$timestamp}_{$randomSuffix}";

        // Build the audit entry
        $entry = [
            'id' => $entryKey,
            'timestamp' => $timestamp,
            'admin_id' => $adminId,
            'admin_email' => $this->getAdminEmail(),
            'action' => $action,
            'resource_type' => $details['resource_type'] ?? '',
            'resource_id' => $details['resource_id'] ?? '',
            'details' => $details,
            'ip_address' => $this->getRequestIp(),
            'result' => $details['result'] ?? 'success',
        ];

        // Store the audit entry
        $this->library->dbSet(self::TABLE, $entryKey, $entry);

        // Update the audit index
        $index = $this->getIndex();
        $index[] = $entryKey;

        // Enforce the 500-entry cap by removing oldest entries
        if (count($index) > self::MAX_ENTRIES) {
            $overflow = count($index) - self::MAX_ENTRIES;
            $oldestKeys = array_slice($index, 0, $overflow);

            // Remove the oldest entries' data
            foreach ($oldestKeys as $oldKey) {
                $this->library->dbForget(self::TABLE, $oldKey);
            }

            // Trim the index to keep only the most recent entries
            $index = array_slice($index, $overflow);
        }

        // Persist the updated index
        $this->library->dbSet(self::TABLE, self::AUDIT_INDEX_KEY, $index);
    }

    /**
     * Retrieve recent audit log entries in reverse chronological order (newest first).
     *
     * @param int $limit Maximum number of entries to return (default 50)
     * @return array Array of audit log entries sorted newest first
     */
    public function getRecent(int $limit = 50): array
    {
        $index = $this->getIndex();

        if (empty($index)) {
            return [];
        }

        // Reverse to get newest first
        $reversed = array_reverse($index);

        // Limit the number of entries to fetch
        $keysToFetch = array_slice($reversed, 0, $limit);

        // Fetch the entries
        $entries = $this->library->dbGetMany(self::TABLE, $keysToFetch);

        // Build result array in order (newest first), filtering out null entries
        $result = [];
        foreach ($keysToFetch as $key) {
            if (isset($entries[$key]) && is_array($entries[$key])) {
                $result[] = $entries[$key];
            }
        }

        return $result;
    }

    /**
     * Retrieve audit log entries filtered by action type, in reverse chronological order.
     *
     * @param string $action The action type to filter by (e.g., 'TOKEN_ADDED')
     * @param int $limit Maximum number of entries to return (default 50)
     * @return array Array of audit log entries matching the action type, sorted newest first
     */
    public function getByAction(string $action, int $limit = 50): array
    {
        $index = $this->getIndex();

        if (empty($index)) {
            return [];
        }

        // Reverse to get newest first
        $reversed = array_reverse($index);

        // Fetch all entries and filter by action
        $result = [];
        $batchSize = min(count($reversed), self::MAX_ENTRIES);
        $keysToFetch = array_slice($reversed, 0, $batchSize);

        $entries = $this->library->dbGetMany(self::TABLE, $keysToFetch);

        foreach ($keysToFetch as $key) {
            if (isset($entries[$key]) && is_array($entries[$key]) && ($entries[$key]['action'] ?? '') === $action) {
                $result[] = $entries[$key];
                if (count($result) >= $limit) {
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Get the current audit index (array of entry keys).
     *
     * @return array The current audit index
     */
    private function getIndex(): array
    {
        $index = $this->library->dbGet(self::TABLE, self::AUDIT_INDEX_KEY);

        if (!is_array($index)) {
            return [];
        }

        return $index;
    }

    /**
     * Get the current admin's email address.
     *
     * Falls back to 'console@localhost' for console/testing contexts
     * where no authenticated user is available.
     *
     * @return string The admin email address
     */
    private function getAdminEmail(): string
    {
        try {
            $user = auth()->user();
            if ($user && isset($user->email)) {
                return $user->email;
            }
        } catch (\Exception $e) {
            // Fall through to default
        }

        return 'console@localhost';
    }

    /**
     * Get the current request IP address.
     *
     * Falls back to '127.0.0.1' for console/testing contexts
     * where no HTTP request is available.
     *
     * @return string The request IP address
     */
    private function getRequestIp(): string
    {
        try {
            $request = request();
            if ($request) {
                return $request->ip() ?? '127.0.0.1';
            }
        } catch (\Exception $e) {
            // Fall through to default
        }

        return '127.0.0.1';
    }
}
