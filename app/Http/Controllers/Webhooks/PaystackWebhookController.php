<?php

namespace App\Http\Controllers\Webhooks;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Auth\Transaction;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Webhooks\Traits\PaymentWebhookTrait;
use App\Models\Product;

class PaystackWebhookController extends Controller
{
    use PaymentWebhookTrait;

    public function handle(Request $request)
    {
        Log::info(print_r($request->all(), true));
        $method = Str::replace('.', '', ucwords($request->event, '.'));

        if (method_exists($this, $handler = 'handle'.$method)) {
            $this->{$handler}($request->data);
        }
        //handleChargeSuccess
    }

     /**
     * @throws Exception
     */
    protected function checkTransaction($request): \Illuminate\Database\Eloquent\Builder|Transaction|null
    {
        $amount = $request['requested_amount'] ?? $request['amount'];

        $transaction = Transaction::where('reference', $request['reference'])
            ->where('amount', $amount)
            ->where('status', Transaction::PENDING)
            ->first();

        Log::info($transaction);
        if (! $this->checkTransactionValid($request, $transaction)) {
            Log::info('Invalid Transaction');
            Log::info(print_r($request, true));
            throw new Exception('Invalid Transaction');
        }

        $this->createTransactionForPartial($request, $transaction);

        $transaction->status = true;
        $transaction->save();

        return $transaction;
    }

    protected function createTransactionForPartial($request, Transaction $transaction)
    {
        if (isset($request['requested_amount']) && $request['requested_amount'] > $request['amount']) {
            $transaction->amount = ($request['amount'] / 100 );
            $transaction->save();
        }
    }

    protected function checkTransactionValid($request, $transaction): bool
    {
        return $request['status'] === 'success' && ! is_null($transaction);
    }

    /**
     * @throws Exception
     * @throws \Throwable
     */
    protected function handleChargeSuccess($request)
    {

        $transaction = $this->checkTransaction($request);

        $method = Str::replace('.', '', ucwords($request['metadata']['event_type'], '.'));

        if (method_exists($this, $handler = 'handled'.$method)) {
            $this->{$handler}($request, $transaction);
        }
    }

    protected function handledProductPayment($request, Transaction $transaction)
    {
        $product = Product::findOrFail($request['metadata']['product_id']);

        $this->updateProduct($product, $transaction, $request);
    }

    protected function handleTransferSuccess($request)
    {
        $transaction = $this->checkTransaction($request);

        if (Str::contains($transaction->reference, 'wdw_')) {
            $this->processWithdrawal($request, $transaction);

            return;
        }

        $this->processWithdraw($transaction, $request);
    }

}
