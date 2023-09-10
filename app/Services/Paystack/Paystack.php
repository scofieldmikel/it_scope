<?php

namespace App\Services\Paystack;

use App\Services\Contract\HttpConfig;

/**
 * @method static add(string $string, string $string1)
 * @method static addAccountNumber(mixed $account_number)
 * @method static getBanks()
 * @method static verify(mixed $reference)
 * @method static fetchCustomer($customer_code)
 * @method static transfer_verify(mixed $reference)
 */
class Paystack
{
    use HttpConfig;

    protected static function process(): Config
    {
        return new Config();
    }
}
