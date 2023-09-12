<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Traits\PaymentTrait;
use Illuminate\Http\Request;
use App\Traits\TransactionTrait;
use App\Http\Requests\PaymentRequest;
use App\Http\Traits\HasApiResponse;

class PaymentController extends Controller
{
    use PaymentTrait, TransactionTrait, HasApiResponse;

    public function purchaseProduct(PaymentRequest $request, Product $product)
    {
        $user = $request->user();

        if($product->quantity < $request->quantity)
        {
            return $this->badRequestResponse('Quqntity left is more than the quantity selected');
        }

        $data = [
            'event_type' => "product.payment",
            'product_id' => $product->id,
            'product_quantity' => $request->quantity,
        ];

        $transaction = $this->saveTransaction($product->amount * $request->quantity, $user, 'Payment', 'Product Purchase');

        $this->savePurchaseTransaction($user, $product, $transaction, $product->user, $request->quantity);

        return $this->paymentMethod($request->payment_method, $transaction, $user, $data);
    }
}
