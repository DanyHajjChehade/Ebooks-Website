@extends('layouts.app')
@section('title', 'Reviews · Admin')
@section('content')
    <h1>Reviews</h1>
    <form method="GET" action="{{ route('admin.reviews.index') }}" role="search">
        <label>Search <input type="search" name="q" value="{{ $filters['q'] }}"></label>
        <label>Rating <select name="rating"><option value="">All</option>@for ($i = 5; $i >= 1; $i--)<option value="{{ $i }}" @selected($filters['rating'] === (string) $i)>{{ $i }}★</option>@endfor</select></label>
        <button type="submit">Filter</button>
    </form>
    <ul>
        @forelse ($reviews as $review)
            <li>{{ $review->rating }}★ on “{{ $review->book->title }}” by {{ $review->user->name }} ({{ $review->user->email }}): {{ $review->body }}
                <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" style="display:inline" onsubmit="return confirm('Delete this review?')">@csrf @method('DELETE')<button type="submit">Delete</button></form></li>
        @empty
            <li>No reviews.</li>
        @endforelse
    </ul>
    {{ $reviews->links() }}
@endsection
