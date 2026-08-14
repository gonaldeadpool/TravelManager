<?php

namespace App\Support;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Throwable;

class TwoFactorAuthenticator
{
    private const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public function generateSecret(int $length = 32): string
    {
        $secret = '';

        for ($i = 0; $i < $length; $i++) {
            $secret .= self::BASE32_ALPHABET[random_int(0, strlen(self::BASE32_ALPHABET) - 1)];
        }

        return $secret;
    }

    public function verify(string $secret, string $code, int $window = 1): bool
    {
        $normalizedCode = preg_replace('/\s+/', '', $code) ?? '';

        if (! preg_match('/^\d{6}$/', $normalizedCode)) {
            return false;
        }

        $timeSlice = (int) floor(time() / 30);

        for ($i = -$window; $i <= $window; $i++) {
            if (hash_equals($this->totp($secret, $timeSlice + $i), $normalizedCode)) {
                return true;
            }
        }

        return false;
    }

    public function provisioningUri(string $issuer, string $accountName, string $secret): string
    {
        $label = rawurlencode($issuer.':'.$accountName);
        $issuerEncoded = rawurlencode($issuer);

        return "otpauth://totp/{$label}?secret={$secret}&issuer={$issuerEncoded}&algorithm=SHA1&digits=6&period=30";
    }

    /**
     * @return array<int, string>
     */
    public function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            $codes[] = Str::upper(Str::random(5).'-'.Str::random(5));
        }

        return $codes;
    }

    public function encryptSecret(string $secret): string
    {
        return Crypt::encryptString($secret);
    }

    public function decryptSecret(string $encryptedSecret): ?string
    {
        try {
            return Crypt::decryptString($encryptedSecret);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param array<int, string> $codes
     */
    public function encryptRecoveryCodes(array $codes): string
    {
        return Crypt::encryptString(json_encode(array_values($codes), JSON_THROW_ON_ERROR));
    }

    /**
     * @return array<int, string>
     */
    public function decryptRecoveryCodes(string $encryptedCodes): array
    {
        try {
            $decoded = json_decode(Crypt::decryptString($encryptedCodes), true, 512, JSON_THROW_ON_ERROR);

            return is_array($decoded) ? array_values(array_filter($decoded, 'is_string')) : [];
        } catch (Throwable) {
            return [];
        }
    }

    private function totp(string $secret, int $timeSlice): string
    {
        $secretKey = $this->base32Decode($secret);
        $time = pack('N*', 0).pack('N*', $timeSlice);
        $hm = hash_hmac('sha1', $time, $secretKey, true);
        $offset = ord(substr($hm, -1)) & 0x0F;
        $binary = (
            ((ord($hm[$offset]) & 0x7F) << 24) |
            ((ord($hm[$offset + 1]) & 0xFF) << 16) |
            ((ord($hm[$offset + 2]) & 0xFF) << 8) |
            (ord($hm[$offset + 3]) & 0xFF)
        );

        return str_pad((string) ($binary % 1000000), 6, '0', STR_PAD_LEFT);
    }

    private function base32Decode(string $secret): string
    {
        $secret = strtoupper($secret);
        $secret = preg_replace('/[^A-Z2-7]/', '', $secret) ?? '';

        $binary = '';

        for ($i = 0, $length = strlen($secret); $i < $length; $i++) {
            $position = strpos(self::BASE32_ALPHABET, $secret[$i]);

            if ($position === false) {
                continue;
            }

            $binary .= str_pad(decbin($position), 5, '0', STR_PAD_LEFT);
        }

        $decoded = '';

        for ($i = 0, $length = strlen($binary); $i + 8 <= $length; $i += 8) {
            $decoded .= chr(bindec(substr($binary, $i, 8)));
        }

        return $decoded;
    }
}
