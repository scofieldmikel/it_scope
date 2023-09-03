<?php

namespace App\Services;

use Exception;

class TotpService
{
    /**
     *  hash method
     */
    protected string $hash;

    /**
     * Double Digits
     *
     * @var []
     */
    protected array $doubleDigits = [0 => '0', 1 => '2', 2 => '4', 3 => '6', 4 => '8', 5 => '1', 6 => '3', 7 => '5', 8 => '7', 9 => '9'];

    /**
     * The secret key
     */
    protected string $secretKey;

    /**
     * Time of execution
     *
     **/
    protected string $time;

    /**
     * The expiration time of the code
     */
    protected int $expiration;

    /**
     * The number of digits in the code
     */
    protected int $codeDigitsNr;

    /**
     * Flag to add or not a checksum at the end of the code
     */
    protected bool $addChecksum = false;

    /**
     * Generated Code
     */
    protected string $generatedCode;

    protected ?string $email;

    /**
     * @throws Exception
     */
    public function __construct($email = null)
    {
        $this->setConstants();
        $this->checkConstants();
        $this->email = is_null($email) ? $email : strtolower($email);
    }

    protected function setConstants()
    {
        $this->setTime();
        $this->secretKey = config('services.otp.secret_key');
        $this->expiration = config('services.otp.expiration_time');
        $this->codeDigitsNr = config('services.otp.digits_no');
        $this->hash = config('services.otp.hash');
    }

    /**
     * @throws \Exception
     */
    protected function checkConstants()
    {
        if (! $this->secretKey) {
            throw new \Exception('Secret Key Not Set');
        }

        if (! $this->expiration) {
            throw new \Exception('Expiration Not Set');
        }

        if ($this->expiration < 0) {
            throw new \Exception('Invalid Expiration Time');
        }

        if (! $this->codeDigitsNr) {
            throw new \Exception('Number Of Code Not Set');
        }

        if (! $this->hash) {
            throw new \Exception('No Hash Method Defined');
        }

        if (! in_array($this->hash, hash_algos())) {
            throw new \Exception('Hash Algo Not Correct');
        }
    }

    /**
     * Generate a keyed hash value using the HMAC method
     *
     * @param  string  $data - data to be hashed
     * @param  bool  $rawOutput - if the output should be binary data or hex
     */
    protected function hmac(string $data, bool $rawOutput = true): string
    {
        return hash_hmac($this->hash, $data, $this->secretKey.$this->email, $rawOutput);
    }

    public function setTime($time = ''): TotpService
    {
        if ($time === '') {
            $this->time = time();
        } else {
            $this->time = $time;
        }

        return $this;
    }

    /**
     * Calculate the checksum of a result
     *
     * @param  int  $num - number
     * @param  int  $digits - number of digits
     */
    public function calcChecksum(int $num, int $digits): int
    {
        $doubleDigit = true;
        $total = 0;
        while ($digits-- > 0) {
            $digit = (int) ($num % 10);
            $num /= 10;
            if ($doubleDigit) {
                $digit = $this->doubleDigits[$digit];
            }
            $total += $digit;
            $doubleDigit = ! $doubleDigit;
        }

        return 10 - $total % 10;
    }

    /**
     * Get the expiration time of the passwordd
     */
    public function getExpirationTime(): int
    {
        return $this->expiration;
    }

    /**
     * Set if it should add or not a checksum at the end of the code
     */
    public function addChecksum(bool $addChecksum): TotpService
    {
        $this->addChecksum = (bool) $addChecksum;

        return $this;
    }

    /**
     * Calculate the one time password
     */
    protected function calculateOTP(int $movingFactor)
    {
        $movingFactor = floor($movingFactor);
        $digits = $this->addChecksum ? ($this->codeDigitsNr + 1) : $this->codeDigitsNr;

        $text = [];
        for ($i = 7; $i >= 0; $i--) {
            $text[] = ($movingFactor & 0xFF);
            $movingFactor >>= 8;
        }
        $text = array_reverse($text);
        foreach ($text as $index => $value) {
            $text[$index] = chr($value);
        }
        $text = implode('', $text);

        $hash = $this->hmac($text);
        $hashLenght = strlen($hash);
        $offset = ord($hash[$hashLenght - 1]) & 0xF;

        $hash = str_split($hash);
        foreach ($hash as $index => $value) {
            $hash[$index] = ord($value);
        }

        $binary = (($hash[$offset] & 0x7F) << 24) | (($hash[$offset + 1] & 0xFF) << 16) | (($hash[$offset + 2] & 0xFF) << 8) | ($hash[$offset + 3] & 0xFF);

        $otp = $binary % pow(10, $this->codeDigitsNr);
        if ($this->addChecksum) {
            $otp = ($otp * 10) + $this->calcChecksum($otp, $this->codeDigitsNr);
        }

        $this->generatedCode = str_pad($otp, $digits, '0', STR_PAD_LEFT);
    }

    /**
     * Generate a new code
     */
    public function generateCode(): string
    {
        $this->calculateOTP($this->time / $this->expiration);

        return $this->generatedCode;
    }

    /**
     * Set the expiration time (in seconds) of a password
     * The actual time is (at 'random' - time based) between the input expiration time and input expiration time + 50%
     * For example: if $expiration=30 => $actualExpiration=[30,45]
     *
     * If the expiration time is not valid it will set it to the default value
     */
    public function setExpirationTime(int $expiration): TotpService
    {
        if ($expiration > 0) {
            $this->expiration = $expiration;
        }

        return $this;
    }

    /**
     * Set the number of digits the code should have
     * If it's less than 0 or bigger than 10 it should result to default
     *
     * @param  int  $digits - the number of digits the computed code shoud have
     */
    public function setDigitsNumber(int $digits): TotpService
    {
        if ($digits > 0 && $digits <= count($this->doubleDigits)) {
            $this->codeDigitsNr = $digits;
        }

        return $this;
    }

    /**
     * Validate code
     */
    public function validateCode(string $code): bool
    {
        $this->calculateOTP($this->time / $this->expiration);

        return $code === $this->generatedCode;
    }
}
