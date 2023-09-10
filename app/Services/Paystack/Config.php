<?php

namespace App\Services\Paystack;

use App\Exceptions\Paystack\NotSet;
use App\Http\Traits\HasApiResponse;
use App\Services\Contract\HttpTrait;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class Config extends \App\Services\Paystack\Paystack
{
    use Identity, Miscellaneous, Transaction, Customer, HttpTrait;

    public array $mode = [
        'local' => 'staging',
        'staging' => 'staging',
        'testing' => 'staging',
        'production' => 'production',
    ];

    protected string $secretKey;

    protected string $publicKey;

    protected PendingRequest $client;

    protected string $baseUrl = 'https://api.paystack.co/';

    /**
     * @throws NotSet
     */
    public function __construct()
    {
        $this->setConstant();
        $this->checkConstant();
        $this->prepareClient();
        $this->prepareUserAgent();
    }

    protected function setConstant(): void
    {
        $this->secretKey = config('services.paystack.'.$this->mode[config('app.env')].'SecretKey');
        $this->publicKey = config('services.paystack.'.$this->mode[config('app.env')].'PublicKey');
    }

    /**
     * @throws NotSet
     */
    protected function checkConstant(): void
    {
        if (empty($this->secretKey) || empty($this->publicKey)) {
            throw NotSet::keys($this);
        }
    }

    protected function prepareClient(): void
    {
        $this->client = Http::withToken($this->secretKey)->acceptJson();
    }

    protected function unCamelCase($str): string
    {
        $str = preg_replace('/([a-z])([A-Z])/', '\\1_\\2', $str);

        return strtolower($str);
    }

    public function __call($name, $argument)
    {
        $key = str_replace('add', '', $name);

        return $this->add($this->unCamelCase($key), $argument[0]);
    }
}
