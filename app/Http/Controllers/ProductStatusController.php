<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductStatus;
use App\Http\Resources\StatusResource;
use App\Traits\Helpers;

class ProductStatusController extends Controller
{
    use Helpers;

    public function getProductStatus()
    {
        return  StatusResource::collection(ProductStatus::all());
    }

    public function getSingleProductStatus(ProductStatus $status)
    {
        return new StatusResource($status);
    }

    public function addStatus(Request $request)
    {
        $status = ProductStatus::create([
            'name' => $request->name
        ]);

        return new StatusResource($status);
    }

    public function updateStatus(ProductStatus $status, Request $request)
    {
        try {
            $status->update([
                'name' => $request->name
            ]);
            
            return new StatusResource($status);
        } catch (\Exception $e) {
            // Handle the update error here, e.g., log the error or return an error response.
            return response()->json(['error' => 'Failed to update status'], 500);
        }
    }
}
