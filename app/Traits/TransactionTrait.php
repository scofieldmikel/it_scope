<?php

namespace App\Traits;

use App\Models\Auth\Transaction;
use App\Models\PurchaseTransaction;
use App\Services\Paystack\Paystack;
use Illuminate\Support\Facades\Cache;

trait TransactionTrait
{

    public function saveTransaction($amount, $user, $description, $action , $status = 0): Transaction
    {
        $transaction = new Transaction([
            'description' => $description,
            'amount' => $amount,
            'action' => $action,
            'status' => $status,
        ]);

        $user->transactions()->save($transaction);

        return $transaction;
    }

    public function getBanks(): array
    {
        $banks = [];

        $result = Cache::remember('paystack-banks', '5184000', function () {
            return Paystack::getBanks();
        });

        // foreach ($result['data'] as $option) {
        //     $banks[$option['code']] = $option['name'];
        // }

        return $result;
    }

    public function getAccountName($bank_code, $account_number)
    {
        if (is_numeric($account_number) && (strlen($account_number) === 10) && $bank_code !== '') {
            $result = Paystack::add('account_number', $account_number)->add('bank_code', $bank_code)->resolveBankAccount();
            if ($result['status']) {
                return $result['data']['account_name'];
            } else {
                return '';
            }
        }
    }

    protected function saveChannel(Transaction $transaction, $channel, $status)
    {
        $transaction->channel = $channel;
        $transaction->status = $status;
        $transaction->save();
    }

    public function savePurchaseTransaction($user, $product ,$transaction, $business, $quantity, $status = 0)
    {
        $purchaseTransaction = new PurchaseTransaction([
            'product_id' => $product->id,
            'transaction_id' => $transaction->id,
            'business_id' => $business->id,
            'quantity' => $quantity,
            'status' => $status,
        ]);

        $user->purchase_transactions()->save($purchaseTransaction);

        return $purchaseTransaction;
    }
}
