@extends('layouts.app')
@section('title', 'Orders · Admin')
@section('content')
    <h1>Orders</h1>
    <form method="GET" action="{{ route('admin.orders.index') }}" role="search">
        <label>Search (email, name, order id) <input type="search" name="q" value="{{ $filters['q'] }}"></label>
        <label>Status <select name="status"><option value="">All</option>
            @foreach (\App\Enums\OrderStatus::cases() as $status)<option value="{{ $status->value }}" @selected($filters['status'] === $status->value)>{{ $status->label() }}</option>@endforeach
        </select></label>
        <button type="submit">Filter</button>
    </form>
    <ul>
        @forelse ($orders as $order)
            <li><a href="{{ route('admin.orders.show', $order) }}">{{ $order->reference }}</a> — {{ $order->user?->email ?? 'deleted user' }} —
                {{ $order->status->label() }} — @money($order->subtotal_cents, $order->currency) — {{ $order->items_count }} item(s) — {{ $order->created_at->toDayDateTimeString() }}</li>
        @empty
            <li>No orders.</li>
        @endforelse
    </ul>
    {{ $orders->links() }}
@endsection
