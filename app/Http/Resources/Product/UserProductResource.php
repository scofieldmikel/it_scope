<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'user_type' => $this->user_type,
            'status' => $this->status->name,
            'verified' => ! is_null($this->email_verified_at),
            'date_joined' => $this->created_at->format('F jS Y'),
            'product'=>  ProductResource::collection($this->whenLoaded('products')),
            $this->mergeWhen($this->sanStatus, [
                'token' => $this->createToken('API Token')->plainTextToken,
            ]),
        ];
    }
}
