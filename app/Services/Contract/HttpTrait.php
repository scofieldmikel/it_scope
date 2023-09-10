<?php

namespace App\Services\Contract;

use Illuminate\Http\Client\RequestException;

trait HttpTrait
{
    public array $body = [];

    protected $response;

    protected string $agent;

    public function add($key, $value): static
    {
        $this->body[$key] = $value;

        return $this;
    }

    /**
     * @throws RequestException
     */
    protected function get($url, $getUrl = false)
    {
        if ($getUrl) {
            $this->response = $this->baseUrl.$url;

            return;
        }
        $this->response = $this->client->withUserAgent($this->agent)->get($this->baseUrl.$url, $this->body)->throw()->json();
    }

    public function getUrl($url): string
    {
        return $this->baseUrl.$url;
    }

    /**
     * @throws RequestException
     */
    protected function post($url): void
    {
        $this->response = $this->client->post($this->baseUrl.$url, $this->body)->throw()->json();
    }

    /**
     * @throws RequestException
     */
    protected function multi_post($name, $content, $filename, $url): void
    {
        $this->response = $this->client->attach($name, $content, $filename)->post($this->baseUrl.$url, $this->body)->throw()->json();
    }

    protected function put($url)
    {
        $this->response = $this->client->put($this->baseUrl.$url, $this->body)->throw()->json();
    }

    protected function prepareUserAgent(): void
    {
        $name = config('app.name');
        $url = config('app.url');
        $email = 'hello@flickwheel.com';
        $version = '1.0';
        $this->agent = "{$name}/{$version} - {$url} | {$email}";
    }
}
