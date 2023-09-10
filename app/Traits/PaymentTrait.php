<?php

namespace App\Traits;

use App\Models\User;
use App\Models\Auth\Transaction;
use App\Services\Paystack\Paystack;

trait PaymentTrait
{
    protected array $url = [
        'local' => 'http://it_scope.test/',
        'staging' => 'http://it_scope.test/',
        'testing' => 'http://it_scope.test/',
        'production' => 'http://it_scope.test/',
    ];

    protected function paymentMethod($payment_type, $transaction, User $user, $data)
    {
        return match ($payment_type) {
            'paystack' => $this->withPaystack($transaction, $user, $data, ['card', 'bank', 'ussd', 'qr', 'mobile_money', 'bank_transfer']),
            'charge' => $this->withCardCharge($transaction, $user, $data),
            'bank' => $this->withPaystack($transaction, $user, $data, ['bank']),
            'wallet' => $this->withWallet($transaction, $user, $data),
            default => $this->withPaystack($transaction, $user, $data, ['card'])
        };
    }

    protected function withPaystack($transaction, User $user, $data, $channel)
    {
        $this->saveChannel($transaction, Transaction::PAYSTACK, Transaction::PENDING);

        $result = $this->processPaystack($transaction, $user, $data, $channel);

        return ['data' => $result];
    }

    protected function processPaystack($transaction, User $user, array $data = [], $channel = ['card', 'card'])
    {
        $amount = $transaction['amount'];

        return Paystack::add('amount', $amount * 100)
            ->add('email', $user->email)
            ->add('currency', 'NGN')
            ->add('channels', $channel)
            ->add('metadata', $data)
            ->add('reference', $transaction['reference'])
            ->add('callback_url', $this->url[config('app.env')])
            ->initialize();
    }

}
