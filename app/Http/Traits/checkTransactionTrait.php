<?php

namespace App\Http\Traits;

use App\Helpers\Misc;
use App\Models\Auth\Transaction;
use App\Models\Transaction\Review;
use App\Models\User;

trait checkTransactionTrait
{
    use HasApiResponse, TransactionTrait;

    public function transaction(User $user, Review $review): Transaction
    {

        if ($review->payment_type == Review::FULL_PAYMENT) {
            if ($review->coupon()->exists() && ! $review->coupon->used) {
                $review->coupon->used = true;
                $review->coupon->user()->associate($user);
                $review->coupon->save();
                $amount = $review->coupon->discount($review);

                return $this->saveTransaction($amount, $user, 'Service Charge For Full Payment '.$review->id, 'Service Charge');
            }

            return $this->saveTransaction(Misc::settings('service')['flickify_service_charge'], $user, 'Service Charge For Full Payment '.$review->id, 'Service Charge');
        }

        return $this->saveTransaction(Misc::settings('service')['service_charge'], $user, 'Service Charge For Review '.$review->id, 'Service Charge');
    }
}
