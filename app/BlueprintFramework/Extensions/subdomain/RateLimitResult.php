<?php

namespace Pterodactyl\BlueprintFramework\Extensions\subdomain;

class RateLimitResult
{
    public bool $allowed;
    public int $secondsUntilReset;
    public string $limitType;

    public function __construct(bool $allowed, int $secondsUntilReset = 0, string $limitType = '')
    {
        $this->allowed = $allowed;
        $this->secondsUntilReset = $secondsUntilReset;
        $this->limitType = $limitType;
    }

    /**
     * Create a result indicating the request is allowed.
     *
     * @return self
     */
    public static function allowed(): self
    {
        return new self(true, 0, '');
    }

    /**
     * Create a result indicating the request is denied due to a rate limit.
     *
     * @param string $limitType The type of limit that was exceeded (e.g., 'admin_subdomain_create', 'global_api', 'cloudflare_429')
     * @param int $secondsUntilReset Number of seconds until the limit resets
     * @return self
     */
    public static function denied(string $limitType, int $secondsUntilReset): self
    {
        return new self(false, $secondsUntilReset, $limitType);
    }
}
