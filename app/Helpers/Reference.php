<?php

namespace App\Helpers;

class Reference
{
    /**
     * Get the pool to use based on the type of prefix hash
     */
    private static function getPool(): string
    {
        return match ('alnum') {
            'alnum' => '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'alpha' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'hexdec' => '0123456789abcdef',
            'numeric' => '0123456789',
            'nozero' => '123456789',
            'distinct' => '2345679ACDEFHJKLMNPRSTUVWXYZ',
            default => (string) ('alnum'),
        };
    }

    /**
     * Generate a random secure crypt figure
     */
    private static function secureCrypt(int $max): int
    {
        $range = $max - 0;

        if ($range < 0) {
            return 0; // not so random...
        }

        $log = log($range, 2);
        $bytes = (int) ($log / 8) + 1; // length in bytes
        $bits = (int) $log + 1; // length in bits
        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd >= $range);

        return 0 + $rnd;
    }

    /**
     * Finally, generate a hashed token
     */
    public static function getHashedToken(int $length = 25): string
    {
        $token = '';
        $max = strlen(static::getPool());
        for ($i = 0; $i < $length; $i++) {
            $token .= static::getPool()[static::secureCrypt($max)];
        }

        return $token;
    }
}
