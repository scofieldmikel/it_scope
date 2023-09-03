<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Traits\Helpers;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;
use App\Http\Requests\Product\ProductRequest;

class ProductController extends Controller
{
    use Helpers;

    public function storeProduct(ProductRequest $request)
    {
        $user = auth()->user();

        $product = Product::create([
            'name' => $request->name,
            'status_id' => ProductController::getProductStatusId('Enabled'),
            'business_id' => $user->id,
            'quantity' => $request->quantity,
            'amount' => $request->amount,
        ]);


        return new ProductResource($product);
    }
}
