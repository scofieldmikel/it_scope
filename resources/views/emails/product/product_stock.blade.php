<x-mail::message>
# Dear {{ $product->user->name }}

This is to inform you that your product named {{ $product->name }} remands just {{ $product->quantity }}.

Kindly restock so as not to get out of stock.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
