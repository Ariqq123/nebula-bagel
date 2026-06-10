<?php

namespace Pterodactyl\BlueprintFramework\Extensions\subdomain;

class DnsRecordResult
{
    public bool $success;
    public string $recordId;
    public string $message;
    public array $data;

    public function __construct(bool $success, string $recordId = '', string $message = '', array $data = [])
    {
        $this->success = $success;
        $this->recordId = $recordId;
        $this->message = $message;
        $this->data = $data;
    }

    /**
     * Create a successful DNS record result.
     *
     * @param string $recordId The Cloudflare DNS record ID
     * @param array $data Additional record data from the API response
     * @return self
     */
    public static function success(string $recordId, array $data = []): self
    {
        return new self(true, $recordId, '', $data);
    }

    /**
     * Create a failed DNS record result.
     *
     * @param string $message The failure reason
     * @param array $data Additional error data (e.g., Cloudflare error codes)
     * @return self
     */
    public static function failure(string $message, array $data = []): self
    {
        return new self(false, '', $message, $data);
    }

    /**
     * Check if the failure was due to a conflict (record already exists).
     *
     * @return bool
     */
    public function isConflict(): bool
    {
        if ($this->success) {
            return false;
        }

        // Cloudflare typically returns error code 81057 or 81058 for record conflicts
        foreach ($this->data as $error) {
            if (isset($error['code']) && in_array($error['code'], [81057, 81058])) {
                return true;
            }
        }

        return str_contains(strtolower($this->message), 'already exists');
    }
}
