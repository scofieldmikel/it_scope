<x-mail::message>
# Hello {{ $transaction->user->name }}

This is to inform you that your product purchase is successful.

<h2>Details:</h2>

<b>Product:</b> {{ $product->name }}

<b>Amount:</b> &#8358;{{ number_format($transaction->amount, 2) }}

<b>Reference Number</b> {{ $transaction->reference }}

<b>Date</b> {{ $transaction->created_at->format('l jS F, Y h:i:sa') }}


Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
