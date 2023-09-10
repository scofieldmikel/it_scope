<?php

namespace App\Services\Paystack;

use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;

trait Identity
{
    public function resolveBankAccount()
    {
        try {
            $this->get('bank/resolve');

            return $this->response;
        } catch (RequestException $e) {
            abort($e->getCode(), 'Could not resolve account name. Check parameters or try again');
        }
    }

    /**
     * @throws Exception
     */
    public function resolveBvn()
    {
        try {
            //dd($this->body);
            $this->post('bvn/match');

            return $this->response;
        } catch (RequestException $e) {
            Log::alert($e->getMessage());
            throw new Exception('Your BVN Doesnt Match');
        }
    }
}
