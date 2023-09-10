<?php

namespace App\Services\Paystack;

use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;

trait Transaction
{
    /**
     * @throws Exception
     */
    public function initialize()
    {
        // try {
            $this->post('transaction/initialize');

            return $this->response;
        // } catch (RequestException $e) {
        //     Log::alert($e->getMessage());
        //     throw new Exception('Cannot Iniatilize Transaction');
        // }
    }

    /**
     * @throws Exception
     */
    public function verify($reference)
    {
        $this->get('transaction/verify/'.$reference);

        return $this->response;
    }

    public function transfer_verify($reference)
    {
        $this->get('transfer/verify/'.$reference);

        return $this->response;
    }

    public function charge()
    {
        try {
            $this->post('charge');

            return $this->response;
        } catch (RequestException $e) {
            Log::alert($e->getMessage());
            throw new Exception('USSD Cant Be Generated At This Time');
        }
    }

    /**
     * @throws RequestException
     */
    public function partial_debit()
    {
        $this->add('currency', 'NGN');
        $this->post('transaction/partial_debit');

        return $this->response;
    }

    public function dedicated_account()
    {
        try {
            $this->post('dedicated_account');

            return $this->response;
        } catch (RequestException $e) {
            Log::alert(print_r($e->getMessage(), true));
            //dump($e->getMessage());
            throw new Exception('Unable to create an account, Please reach out to the admin');
        }
    }

    /**
     * @throws RequestException
     */
    public function authorization($partial_debit = false)
    {
        $partial_debit ? $this->partial_debit() : $this->post('transaction/charge_authorization');

        return $this->response;
    }

    /**
     * @throws RequestException
     */
    public function transfer_recipient()
    {
        $this->post('transferrecipient');

        return $this->response;
    }

    /**
     * @throws RequestException
     */
    public function transfer()
    {
        $this->post('transfer');

        return $this->response;
    }

    /**
     * @throws RequestException
     */
    public function finalize_transfer()
    {
        $this->post('transfer/finalize_transfer');

        return $this->response;
    }
}
