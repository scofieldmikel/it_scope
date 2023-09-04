<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // dd($this->user);
        return [
            'id' => $this->id,
            'name' => $this->name,
            'quantity' => $this->quantity,
            'amount' => $this->amount,
            'status' => $this->productStatus->name,
            // 'business'=> $this->user[0]['name'],
            'business_owner'=> new UserResource($this->whenLoaded('user')),

        ];
    }
}
