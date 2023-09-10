<?php

namespace App\Services\Contract;

trait HttpConfig
{
    public static function __callStatic($method, $arguments)
    {
        return self::process()->{$method}(...$arguments);
    }
}
