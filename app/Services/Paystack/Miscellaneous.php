<?php

namespace App\Services\Paystack;

use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

trait Miscellaneous
{
    /**
     * @throws Exception
     */
    public function getBanks()
    {
        try {
            return Cache::rememberForever('getBank', function () {
                $this->get('bank');

                return $this->response;
            });
        } catch (RequestException $e) {
            Log::alert($e->getMessage());
            throw new Exception('Unable To List Banks');
        }
    }

    public function getInternetBank()
    {
        try {
            return Cache::rememberForever('internetBank', function () {
                $this->get('bank');

                return $this->response;
            });
        } catch (RequestException $e) {
            Log::alert($e->getMessage());
            throw new Exception('Unable To List Banks');
        }
    }
}
