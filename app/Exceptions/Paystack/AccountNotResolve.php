<?php

namespace App\Exceptions\Paystack;

use ReflectionClass;

class AccountNotResolve extends PaymentException
{
    public function getShortName(): string
    {
        return (new ReflectionClass($this))->getShortName();
    }
}
