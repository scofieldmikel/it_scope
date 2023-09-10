<?php

namespace App\Services\Paystack;

use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;

trait Customer
{
    /**
     * @throws Exception
     */
    public function createCustomer()
    {
        try {
            $this->post('customer');

            return $this->response;
        } catch (RequestException $e) {
            Log::alert($e->getMessage());
            throw new Exception('Cannot Create Transaction');
        }
    }

    /**
     * @throws RequestException
     */
    public function fetchCustomer($code)
    {
        $this->get('customer/'.$code);

        return $this->response;
    }

    public function updateCustomer($code)
    {
        $this->put('customer/'.$code);

        return $this->response;
    }

    /**
     * @throws RequestException
     */
    public function validateCustomer($code)
    {
        $this->post('customer/'.$code.'/identification');

        return $this->response;
    }
}
