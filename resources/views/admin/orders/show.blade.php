@extends('layouts.app')
@section('title', 'Order '.$order->reference.' · Admin')
@section('content')
    <h1>Order {{ $order->reference }}</h1>
    <p>{{ $order->status->label() }} · {{ $order->user?->name ?? 'Deleted user' }} {{ $order->user?->email }}</p>
    <p>Placed {{ $order->created_at->toDayDateTimeString() }}@if ($order->paid_at) · paid {{ $order->paid_at->toDayDateTimeString() }}@endif</p>
    <p>Stripe session: {{ $order->stripe_checkout_session_id ?? '—' }} · payment intent: {{ $order->stripe_payment_intent_id ?? '—' }}</p>
    <ul>@foreach ($order->items as $item)<li>{{ $item->title }} — @money($item->price_cents, $order->currency)</li>@endforeach</ul>
    <p>Total: @money($order->subtotal_cents, $order->currency)</p>
    @if ($order->isPaid())
        <form method="POST" action="{{ route('admin.orders.refund', $order) }}" onsubmit="return confirm('Refund this order in full and remove its books from the customer\'s library?')">@csrf
            <button type="submit">Refund</button>
        </form>
    @endif
@endsection
