<?php

namespace App\Http\Traits;

use App\Models\Auth\Transaction;
use App\Models\User;
use App\Services\Paystack\Paystack;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

trait TransactionTrait
{
    public function saveTransaction($total, $user, $description, $action = 'Payment', $status = 0): Transaction
    {
        $transaction = new Transaction([
            'description' => $description,
            'amount' => $total,
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

        foreach ($result['data'] as $option) {
            $banks[$option['code']] = $option['name'];
        }

        return $banks;
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

    /**
     * @throws \Throwable
     */
    public function deductFromWallet($amount, User $user, $description, $action)
    {
        DB::beginTransaction();
        try {
            $transaction = $this->saveTransaction($amount, $user, $description, $action, 1);
        } catch (Exception $e) {
            DB::rollback();
        }
    }

    protected function saveChannel(Transaction $transaction, $channel = Transaction::PAYSTACK)
    {
        $transaction->channel = $channel;
        $transaction->save();
    }
}
