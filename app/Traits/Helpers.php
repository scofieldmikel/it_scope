<?php

namespace App\Traits;

use App\Models\ProductStatus;
use App\Models\Status;

trait Helpers
{
    public static function getStatusId(string $name): int
    {
        $status_id = Status::where('name', $name)->pluck('id')->first();
        if (is_null($status_id)) {
            throw new \Exception('Failed To Fetch Status ID');
        }

        return $status_id;
    }

    public static function getStatusName(int $statusID): string
    {
        $status = Status::find($statusID);
        if (is_null($status)) {
            throw new \Exception('Failed To Fetch Status Name');
        }

        return $status->name;
    }

    public static function getProductStatusId(string $name): int
    {
        $product_status_id = ProductStatus::where('name', $name)->pluck('id')->first();
        if (is_null($product_status_id)) {
            throw new \Exception('Failed To Fetch Status ID');
        }

        return $product_status_id;
    }
}
