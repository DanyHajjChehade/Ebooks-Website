@extends('layouts.app')
@section('title', $book->title)
@section('description', \Illuminate\Support\Str::limit($book->description, 155))
@section('content')
    <h1>{{ $book->title }}</h1>
    <p>by <a href="{{ route('authors.show', $book->author) }}">{{ $book->author->name }}</a>
        in <a href="{{ route('categories.show', $book->category) }}">{{ $book->category->name }}</a></p>
    <p>{{ strtoupper($book->file_format) }} · {{ $book->page_count }} pages · ISBN {{ $book->isbn }} ·
        {{ $book->reviews_count }} reviews ({{ number_format((float) $book->reviews_avg_rating, 1) }}★)</p>
    <p>@if ($book->isOnSale()) <s>@money($book->price_cents)</s> @endif {{ $book->isFree() ? 'Free' : \App\Support\Money::format($book->effective_price_cents) }}</p>
    <p>{{ $book->description }}</p>

    @if ($owned)
        <p>In your library. <a href="{{ route('library.download', $book) }}">Download</a></p>
    @elseif ($inCart)
        <p>In your cart. <a href="{{ route('cart.index') }}">View cart</a></p>
    @else
        <form method="POST" action="{{ route('cart.store') }}">@csrf
            <input type="hidden" name="book_id" value="{{ $book->id }}">
            <button type="submit">Add to cart</button>
        </form>
    @endif

    <h2 id="reviews">Reviews</h2>
    @if ($canReview)
        <form method="POST" action="{{ route('reviews.store', $book) }}">@csrf
            <label>Rating <select name="rating">@for ($i = 5; $i >= 1; $i--)<option value="{{ $i }}" @selected(old('rating') == $i)>{{ $i }}</option>@endfor</select></label>
            <label>Review <textarea name="body" maxlength="2000">{{ old('body') }}</textarea></label>
            @error('rating')<p>{{ $message }}</p>@enderror @error('body')<p>{{ $message }}</p>@enderror
            <button type="submit">Post review</button>
        </form>
    @endif
    @if ($userReview)
        <form method="POST" action="{{ route('reviews.update', $userReview) }}">@csrf @method('PATCH')
            <label>Your rating <input type="number" name="rating" min="1" max="5" value="{{ old('rating', $userReview->rating) }}"></label>
            <label>Your review <textarea name="body" maxlength="2000">{{ old('body', $userReview->body) }}</textarea></label>
            <button type="submit">Update review</button>
        </form>
        <form method="POST" action="{{ route('reviews.destroy', $userReview) }}" onsubmit="return confirm('Delete your review?')">@csrf @method('DELETE')
            <button type="submit">Delete review</button>
        </form>
    @endif
    <ul>
        @forelse ($reviews as $review)
            <li>{{ $review->rating }}★ {{ $review->user->name }} ({{ $review->created_at->toFormattedDateString() }}): {{ $review->body }}</li>
        @empty
            <li>No reviews yet.</li>
        @endforelse
    </ul>
    {{ $reviews->links() }}

    <h2>Related</h2>
    @include('partials.book-list', ['list' => $related])
@endsection
