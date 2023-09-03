<?php

namespace App\Http\Traits;

use App\Models\Auth\Repayment;
use App\Models\Auth\Transaction;
use App\Models\PaystackCustomer as PaystackCreateCustomer;
use App\Models\User;
use App\Services\Paystack\Paystack;
use Illuminate\Support\Arr;

trait paystackCharge
{
    protected function chargePayStack(User $user, $amount, Repayment $repayment, $partial_debit = false)
    {
        if ($user->paystack()->exists()) {
            $payStack = $user->paystack()->where('paying', true)->first();
            if (! is_null($payStack)) {
                $transaction = $this->saveTransaction($amount, $user, 'Repayment For '.$repayment->due_date, 'Repayment Charge');
                $transaction->channel = Transaction::PAYSTACK;
                $transaction->save();

                $repayment->transactions()->syncWithoutDetaching($transaction);

                return Paystack::add('amount', $transaction->amount * 100)
                    ->add('email', $user->email)
                    ->add('authorization_code', $payStack->authorization['authorization_code'])
                    ->add('metadata', [
                        'event_type' => 'repayment',
                        'data' => [
                            'review_id' => $repayment->review_id,
                            'repayment' => $repayment->id,
                        ],
                    ])
                    ->add('reference', $transaction->reference)
                    ->authorization($partial_debit);
            }
        }
    }

    protected function partial_debit()
    {
    }

    protected function all_debit()
    {
    }

    protected function whatIsLeft(Repayment $repayment)
    {
        $amountPaid = $repayment->transactions()->where('status', Transaction::SUCCESSFUL)->sum('amount');

        return $repayment->amount - ($amountPaid / 100);
    }

    protected function createCustomer(User $user)
    {
        $result = Paystack::add('email', $user->email)
            ->add('first_name', $user->first_name)
            ->add('last_name', $user->last_name)
            ->add('phone', $user->phone)
            ->createCustomer();

        PaystackCreateCustomer::updateOrCreate(
            ['user_id' => $user['id'], 'customer_code' => $result['data']['customer_code']],
            [
                'customer_code' => $result['data']['customer_code'],
                'first_name' => $result['data']['first_name'],
                'last_name' => $result['data']['last_name'],
                'identified' => $result['data']['identified'],
            ]
        );
    }

    protected function verifyAndSave(Transaction $transaction)
    {
        if ($transaction->action === 'Transfer') {
            $result = Paystack::transfer_verify($transaction->reference);
        } else {
            $result = Paystack::verify($transaction->reference);
        }

        if (isset($result['data'])) {
            $status = match (strtolower($result['data']['status'])) {
                'success' => 1,
                'failed' => 2,
                'abandoned' => 4,
                default => 0,
            };

            if ($transaction->action === 'Transfer') {
                $transaction->update([
                    'status' => $status,
                    'data' => $result['data'],
                ]);
            } else {
                $transaction->update([
                    'status' => $status,
                    'data' => [
                        'status' => $result['data']['status'],
                        'message' => $result['data']['message'],
                        'gateway_response' => $result['data']['gateway_response'],
                        'method' => $result['data']['channel'],
                        'ip_address' => $result['data']['ip_address'],
                        'authorization' => $this->processAuthorization($result['data']['authorization']),
                        'log' => $result['data']['log'] ?? null,
                    ],
                ]);
            }
        }
    }

    private function processAuthorization($authorization): array
    {
        if (is_null($authorization)) {
            return [];
        }

        return Arr::except($authorization, ['authorization_code']);
    }
}
