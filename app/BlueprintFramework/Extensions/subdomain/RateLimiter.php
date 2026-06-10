<?php

namespace Pterodactyl\BlueprintFramework\Extensions\subdomain;

use Pterodactyl\BlueprintFramework\Libraries\ExtensionLibrary\Console\BlueprintConsoleLibrary;

class RateLimiter
{
    /**
     * The database table name used by the extension.
     */
    private const TABLE = 'subdomain';

    /**
     * Default maximum subdomain creations per admin per 60-second window.
     */
    private const DEFAULT_ADMIN_LIMIT = 10;

    /**
     * Default maximum global Cloudflare API calls per 60-second window.
     */
    private const DEFAULT_GLOBAL_API_LIMIT = 30;

    /**
     * The fixed window size in seconds.
     */
    private const WINDOW_SIZE = 60;

    /**
     * Default cooldown in seconds when Cloudflare returns 429 without retry-after.
     */
    private const DEFAULT_CF_COOLDOWN = 60;

    /**
     * Storage key prefix for per-admin subdomain creation rate limit.
     */
    private const KEY_ADMIN_PREFIX = 'rate_limit_admin_';

    /**
     * Storage key for global API call rate limit.
     */
    private const KEY_GLOBAL_API = 'rate_limit_global_api';

    /**
     * Storage key for Cloudflare 429 block expiry timestamp.
     */
    private const KEY_CF_BLOCKED_UNTIL = 'rate_limit_cf_blocked_until';

    /**
     * @var BlueprintConsoleLibrary
     */
    private BlueprintConsoleLibrary $blueprint;

    /**
     * @var int|null Configurable global API limit (overrides default if set)
     */
    private ?int $globalApiLimit;

    /**
     * Create a new RateLimiter instance.
     *
     * @param BlueprintConsoleLibrary $blueprint The BlueprintExtensionLibrary instance for data persistence
     * @param int|null $globalApiLimit Optional override for the global API call limit
     */
    public function __construct(BlueprintConsoleLibrary $blueprint, ?int $globalApiLimit = null)
    {
        $this->blueprint = $blueprint;
        $this->globalApiLimit = $globalApiLimit;
    }

    /**
     * Check whether a request is allowed based on rate limits.
     *
     * Checks in order:
     * 1. Cloudflare 429 block (if active, all requests denied)
     * 2. Global API call limit
     * 3. Per-admin subdomain creation limit (only for 'subdomain_create' action)
     *
     * @param int $adminId The admin user ID performing the action
     * @param string $action The action being performed (e.g., 'subdomain_create', 'api_call')
     * @return RateLimitResult Result indicating allowed/denied with seconds until reset
     */
    public function checkLimit(int $adminId, string $action): RateLimitResult
    {
        // 1. Check if Cloudflare 429 block is active
        $cfBlockResult = $this->checkCloudflareBlock();
        if ($cfBlockResult !== null) {
            return $cfBlockResult;
        }

        // 2. Check global API limit
        $globalResult = $this->checkGlobalApiLimit();
        if (!$globalResult->allowed) {
            return $globalResult;
        }

        // 3. If action is 'subdomain_create', also check per-admin limit
        if ($action === 'subdomain_create') {
            $adminResult = $this->checkAdminSubdomainLimit($adminId);
            if (!$adminResult->allowed) {
                return $adminResult;
            }
        }

        return RateLimitResult::allowed();
    }

    /**
     * Record a Cloudflare API call in the global rate limit tracker.
     *
     * Adds the current timestamp to the global API call timestamps array.
     *
     * @return void
     */
    public function recordApiCall(): void
    {
        $key = self::KEY_GLOBAL_API;
        $timestamps = $this->getTimestamps($key);
        $timestamps[] = time();
        $this->setTimestamps($key, $timestamps);
    }

    /**
     * Record a subdomain creation in the per-admin rate limit tracker.
     *
     * Adds the current timestamp to the admin's subdomain creation timestamps array.
     *
     * @param int $adminId The admin user ID who created the subdomain
     * @return void
     */
    public function recordSubdomainCreation(int $adminId): void
    {
        $key = self::KEY_ADMIN_PREFIX . $adminId;
        $timestamps = $this->getTimestamps($key);
        $timestamps[] = time();
        $this->setTimestamps($key, $timestamps);
    }

    /**
     * Set the Cloudflare 429 block with the given retry-after duration.
     *
     * When Cloudflare returns a 429 response, this method stores the Unix
     * timestamp until which all further requests should be blocked.
     *
     * @param int $retryAfter Number of seconds to block (from retry-after header or default 60s)
     * @return void
     */
    public function setCloudflareBlock(int $retryAfter): void
    {
        if ($retryAfter <= 0) {
            $retryAfter = self::DEFAULT_CF_COOLDOWN;
        }

        $blockedUntil = time() + $retryAfter;
        $this->blueprint->dbSet(self::TABLE, self::KEY_CF_BLOCKED_UNTIL, $blockedUntil);
    }

    /**
     * Check if the Cloudflare 429 block is currently active.
     *
     * @return RateLimitResult|null Returns a denied result if blocked, null if not blocked
     */
    private function checkCloudflareBlock(): ?RateLimitResult
    {
        $blockedUntil = $this->blueprint->dbGet(self::TABLE, self::KEY_CF_BLOCKED_UNTIL);

        if ($blockedUntil === null) {
            return null;
        }

        $now = time();

        if ($blockedUntil > $now) {
            $secondsRemaining = $blockedUntil - $now;
            return RateLimitResult::denied('cloudflare_429', $secondsRemaining);
        }

        // Block has expired, clean it up
        $this->blueprint->dbForget(self::TABLE, self::KEY_CF_BLOCKED_UNTIL);

        return null;
    }

    /**
     * Check the global Cloudflare API call rate limit.
     *
     * Cleans up expired timestamps (older than 60 seconds) and checks if the
     * number of remaining timestamps exceeds the configured limit.
     *
     * @return RateLimitResult Result indicating allowed/denied
     */
    private function checkGlobalApiLimit(): RateLimitResult
    {
        $limit = $this->getGlobalApiLimit();
        $key = self::KEY_GLOBAL_API;

        $timestamps = $this->getTimestamps($key);
        $timestamps = $this->cleanExpiredTimestamps($timestamps);
        $this->setTimestamps($key, $timestamps);

        if (count($timestamps) >= $limit) {
            $secondsUntilReset = $this->getSecondsUntilReset($timestamps);
            return RateLimitResult::denied('global_api', $secondsUntilReset);
        }

        return RateLimitResult::allowed();
    }

    /**
     * Check the per-admin subdomain creation rate limit.
     *
     * Cleans up expired timestamps (older than 60 seconds) and checks if the
     * number of remaining timestamps exceeds 10 per admin per window.
     *
     * @param int $adminId The admin user ID to check
     * @return RateLimitResult Result indicating allowed/denied
     */
    private function checkAdminSubdomainLimit(int $adminId): RateLimitResult
    {
        $key = self::KEY_ADMIN_PREFIX . $adminId;

        $timestamps = $this->getTimestamps($key);
        $timestamps = $this->cleanExpiredTimestamps($timestamps);
        $this->setTimestamps($key, $timestamps);

        if (count($timestamps) >= self::DEFAULT_ADMIN_LIMIT) {
            $secondsUntilReset = $this->getSecondsUntilReset($timestamps);
            return RateLimitResult::denied('admin_subdomain_create', $secondsUntilReset);
        }

        return RateLimitResult::allowed();
    }

    /**
     * Get the configured global API call limit.
     *
     * Checks for a constructor-provided override first, then checks the
     * extension settings, then falls back to the default.
     *
     * @return int The global API call limit
     */
    private function getGlobalApiLimit(): int
    {
        if ($this->globalApiLimit !== null) {
            return $this->globalApiLimit;
        }

        $configured = $this->blueprint->dbGet(self::TABLE, 'settings_rate_limit_per_minute');

        if ($configured !== null && is_numeric($configured) && (int) $configured >= 1 && (int) $configured <= 1200) {
            return (int) $configured;
        }

        return self::DEFAULT_GLOBAL_API_LIMIT;
    }

    /**
     * Get timestamps array from the database.
     *
     * @param string $key The storage key
     * @return array Array of Unix timestamps
     */
    private function getTimestamps(string $key): array
    {
        $value = $this->blueprint->dbGet(self::TABLE, $key);

        if ($value === null || !is_array($value)) {
            return [];
        }

        return $value;
    }

    /**
     * Store timestamps array in the database.
     *
     * @param string $key The storage key
     * @param array $timestamps Array of Unix timestamps
     * @return void
     */
    private function setTimestamps(string $key, array $timestamps): void
    {
        $this->blueprint->dbSet(self::TABLE, $key, $timestamps);
    }

    /**
     * Remove timestamps older than the window size (60 seconds).
     *
     * @param array $timestamps Array of Unix timestamps
     * @return array Filtered array with only timestamps within the current window
     */
    private function cleanExpiredTimestamps(array $timestamps): array
    {
        $cutoff = time() - self::WINDOW_SIZE;

        return array_values(array_filter($timestamps, function (int $timestamp) use ($cutoff) {
            return $timestamp > $cutoff;
        }));
    }

    /**
     * Calculate seconds until the oldest timestamp in the window expires.
     *
     * This gives the number of seconds until at least one slot frees up in the
     * rate limit window.
     *
     * @param array $timestamps Array of Unix timestamps (should be non-empty)
     * @return int Seconds until reset (minimum 1)
     */
    private function getSecondsUntilReset(array $timestamps): int
    {
        if (empty($timestamps)) {
            return 0;
        }

        $oldest = min($timestamps);
        $resetAt = $oldest + self::WINDOW_SIZE;
        $secondsRemaining = $resetAt - time();

        return max(1, $secondsRemaining);
    }
}
