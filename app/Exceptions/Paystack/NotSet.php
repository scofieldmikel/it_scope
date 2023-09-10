<?php

namespace App\Exceptions\Paystack;

use App\Services\Paystack\Paystack;
use Exception;

class NotSet extends Exception
{
    public static function keys(Paystack $payStack): self
    {
        return new static('PayStack '.ucwords($payStack->mode[config('app.env')]).' Properties Not Set');
    }
}
