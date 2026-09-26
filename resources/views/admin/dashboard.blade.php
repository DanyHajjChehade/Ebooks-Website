@extends('layouts.app')
@section('title', 'Admin')
@section('content')
    <h1>Dashboard</h1>
    <ul>
        <li>Revenue (30 days): @money($stats['revenue_cents_30d'])</li>
        <li>Paid orders (30 days): {{ $stats['paid_orders_30d'] }}</li>
        <li>Customers: {{ $stats['customers'] }}</li>
        <li>Published books: {{ $stats['published_books'] }}</li>
    </ul>
    <h2>Recent orders</h2>
    <ul>@foreach ($recentOrders as $order)<li><a href="{{ route('admin.orders.show', $order) }}">{{ $order->reference }}</a> {{ $order->user?->email ?? 'deleted user' }} {{ $order->status->label() }} @money($order->subtotal_cents, $order->currency) ({{ $order->items_count }} items)</li>@endforeach</ul>
    <h2>Top books</h2>
    <ul>@foreach ($topBooks as $book)<li>{{ $book->title }} — {{ $book->sales_count }} sold — @money((int) $book->revenue_cents)</li>@endforeach</ul>
@endsection
