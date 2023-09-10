<?php

namespace App\Http\Controllers\Webhooks\Traits;

use Exception;
use App\Models\Product;
use App\Traits\Helpers;
use App\Models\Auth\Transaction;
use Illuminate\Support\Facades\DB;
use App\Http\Traits\HasApiResponse;
use App\Models\PurchaseTransaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Notifiable;
use App\Notifications\UserPurchaseNotify;
use App\Notifications\BusinessPurchaseNotify;

trait PaymentWebhookTrait
{
    use Helpers, HasApiResponse, Notifiable;
    protected function updateProduct(Product $product, Transaction $transaction, $request)
    {

        DB::beginTransaction();
        try {
        //Subtract the quantity bought from the remaining quantity
            $product->quantity -= $request['metadata']['product_quantity'];
            $product->save();

            //Update transaction table with the paystack data
            $transaction->data = $request;
            $transaction->save();

            //Update purchase transaction table to success
            $purchae = PurchaseTransaction::where('transaction_id', $transaction->id)->first()
            ->update([
                'status' => Transaction::SUCCESSFUL,
            ]);

            Log::info($transaction->user);
            
            //Send Mail and notification  
            $transaction->user->notify(new UserPurchaseNotify($transaction, $product));
            $product->user->notify(new BusinessPurchaseNotify($transaction, $product));

            DB::commit();
            return  $this->okResponse('Product Payment Successfully');


        } catch (Exception $e) {
            Log::info($e);
            DB::rollback();
        }
    }

 }