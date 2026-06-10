<?php

namespace Pterodactyl\BlueprintFramework\Extensions\subdomain;

class ValidationResult
{
    public bool $valid;
    public string $message;

    public function __construct(bool $valid, string $message = '')
    {
        $this->valid = $valid;
        $this->message = $message;
    }

    public static function success(): self
    {
        return new self(true);
    }

    public static function failure(string $message): self
    {
        return new self(false, $message);
    }
}
