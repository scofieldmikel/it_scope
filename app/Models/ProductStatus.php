<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductStatus extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public function product(): HasOne
    {
        return $this->hasOne(Product::class);
    }
}
