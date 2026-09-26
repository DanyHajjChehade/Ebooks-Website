{{-- Admin reviews (DESIGN.md §5.4): search, rating filter, delete with confirm. --}}
@php
    $q = $filters['q'] ?? null;
    $rating = $filters['rating'] ?? null;
@endphp
<x-layouts.admin title="Reviews">
    <x-admin.page-header title="Reviews" :breadcrumbs="[['Admin', route('admin.dashboard')], ['Reviews']]"/>
    <x-admin.filters :action="route('admin.reviews.index')" label="Search reviews" placeholder="Book or reviewer" :q="$q" :active="$q || $rating">
        <x-select class="w-44" name="rating" label="Rating" :hide-label="true" :options="['5' => '5 stars', '4' => '4 stars', '3' => '3 stars', '2' => '2 stars', '1' => '1 star']" :selected="$rating" placeholder="All ratings"/>
    </x-admin.filters>

    @if ($reviews->isEmpty())
        <x-empty-state :heading="$q || $rating ? 'No reviews match' : 'No reviews yet'" icon="message-square-quote">
            {{ $q || $rating ? 'Try a different search or rating.' : 'Reviews from readers who bought a book appear here.' }}
        </x-empty-state>
    @else
        <div class="table-wrap">
            <table class="table">
                <caption class="sr-only">Reviews</caption>
                <thead><tr><th scope="col">Review</th><th scope="col">Book</th><th scope="col">Reviewer</th><th scope="col">Date</th><th scope="col" class="actions"><span class="sr-only">Actions</span></th></tr></thead>
                <tbody>
                    @foreach ($reviews as $review)
                        <tr>
                            <th scope="row" class="min-w-64 font-normal">
                                <span class="grid gap-1">
                                    <x-stars :value="$review->rating"/>
                                    <span class="line-clamp-2 max-w-md font-normal">{{ $review->body }}</span>
                                </span>
                            </th>
                            <td>
                                @if ($review->book && ! $review->book->trashed())
                                    <a class="link" href="{{ route('books.show', $review->book) }}">{{ $review->book->title }}</a>
                                @else
                                    <span class="text-muted">{{ $review->book?->title ?? 'Deleted book' }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="block font-semibold">{{ $review->user?->name }}</span>
                                <span class="block text-muted">{{ $review->user?->email }}</span>
                            </td>
                            <td class="whitespace-nowrap"><time datetime="{{ $review->created_at->toIso8601String() }}">{{ $review->created_at->format('M j, Y') }}</time></td>
                            <td class="actions">
                                <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger-quiet btn-sm btn-icon" type="submit" aria-label="Delete review by {{ $review->user?->name }}"
                                        data-confirm="Delete this review?" data-confirm-body="It disappears from the book page. The reviewer isn’t notified."
                                        data-confirm-ok="Delete review" data-confirm-cancel="Keep review" data-confirm-busy="Deleting…"><x-icon name="trash-2"/></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <x-pagination :paginator="$reviews"/>
    @endif
</x-layouts.admin>
