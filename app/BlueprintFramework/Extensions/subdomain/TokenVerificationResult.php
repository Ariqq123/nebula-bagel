<?php

namespace Pterodactyl\BlueprintFramework\Extensions\subdomain;

class TokenVerificationResult
{
    public bool $valid;
    public string $status;
    public array $permissions;
    public string $message;

    public function __construct(bool $valid, string $status = '', array $permissions = [], string $message = '')
    {
        $this->valid = $valid;
        $this->status = $status;
        $this->permissions = $permissions;
        $this->message = $message;
    }

    /**
     * Create a successful verification result.
     *
     * @param string $status The token status (e.g., "active")
     * @param array $permissions The token permission set
     * @return self
     */
    public static function success(string $status, array $permissions): self
    {
        return new self(true, $status, $permissions);
    }

    /**
     * Create a failed verification result.
     *
     * @param string $message The failure reason
     * @return self
     */
    public static function failure(string $message): self
    {
        return new self(false, '', [], $message);
    }

    /**
     * Check whether the token has the required permissions (Zone:Read and DNS:Edit).
     *
     * @return bool
     */
    public function hasRequiredPermissions(): bool
    {
        $hasZoneRead = false;
        $hasDnsEdit = false;

        foreach ($this->permissions as $permission) {
            if (is_string($permission)) {
                if (stripos($permission, 'Zone') !== false && stripos($permission, 'Read') !== false) {
                    $hasZoneRead = true;
                }
                if (stripos($permission, 'DNS') !== false && stripos($permission, 'Edit') !== false) {
                    $hasDnsEdit = true;
                }
            } elseif (is_array($permission)) {
                // Handle permission objects with keys like "id", "resources", "effect", etc.
                $permStr = json_encode($permission);
                if (stripos($permStr, 'Zone') !== false && stripos($permStr, 'Read') !== false) {
                    $hasZoneRead = true;
                }
                if (stripos($permStr, 'DNS') !== false && stripos($permStr, 'Edit') !== false) {
                    $hasDnsEdit = true;
                }
            }
        }

        return $hasZoneRead && $hasDnsEdit;
    }
}
