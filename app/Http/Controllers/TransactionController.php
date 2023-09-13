<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Auth\Transaction;
use App\Http\Resources\TransactionResource;
use App\Http\Resources\PurchaseHistoryResource;

class TransactionController extends Controller
{
    public function myTransactions()
    {
        $user = auth()->user();

        $transaction = $user->transactions()->latest()->paginate(10);
        return TransactionResource::collection($transaction);
    }

    public function singleTransaction(Transaction $transaction)
    {
        $this->authorize('viewTransaction', $transaction);
         
        return new TransactionResource($transaction);
    }

    public function userPurchasehistory()
    {
        $user = auth()->user();

        $userPurchasehistory = $user->purchase_transactions;

        return PurchaseHistoryResource::collection($userPurchasehistory) ;
    }
}
