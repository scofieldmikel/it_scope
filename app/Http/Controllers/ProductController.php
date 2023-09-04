<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Traits\Helpers;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;
use App\Http\Requests\Product\ProductRequest;
use App\Http\Resources\Product\UserProductResource;

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

    public function getAllProduct()
    {
        $product = Product::where('status_id', ProductController::getProductStatusId('Enabled'))->with('user')->paginate(10);
        // return $product;
        return ProductResource::collection($product);
    }

    public function getAllUserProduct()
    {
        $user = User::where('status_id', ProductController::getStatusId('Active'))->with('products')->paginate(10);
        // return $user;
        return UserProductResource::collection($user);
    }
}
