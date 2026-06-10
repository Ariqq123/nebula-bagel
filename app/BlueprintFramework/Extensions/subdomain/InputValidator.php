<?php

namespace Pterodactyl\BlueprintFramework\Extensions\subdomain;

class InputValidator
{
    /**
     * Maximum length for a single DNS label.
     */
    private const MAX_LABEL_LENGTH = 63;

    /**
     * Maximum length for a fully qualified domain name.
     */
    private const MAX_FQDN_LENGTH = 253;

    /**
     * Regex pattern for valid Cloudflare API tokens.
     */
    private const TOKEN_PATTERN = '/^[A-Za-z0-9_\-]{40}$/';

    /**
     * Regex pattern for valid subdomain names.
     */
    private const SUBDOMAIN_PATTERN = '/^[a-z0-9]([a-z0-9\-]{0,61}[a-z0-9])?$/';

    /**
     * Regex pattern for valid Cloudflare zone IDs.
     */
    private const ZONE_ID_PATTERN = '/^[0-9a-f]{32}$/';

    /**
     * Allowed DNS record types.
     */
    private const ALLOWED_RECORD_TYPES = ['A', 'AAAA', 'CNAME'];

    /**
     * Validate a Cloudflare API token format.
     *
     * Token must be exactly 40 characters consisting of alphanumeric characters,
     * underscores, and hyphens.
     */
    public function validateApiToken(string $token): ValidationResult
    {
        if (preg_match(self::TOKEN_PATTERN, $token) !== 1) {
            return ValidationResult::failure(
                'Invalid API token format. Token must be exactly 40 characters consisting of letters, numbers, underscores, and hyphens.'
            );
        }

        return ValidationResult::success();
    }

    /**
     * Validate a subdomain name.
     *
     * Sanitization is applied before validation (requirement 9.8).
     * The name must match the subdomain pattern, contain no dots,
     * be at most 63 characters (label length), and produce an FQDN
     * no longer than 253 characters.
     *
     * @param string $name The subdomain name to validate.
     * @param bool $wildcardAllowed Whether wildcard (*) prefixes are permitted.
     * @param string $zoneName The zone name for FQDN length calculation (e.g., "example.com").
     */
    public function validateSubdomainName(string $name, bool $wildcardAllowed = false, string $zoneName = ''): ValidationResult
    {
        // Apply sanitization before validation (requirement 9.8)
        $sanitized = $this->sanitizeSubdomainName($name);

        // Check for wildcard prefix on the original (pre-sanitization) input
        if (str_starts_with(trim($name), '*')) {
            if (!$wildcardAllowed) {
                return ValidationResult::failure(
                    'Wildcard subdomains are not permitted. Subdomain names cannot begin with an asterisk (*) character.'
                );
            }
        }

        // Check for dots in the original input (before sanitization removes them)
        if (str_contains($name, '.')) {
            return ValidationResult::failure(
                'Dots are not permitted in subdomain names. Please provide only the subdomain label without the domain.'
            );
        }

        // Validate the sanitized name is not empty
        if ($sanitized === '') {
            return ValidationResult::failure(
                'Subdomain name cannot be empty after sanitization.'
            );
        }

        // Enforce maximum label length (63 characters)
        if (strlen($sanitized) > self::MAX_LABEL_LENGTH) {
            return ValidationResult::failure(
                'Subdomain label must not exceed 63 characters.'
            );
        }

        // Enforce maximum FQDN length (253 characters) if zone name provided
        if ($zoneName !== '') {
            $fqdn = $sanitized . '.' . $zoneName;
            if (strlen($fqdn) > self::MAX_FQDN_LENGTH) {
                return ValidationResult::failure(
                    'The fully qualified domain name (FQDN) must not exceed 253 characters.'
                );
            }
        }

        // Validate against the subdomain regex pattern
        if (preg_match(self::SUBDOMAIN_PATTERN, $sanitized) !== 1) {
            return ValidationResult::failure(
                'Invalid subdomain name format. Subdomain must start and end with a lowercase letter or number, and may contain lowercase letters, numbers, and hyphens in between.'
            );
        }

        return ValidationResult::success();
    }

    /**
     * Validate a DNS record type.
     *
     * Only A, AAAA, and CNAME record types are accepted.
     */
    public function validateRecordType(string $type): ValidationResult
    {
        if (!in_array($type, self::ALLOWED_RECORD_TYPES, true)) {
            return ValidationResult::failure(
                'Invalid record type. Only A, AAAA, and CNAME record types are supported.'
            );
        }

        return ValidationResult::success();
    }

    /**
     * Validate a DNS record target based on record type.
     *
     * - A records require a valid IPv4 address.
     * - AAAA records require a valid IPv6 address.
     * - CNAME records require a valid hostname that is not an IP address.
     */
    public function validateTarget(string $target, string $type): ValidationResult
    {
        switch ($type) {
            case 'A':
                if (filter_var($target, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
                    return ValidationResult::failure(
                        'Invalid target for A record. A valid IPv4 address is required (e.g., 192.168.1.1).'
                    );
                }
                return ValidationResult::success();

            case 'AAAA':
                if (filter_var($target, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false) {
                    return ValidationResult::failure(
                        'Invalid target for AAAA record. A valid IPv6 address is required (e.g., 2001:db8::1).'
                    );
                }
                return ValidationResult::success();

            case 'CNAME':
                // CNAME target must be a valid hostname and NOT an IP address
                if (filter_var($target, FILTER_VALIDATE_IP) !== false) {
                    return ValidationResult::failure(
                        'Invalid target for CNAME record. CNAME targets must be a hostname, not an IP address.'
                    );
                }

                if (!$this->isValidHostname($target)) {
                    return ValidationResult::failure(
                        'Invalid target for CNAME record. A valid hostname is required (e.g., server.example.com).'
                    );
                }
                return ValidationResult::success();

            default:
                return ValidationResult::failure(
                    'Cannot validate target for unsupported record type: ' . $type
                );
        }
    }

    /**
     * Validate a Cloudflare zone ID format.
     *
     * Zone ID must be exactly 32 lowercase hexadecimal characters.
     */
    public function validateZoneId(string $zoneId): ValidationResult
    {
        if (preg_match(self::ZONE_ID_PATTERN, $zoneId) !== 1) {
            return ValidationResult::failure(
                'Invalid zone ID format. Zone ID must be exactly 32 lowercase hexadecimal characters.'
            );
        }

        return ValidationResult::success();
    }

    /**
     * Sanitize a subdomain name.
     *
     * Operations performed in order (requirement 9.8):
     * 1. Trim leading and trailing whitespace
     * 2. Convert all characters to lowercase
     * 3. Remove any characters not matching [a-z0-9-]
     * 4. Remove leading and trailing hyphens
     */
    public function sanitizeSubdomainName(string $name): string
    {
        // Step 1: Trim leading and trailing whitespace
        $name = trim($name);

        // Step 2: Convert to lowercase
        $name = strtolower($name);

        // Step 3: Remove characters not matching [a-z0-9-]
        $name = preg_replace('/[^a-z0-9\-]/', '', $name);

        // Step 4: Remove leading and trailing hyphens
        $name = trim($name, '-');

        return $name;
    }

    /**
     * Validate whether a string is a valid hostname.
     *
     * A valid hostname:
     * - Contains only alphanumeric characters, hyphens, and dots
     * - Each label is 1-63 characters
     * - Does not start or end with a hyphen in any label
     * - Total length does not exceed 253 characters
     * - Has at least two labels (e.g., "example.com")
     */
    private function isValidHostname(string $hostname): bool
    {
        // Cannot be empty
        if ($hostname === '') {
            return false;
        }

        // Total length check
        if (strlen($hostname) > 253) {
            return false;
        }

        // Must contain only valid hostname characters
        if (!preg_match('/^[a-zA-Z0-9.\-]+$/', $hostname)) {
            return false;
        }

        // Split into labels
        $labels = explode('.', $hostname);

        // Must have at least two labels (e.g., "host.domain")
        if (count($labels) < 2) {
            return false;
        }

        foreach ($labels as $label) {
            // Each label must be 1-63 characters
            if ($label === '' || strlen($label) > 63) {
                return false;
            }

            // Labels cannot start or end with a hyphen
            if (str_starts_with($label, '-') || str_ends_with($label, '-')) {
                return false;
            }
        }

        return true;
    }
}
