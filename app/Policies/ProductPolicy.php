<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Product;
use Illuminate\Auth\Access\Response;

class ProductPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function viewPoduct(User $user, Product $product): Response
    {
        return $user->id === $product->business_id
            ? Response::allow()
            : Response::deny('You do not own this product.');
    }

    public function update(User $user, Product $product)
    {
        return $user->id === $product->business_id
        ? Response::allow()
        : Response::deny('You do not own this product.');
    }
}
