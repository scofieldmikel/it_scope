<?php

namespace App\Http\Traits;

use App\Services\Paystack\Paystack;
use Illuminate\Support\Facades\Cache;

trait Bank
{
    protected function getBanks(): array
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
}
