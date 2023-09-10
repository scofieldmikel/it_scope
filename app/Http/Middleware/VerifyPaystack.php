<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyPaystack
{

    public array $mode = [
        'local' => 'staging',
        'staging' => 'staging',
        'production' => 'production',
    ];
    
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $this->checkServer($request);

        if ($this->checkSignature($request)) {
            abort(404, 'Not Matched');
        }

        http_response_code(200);

        return $next($request);
    }

    /**
     * @throws Exception
     */
    protected function checkServer(Request $request)
    {
        if (! $request->isMethod('post')) {
            throw new Exception('Method not allowed');
        }

        if (! $request->hasHeader('x-paystack-signature')) {
            throw new Exception('Signature not found');
        }
    }

    protected function checkSignature(Request $request): bool
    {
        return $request->header('x-paystack-signature') !== hash_hmac('sha512', $request->getContent(), config('services.paystack.'.$this->mode[config('app.env')].'SecretKey'));
    }
}
