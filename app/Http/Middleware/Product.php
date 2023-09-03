<?php

namespace App\Http\Middleware;

use Closure;
use App\Traits\Helpers;
use Illuminate\Http\Request;
use App\Http\Traits\HasApiResponse;
use Symfony\Component\HttpFoundation\Response;

class Product
{
    use HasApiResponse, Helpers;
    
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if($request->user()->user_type !== 'business')
        {
            return $this->forbiddenResponse('Sorry, You cannot create a product, only business account can create a product!');
        }
        return $next($request);
    }
}
