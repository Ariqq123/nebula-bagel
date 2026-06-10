<?php

namespace Pterodactyl\BlueprintFramework\Extensions\subdomain;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class EncryptionService
{
    /**
     * Encrypt a plaintext value using Laravel's AES-256-CBC encryption.
     *
     * Uses the application's APP_KEY for encryption. The resulting ciphertext
     * includes a MAC (message authentication code) for integrity verification.
     *
     * @param string $plaintext The plaintext value to encrypt
     * @return string The encrypted ciphertext (base64 encoded JSON payload)
     */
    public function encrypt(string $plaintext): string
    {
        return Crypt::encryptString($plaintext);
    }

    /**
     * Decrypt an encrypted value back to plaintext.
     *
     * Verifies the MAC (message authentication code) before returning the
     * plaintext value. If the ciphertext has been tampered with or is corrupted,
     * a DecryptException is thrown.
     *
     * @param string $ciphertext The encrypted ciphertext to decrypt
     * @return string The decrypted plaintext value
     *
     * @throws DecryptException If the ciphertext is invalid, tampered with, or corrupted
     */
    public function decrypt(string $ciphertext): string
    {
        try {
            return Crypt::decryptString($ciphertext);
        } catch (DecryptException $e) {
            throw new DecryptException(
                'Failed to decrypt value: the ciphertext is invalid or has been tampered with.'
            );
        }
    }

    /**
     * Check if a value appears to be an encrypted Laravel payload.
     *
     * Validates that the value is a base64-encoded JSON object containing
     * the required 'iv', 'value', and 'mac' fields that Laravel's encrypter
     * produces.
     *
     * @param string $value The value to check
     * @return bool True if the value appears to be a valid encrypted payload
     */
    public function isEncrypted(string $value): bool
    {
        if (empty($value)) {
            return false;
        }

        $decoded = base64_decode($value, true);

        if ($decoded === false) {
            return false;
        }

        $payload = json_decode($decoded, true);

        if (!is_array($payload)) {
            return false;
        }

        return isset($payload['iv']) && isset($payload['value']) && isset($payload['mac']);
    }
}
