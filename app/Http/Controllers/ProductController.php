<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Image;
use App\Models\Product;
use App\Traits\Helpers;
use App\Events\UploadImage;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Requests\Product\ProductRequest;
use App\Http\Resources\Product\UserProductResource;
use App\Http\Traits\HasApiResponse;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ProductController extends Controller
{
    use Helpers, HasApiResponse;

    public function storeProduct(ProductRequest $request)
    {
        $user = auth()->user();

        $product_image = 
            Cloudinary::upload($request->file('image')
                    ->getRealPath(), ['folder' => 'it_scope/product'])
                ->getSecurePath();

        $product = Product::create([
            'name' => $request->name,
            'status_id' => ProductController::getProductStatusId('Enabled'),
            'user_id' => $user->id,
            'quantity' => $request->quantity,
            'amount' => $request->amount,
        ]);

        UploadImage::dispatch($product->id, $product_image);

        return new ProductResource($product);
    }

    public function getAllProduct()
    {
        $product = Product::where('status_id', ProductController::getProductStatusId('Enabled'))->with('user')->paginate(10);
        return ProductResource::collection($product);
    }

    public function getAllUserProduct()
    {
        $user = User::where('status_id', ProductController::getStatusId('Active'))->with('products')->paginate(10);
        return UserProductResource::collection($user);
    }

    public function updateProduct(Product $product, UpdateProductRequest $request)
    {
        $this->authorize('update', $product);

        $product->update([
            'quantity' => $product->quantity + $request-> quantity,
            'amount' => $request-> amount,
            "status_id" => $request->status_id,
        ]);
        return new ProductResource($product);
    }

    public function getMyProduct()
    {
        $user = auth()->user();
        $product = $user->products()->latest()->paginate(10);

        return ProductResource::collection($product);
    }

    public function productDetails(Product $product)
    {
        return new ProductResource($product->load('user'));
    }
}
