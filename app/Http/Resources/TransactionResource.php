<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            'reference' =>$this->reference,
            'amount' => number_format($this->amount, 2),
            'status' => $this->getStatus(),
            'payment_channel' => $this->channel,
            'created_at'  => $this->created_at->format('l jS F, Y h:i:sa') ,
        ];
    }
}
