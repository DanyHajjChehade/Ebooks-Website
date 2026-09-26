@extends('layouts.app')
@section('title', 'Books · Admin')
@section('content')
    <h1>Books</h1>
    <p><a href="{{ route('admin.books.create') }}">New book</a></p>
    <form method="GET" action="{{ route('admin.books.index') }}" role="search">
        <label>Search <input type="search" name="q" value="{{ $filters['q'] }}"></label>
        <label>Status <select name="status"><option value="">All</option>
            @foreach (\App\Http\Requests\Admin\BookIndexRequest::STATUSES as $status)<option value="{{ $status }}" @selected($filters['status'] === $status)>{{ ucfirst($status) }}</option>@endforeach
        </select></label>
        <button type="submit">Filter</button>
    </form>
    <table>
        <thead><tr><th>Title</th><th>Author</th><th>Category</th><th>Price</th><th>Status</th><th>Sales</th><th>Actions</th></tr></thead>
        <tbody>
        @forelse ($books as $book)
            <tr>
                <td>{{ $book->title }}</td><td>{{ $book->author->name }}</td><td>{{ $book->category->name }}</td>
                <td>@money($book->effective_price_cents)</td>
                <td>{{ $book->is_published ? 'Published' : 'Draft' }}{{ $book->is_featured ? ' · Featured' : '' }}</td>
                <td>{{ $book->sales_count }}</td>
                <td>
                    <a href="{{ route('books.show', $book) }}">View</a>
                    <a href="{{ route('admin.books.edit', $book) }}">Edit</a>
                    <form method="POST" action="{{ route('admin.books.destroy', $book) }}" style="display:inline" onsubmit="return confirm('Remove this book from the store?')">@csrf @method('DELETE')<button type="submit">Delete</button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="7">No books.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $books->links() }}
@endsection
