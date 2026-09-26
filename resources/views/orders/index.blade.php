@extends('layouts.app')
@section('title', 'Your orders')
@section('content')
    <h1>Your orders</h1>
    <ul>
        @forelse ($orders as $order)
            <li><a href="{{ route('orders.show', $order) }}">{{ $order->reference }}</a> — {{ $order->created_at->toFormattedDateString() }} —
                {{ $order->status->label() }} — @money($order->subtotal_cents, $order->currency) — {{ $order->items->count() }} item(s)</li>
        @empty
            <li>No orders yet.</li>
        @endforelse
    </ul>
    {{ $orders->links() }}
@endsection
