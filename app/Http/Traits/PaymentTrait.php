<?php

namespace App\Http\Traits;

use App\Http\Controllers\Webhooks\WalletWebhook;
use App\Models\Auth\Paystack as AuthPaystack;
use App\Models\Auth\Transaction;
use App\Models\User;
use App\Services\Paystack\Paystack;
use Illuminate\Http\JsonResponse;

trait PaymentTrait
{
    protected array $url = [
        'local' => 'http://mechanic.test',
        'staging' => 'https://flickauto.com',
        'testing' => 'http://mechanic.test',
        'production' => 'https://flickwheel.com',
    ];

    /**
     * @throws \Throwable
     */
    protected function paymentMethod($payment_type, Transaction $transaction, User $user, $data): JsonResponse
    {
        return match ($payment_type) {
            'paystack' => $this->withPaystack($transaction, $user, $data, ['card', 'bank', 'ussd', 'qr', 'mobile_money', 'bank_transfer']),
            'charge' => $this->withCardCharge($transaction, $user, $data),
            'bank' => $this->withPaystack($transaction, $user, $data, ['bank']),
            'wallet' => $this->withWallet($transaction, $user, $data),
            default => $this->withPaystack($transaction, $user, $data, ['card'])
        };
    }

    /**
     * @throws \Throwable
     */
    protected function withWallet(Transaction $transaction, User $user, $data)
    {
        if ($transaction->amount > $user->real) {
            return $this->badRequestResponse('Insufficient Balance, Kindly Topup Your Wallet');
        }

        (new WalletWebhook($user, $transaction))->handle($data, $transaction->amount);

        return $this->okResponse('Paid Successfully', [
            'mode' => 'wallet',
            'reference' => $transaction->reference,
        ]);
    }

    protected function withPaystack(Transaction $transaction, User $user, $data, $channel): JsonResponse
    {
        $this->saveChannel($transaction);

        $result = $this->processPaystack($transaction, $user, $data, $channel);

        return $this->okResponse('Authorization URL created', $result['data']);
    }

    protected function withCardCharge(Transaction $transaction, User $user, $data)
    {
        $payStack = $user->paystack()->where('paying', true)->first();
        if (is_null($payStack)) {
            return $this->badRequestResponse('You have not set a default card');
        }

        $this->saveChannel($transaction);

        $result = $this->processPaystackCharge($transaction, $payStack, $data);

        if (isset($result['data']['status']) && $result['data']['status'] === 'success') {
            return $this->okResponse('Charge attempted', [
                'mode' => 'Charge attempted',
                'reference' => $transaction->reference,
            ]);
        } else {
            return $this->badRequestResponse('We were unable to charge your account');
        }
    }

    protected function processPaystack(Transaction $transaction, User $user, array $data = [], $channel = ['card'])
    {
        return Paystack::add('amount', $transaction->amount * 100)
            ->add('email', $user->email)
            ->add('currency', 'NGN')
            ->add('channels', $channel)
            ->add('metadata', $data)
            ->add('reference', $transaction->reference)
            ->add('callback_url', $this->url[config('app.env')])
            ->initialize();
    }

    protected function processPaystackCharge(Transaction $transaction, AuthPaystack $payStack, $data = [])
    {
        return Paystack::add('amount', $transaction->amount * 100)
            ->add('email', $payStack['customer']['email'])
            ->add('authorization_code', $payStack->authorization['authorization_code'])
            ->add('metadata', $data)
            ->add('reference', $transaction->reference)
            ->authorization();
    }
}
