@extends('layouts.app')
@section('title', 'Your cart')
@section('content')
    <h1>Your cart</h1>
    <ul>
        @forelse ($items as $book)
            <li>
                <a href="{{ route('books.show', $book) }}">{{ $book->title }}</a> by {{ $book->author->name }} —
                {{ $book->isFree() ? 'Free' : \App\Support\Money::format($book->effective_price_cents) }}
                <form method="POST" action="{{ route('cart.destroy', $book) }}" style="display:inline">@csrf @method('DELETE')
                    <button type="submit">Remove</button>
                </form>
            </li>
        @empty
            <li>Your cart is empty. <a href="{{ route('books.index') }}">Browse books</a></li>
        @endforelse
    </ul>
    <p>Subtotal: @money($subtotalCents)</p>
    @if ($items->isNotEmpty())
        <p>Digital content: your books are available to download immediately after payment. By checking out you agree to the <a href="{{ route('pages.terms') }}">terms</a> and acknowledge the <a href="{{ route('pages.refunds') }}">refund policy</a>.</p>
        @auth
            <form method="POST" action="{{ route('checkout.store') }}">@csrf
                <button type="submit">{{ $subtotalCents === 0 ? 'Get for free' : 'Checkout' }}</button>
            </form>
        @else
            <p><a href="{{ route('login') }}">Log in</a> or <a href="{{ route('register') }}">create an account</a> to check out.</p>
        @endauth
    @endif
@endsection
