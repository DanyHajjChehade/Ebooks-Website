@extends('layouts.app')
@section('title', 'Thank you')
@section('content')
    @if ($order->isPaid())
        <h1>Thank you — your books are ready</h1>
    @else
        <h1>Payment processing</h1>
        <p>Status: {{ $order->status->label() }}. We will add your books to your library as soon as the payment is confirmed.</p>
    @endif
    <p>Order {{ $order->reference }} · @money($order->subtotal_cents, $order->currency)</p>
    <ul>
        @foreach ($order->items as $item)
            <li>{{ $item->title }} — @money($item->price_cents, $order->currency)
                @if ($order->isPaid() && $item->book) <a href="{{ route('library.download', $item->book) }}">Download</a>@endif</li>
        @endforeach
    </ul>
    <p><a href="{{ route('library.index') }}">Go to your library</a></p>
@endsection
