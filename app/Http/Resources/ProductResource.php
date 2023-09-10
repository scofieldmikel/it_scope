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
        // dd($this);
        return [
            'id' => $this->id,
            'name' => $this->name,
            'quantity' => $this->quantity,
            'amount' => $this->amount,
            'status' => $this->productStatus->name,
            'image' => $this->images(),
            'business_owner'=> new UserResource($this->whenLoaded('user')),

        ];
    }

    public function images()
    {
        foreach ($this->images as $image) {
            return [
                'image_url' => $image->url,
                'image_extenstion' => $image->extension
            ];
        }
        
    }
}
