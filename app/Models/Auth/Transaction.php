<?php

namespace App\Models\Auth;

use App\Models\User;
use App\Casts\KoboNaira;
use App\Helpers\Reference;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    const SUCCESSFUL = 1;

    const PENDING = 0;

    const FAILED = 2;

    const APPROVED = 3;

    const ABANDON = 4;

    const ON_HOLD = 5; 

    const TO_MAIN = 6;

    const BACKBUYER = 7;

    const REFUND = 8;


    const PAYSTACK = 'Paystack';


    protected $guarded = ['id'];

    protected static function booted()
    {
        static::creating(function ($transaction) {
            $transaction->reference = strtolower(Reference::getHashedToken());
        });
    }

    protected $casts = [
        'amount' => KoboNaira::class,
        'data' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getStatus(): string
    {
        return match ((int) $this->status) {
            Transaction::SUCCESSFUL => 'Success',
            Transaction::APPROVED => 'Approved',
            Transaction::ABANDON => 'Abandoned',
            Transaction::PENDING => 'Pending',
            Transaction::FAILED => 'Failed',
            Transaction::ON_HOLD => 'On Hold',
            Transaction::TO_MAIN => 'To Main',
            Transaction::BACKBUYER => 'Back2Buyer',
            Transaction::REFUND => 'Refund',

            default => 'Pending'
        };
    }

}
