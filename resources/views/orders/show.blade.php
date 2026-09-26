@extends('layouts.app')
@section('title', 'Order '.$order->reference)
@section('content')
    <h1>Order {{ $order->reference }}</h1>
    <p>{{ $order->status->label() }} · placed {{ $order->created_at->toDayDateTimeString() }}@if ($order->paid_at) · paid {{ $order->paid_at->toDayDateTimeString() }}@endif</p>
    <ul>
        @foreach ($order->items as $item)
            <li>{{ $item->title }}@if ($item->book) by {{ $item->book->author->name }}@endif — @money($item->price_cents, $order->currency)
                @if ($order->isPaid() && $item->book) <a href="{{ route('library.download', $item->book) }}">Download</a>@endif</li>
        @endforeach
    </ul>
    <p>Total: @money($order->subtotal_cents, $order->currency)</p>
@endsection
