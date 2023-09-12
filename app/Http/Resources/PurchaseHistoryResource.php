<?php

namespace App\Http\Resources;

use App\Models\Auth\Transaction;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseHistoryResource extends JsonResource
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
            'product' => $this->product(),
            'business_owner' => $this->business_owner(),
            'transaction' => $this->transaction(),
            'created_at' => $this->created_at,
            'purchase_quantity' => $this->quantity,
            'purchase_status' => $this->getStatus(),


        ];
    }

    public function product()
    {
        $product = Product::where('id', $this->product_id)->first();
        return new ProductResource($product);
    }

    public function business_owner()
    {
        $owner = User::where('id', $this->business_id)->first();
        return new UserResource($owner);
    }

    public function transaction()
    {
        $transaction = Transaction::where('id', $this->transaction_id)->first();

        return new TransactionResource($transaction);
    }

    public function getStatus(): string
    {
        return match ((int) $this->status) {
            Transaction::SUCCESSFUL => 'Success',
            Transaction::APPROVED => 'Approved',
            Transaction::ABANDON => 'Abandoned',
            Transaction::PENDING => 'Pending',
            Transaction::FAILED => 'Failed',
            Transaction::ON_HOLD => 'On Hold',
            Transaction::TO_MAIN => 'To Main',
            Transaction::BACKBUYER => 'Back2Buyer',
            Transaction::REFUND => 'Refund',

            default => 'Pending'
        };
    }
}
