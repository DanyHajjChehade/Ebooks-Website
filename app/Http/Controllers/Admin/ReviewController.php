<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewIndexRequest;
use App\Models\Review;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(ReviewIndexRequest $request): View
    {
        $filters = $request->filters();

        return view('admin.reviews.index', [
            'reviews' => Review::query()
                ->with(['user:id,name,email', 'book:id,title,slug,deleted_at'])
                ->when($filters['rating'], fn (Builder $q, string $rating) => $q->where('rating', (int) $rating))
                ->when($filters['q'], function (Builder $query, string $term) {
                    $like = '%'.str_replace(['%', '_'], ' ', $term).'%';
                    $query->where(fn (Builder $q) => $q
                        ->whereLike('body', $like)
                        ->orWhereHas('book', fn (Builder $b) => $b->whereLike('title', $like))
                        ->orWhereHas('user', fn (Builder $u) => $u->whereLike('name', $like)->orWhereLike('email', $like)));
                })
                ->latest()
                ->latest('id')
                ->paginate(20)
                ->withQueryString(),
            'filters' => $filters,
        ]);
    }

    public function destroy(Review $review): RedirectResponse
    {
        $this->authorize('delete', $review);

        $review->delete();

        return back()->with('status', 'Review deleted.');
    }
}
