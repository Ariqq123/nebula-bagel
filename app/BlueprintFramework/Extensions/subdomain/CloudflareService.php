<?php

namespace Pterodactyl\BlueprintFramework\Extensions\subdomain;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;

class CloudflareService
{
    /**
     * Cloudflare API v4 base URL.
     */
    private const BASE_URL = 'https://api.cloudflare.com/client/v4/';

    /**
     * Request timeout in seconds.
     */
    private const TIMEOUT = 10;

    /**
     * Maximum number of retry attempts for network failures.
     */
    private const MAX_RETRIES = 3;

    /**
     * Backoff delays in seconds for each retry attempt (1s, 2s, 4s).
     */
    private const BACKOFF_DELAYS = [1, 2, 4];

    /**
     * Default cooldown in seconds when Cloudflare returns 429 without retry-after header.
     */
    private const DEFAULT_RATE_LIMIT_COOLDOWN = 60;

    /**
     * Required permissions for a valid token.
     */
    private const REQUIRED_PERMISSIONS = ['Zone:Read', 'DNS:Edit'];

    /**
     * Timestamp when rate limit cooldown expires (null if not rate limited).
     *
     * @var int|null
     */
    private ?int $rateLimitedUntil = null;

    /**
     * Verify that a Cloudflare API token is valid and has required permissions.
     *
     * Calls GET /user/tokens/verify with a 10-second timeout. Validates that
     * the token status is "active" and that the permission set includes Zone:Read
     * and DNS:Edit.
     *
     * @param string $token The Cloudflare API token to verify
     * @return TokenVerificationResult The verification result
     */
    public function verifyToken(string $token): TokenVerificationResult
    {
        try {
            $response = $this->makeRequest('GET', 'user/tokens/verify', $token);
        } catch (CloudflareServiceException $e) {
            return TokenVerificationResult::failure($e->getMessage());
        }

        if (!$response->successful()) {
            $errors = $response->json('errors', []);
            $errorMessage = !empty($errors) ? $errors[0]['message'] ?? 'Unknown error' : 'Token verification failed';
            return TokenVerificationResult::failure(
                'Token verification failed: ' . $errorMessage . ' (HTTP ' . $response->status() . ')'
            );
        }

        $result = $response->json('result', []);
        $status = $result['status'] ?? '';

        if ($status !== 'active') {
            return TokenVerificationResult::failure(
                'Token is not active. Current status: ' . ($status ?: 'unknown')
            );
        }

        // Extract permissions from the token verification response
        $permissions = $this->extractPermissions($response->json());

        $verificationResult = TokenVerificationResult::success($status, $permissions);

        // Validate that the token has the required permissions
        if (!$verificationResult->hasRequiredPermissions()) {
            return TokenVerificationResult::failure(
                'Token is missing required permissions. Both Zone:Read and DNS:Edit permissions are required.'
            );
        }

        return $verificationResult;
    }

    /**
     * List all zones accessible by the token.
     *
     * Calls GET /zones and parses the zone list from the response.
     *
     * @param string $token The Cloudflare API token
     * @return array Array of zone objects with id, name, status, and name_servers
     *
     * @throws CloudflareServiceException If the API call fails after retries
     */
    public function listZones(string $token): array
    {
        try {
            $response = $this->makeRequest('GET', 'zones', $token);
        } catch (CloudflareServiceException $e) {
            throw $e;
        }

        if (!$response->successful()) {
            $errors = $response->json('errors', []);
            $errorMessage = !empty($errors) ? $errors[0]['message'] ?? 'Unknown error' : 'Failed to list zones';
            throw new CloudflareServiceException(
                'Failed to list zones: ' . $errorMessage . ' (HTTP ' . $response->status() . ')'
            );
        }

        $zones = $response->json('result', []);

        return array_map(function ($zone) {
            return [
                'id' => $zone['id'] ?? '',
                'name' => $zone['name'] ?? '',
                'status' => $zone['status'] ?? '',
                'name_servers' => $zone['name_servers'] ?? [],
            ];
        }, $zones);
    }

    /**
     * List DNS records for a specific zone.
     *
     * Calls GET /zones/{zone_id}/dns_records and returns the record list.
     *
     * @param string $token The Cloudflare API token
     * @param string $zoneId The Cloudflare zone ID
     * @return array Array of DNS record objects
     *
     * @throws CloudflareServiceException If the API call fails after retries
     */
    public function listDnsRecords(string $token, string $zoneId): array
    {
        try {
            $response = $this->makeRequest('GET', 'zones/' . $zoneId . '/dns_records', $token);
        } catch (CloudflareServiceException $e) {
            throw $e;
        }

        if (!$response->successful()) {
            $errors = $response->json('errors', []);
            $errorMessage = !empty($errors) ? $errors[0]['message'] ?? 'Unknown error' : 'Failed to list DNS records';
            throw new CloudflareServiceException(
                'Failed to list DNS records: ' . $errorMessage . ' (HTTP ' . $response->status() . ')'
            );
        }

        return $response->json('result', []);
    }

    /**
     * Create a new DNS record in a zone.
     *
     * Calls POST /zones/{zone_id}/dns_records with the record data.
     * Uses a 10-second timeout per attempt.
     *
     * @param string $token The Cloudflare API token
     * @param string $zoneId The Cloudflare zone ID
     * @param string $name The DNS record name (subdomain)
     * @param string $type The record type (A, AAAA, or CNAME)
     * @param string $content The record content (IP address or hostname)
     * @param bool $proxied Whether to enable Cloudflare proxy
     * @param int $ttl The TTL in seconds (1 = automatic)
     * @return DnsRecordResult The result of the creation attempt
     */
    public function createDnsRecord(
        string $token,
        string $zoneId,
        string $name,
        string $type,
        string $content,
        bool $proxied = false,
        int $ttl = 1
    ): DnsRecordResult {
        $body = [
            'type' => $type,
            'name' => $name,
            'content' => $content,
            'proxied' => $proxied,
            'ttl' => $ttl,
        ];

        try {
            $response = $this->makeRequest('POST', 'zones/' . $zoneId . '/dns_records', $token, $body);
        } catch (CloudflareServiceException $e) {
            return DnsRecordResult::failure($e->getMessage());
        }

        if (!$response->successful()) {
            $errors = $response->json('errors', []);
            $errorMessage = !empty($errors) ? $errors[0]['message'] ?? 'Unknown error' : 'Failed to create DNS record';
            return DnsRecordResult::failure(
                'Failed to create DNS record: ' . $errorMessage . ' (HTTP ' . $response->status() . ')',
                $errors
            );
        }

        $result = $response->json('result', []);
        $recordId = $result['id'] ?? '';

        if (empty($recordId)) {
            return DnsRecordResult::failure('DNS record created but no record ID returned.');
        }

        return DnsRecordResult::success($recordId, $result);
    }

    /**
     * Delete a DNS record from a zone.
     *
     * Calls DELETE /zones/{zone_id}/dns_records/{record_id}.
     *
     * @param string $token The Cloudflare API token
     * @param string $zoneId The Cloudflare zone ID
     * @param string $recordId The DNS record ID to delete
     * @return bool True if the record was successfully deleted
     *
     * @throws CloudflareServiceException If the API call fails after retries
     */
    public function deleteDnsRecord(string $token, string $zoneId, string $recordId): bool
    {
        try {
            $response = $this->makeRequest('DELETE', 'zones/' . $zoneId . '/dns_records/' . $recordId, $token);
        } catch (CloudflareServiceException $e) {
            throw $e;
        }

        if (!$response->successful()) {
            // If record not found (404), still consider deletion successful
            if ($response->status() === 404) {
                return true;
            }

            $errors = $response->json('errors', []);
            $errorMessage = !empty($errors) ? $errors[0]['message'] ?? 'Unknown error' : 'Failed to delete DNS record';
            throw new CloudflareServiceException(
                'Failed to delete DNS record: ' . $errorMessage . ' (HTTP ' . $response->status() . ')'
            );
        }

        return true;
    }

    /**
     * Make an HTTP request to the Cloudflare API with retry logic and rate limit handling.
     *
     * Implements exponential backoff (1s, 2s, 4s) for network failures, up to 3 attempts.
     * Handles 429 responses by respecting the retry-after header or using a 60s default cooldown.
     *
     * @param string $method The HTTP method (GET, POST, DELETE)
     * @param string $endpoint The API endpoint (relative to base URL)
     * @param string $token The Bearer authentication token
     * @param array|null $body The request body for POST requests
     * @return Response The HTTP response
     *
     * @throws CloudflareServiceException If all retry attempts fail or rate limited
     */
    private function makeRequest(string $method, string $endpoint, string $token, ?array $body = null): Response
    {
        // Check if we are currently rate limited
        if ($this->rateLimitedUntil !== null && time() < $this->rateLimitedUntil) {
            $secondsRemaining = $this->rateLimitedUntil - time();
            throw new CloudflareServiceException(
                'Rate limited by Cloudflare. Please retry after ' . $secondsRemaining . ' seconds.'
            );
        }

        $lastException = null;
        $url = self::BASE_URL . ltrim($endpoint, '/');

        for ($attempt = 0; $attempt < self::MAX_RETRIES; $attempt++) {
            // Wait for backoff delay on retry attempts (not on first attempt)
            if ($attempt > 0) {
                $delay = self::BACKOFF_DELAYS[$attempt - 1] ?? end(self::BACKOFF_DELAYS);
                sleep($delay);
            }

            try {
                $request = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                ])->timeout(self::TIMEOUT);

                switch (strtoupper($method)) {
                    case 'GET':
                        $response = $request->get($url);
                        break;
                    case 'POST':
                        $response = $request->post($url, $body ?? []);
                        break;
                    case 'DELETE':
                        $response = $request->delete($url);
                        break;
                    default:
                        throw new CloudflareServiceException('Unsupported HTTP method: ' . $method);
                }

                // Handle rate limiting (429)
                if ($response->status() === 429) {
                    $this->handleRateLimit($response);

                    // If this is not the last attempt, retry after the cooldown
                    if ($attempt < self::MAX_RETRIES - 1) {
                        continue;
                    }

                    throw new CloudflareServiceException(
                        'Cloudflare API rate limit exceeded. Please retry later.'
                    );
                }

                // Successful request (even if the API returns an error status)
                return $response;

            } catch (ConnectionException $e) {
                $lastException = $e;
                // Continue to next retry attempt on network failures
                continue;
            }
        }

        // All attempts exhausted
        throw new CloudflareServiceException(
            'Failed to connect to Cloudflare API after ' . self::MAX_RETRIES . ' attempts. ' .
            'Last error: ' . ($lastException ? $lastException->getMessage() : 'Unknown error')
        );
    }

    /**
     * Handle a Cloudflare 429 rate limit response.
     *
     * Reads the retry-after header if present, otherwise defaults to 60 seconds.
     * Sets the rateLimitedUntil timestamp to block further requests until cooldown expires.
     *
     * @param Response $response The 429 response from Cloudflare
     */
    private function handleRateLimit(Response $response): void
    {
        $retryAfter = $response->header('retry-after');

        if ($retryAfter !== null && $retryAfter !== '') {
            // retry-after can be a number of seconds or an HTTP date
            if (is_numeric($retryAfter)) {
                $cooldown = (int) $retryAfter;
            } else {
                // Parse as HTTP date
                $timestamp = strtotime($retryAfter);
                $cooldown = $timestamp !== false ? max(0, $timestamp - time()) : self::DEFAULT_RATE_LIMIT_COOLDOWN;
            }
        } else {
            $cooldown = self::DEFAULT_RATE_LIMIT_COOLDOWN;
        }

        $this->rateLimitedUntil = time() + $cooldown;
    }

    /**
     * Extract permissions from the Cloudflare token verification response.
     *
     * The verification endpoint may return permissions in the token details.
     * This method parses various permission formats that Cloudflare may use.
     *
     * @param array $responseData The full JSON response data
     * @return array Array of permission strings (e.g., ['Zone:Read', 'DNS:Edit'])
     */
    private function extractPermissions(array $responseData): array
    {
        $permissions = [];

        // Check if permissions are in result.permissions
        $result = $responseData['result'] ?? [];

        if (isset($result['permissions'])) {
            return $result['permissions'];
        }

        // Check for permission groups in the token details
        if (isset($result['policies'])) {
            foreach ($result['policies'] as $policy) {
                if (isset($policy['permission_groups'])) {
                    foreach ($policy['permission_groups'] as $group) {
                        $permissions[] = $group['name'] ?? ($group['id'] ?? '');
                    }
                }
            }
        }

        // Check for scopes format
        if (isset($result['scopes'])) {
            foreach ($result['scopes'] as $scope) {
                $permissions[] = $scope;
            }
        }

        return $permissions;
    }

    /**
     * Get the current rate limit status.
     *
     * @return int|null Unix timestamp when rate limit expires, or null if not rate limited
     */
    public function getRateLimitedUntil(): ?int
    {
        return $this->rateLimitedUntil;
    }

    /**
     * Reset the rate limit status.
     *
     * Primarily useful for testing purposes.
     */
    public function resetRateLimit(): void
    {
        $this->rateLimitedUntil = null;
    }
}
