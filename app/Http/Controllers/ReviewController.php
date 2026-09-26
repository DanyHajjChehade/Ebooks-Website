<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewRequest;
use App\Models\Book;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(ReviewRequest $request, Book $book): RedirectResponse
    {
        $this->authorize('create', [Review::class, $book]);

        $review = new Review($request->validated());
        $review->user()->associate($request->user());
        $review->book()->associate($book);
        $review->save();

        return redirect()->to(route('books.show', $book).'#reviews')
            ->with('status', 'Thanks — your review is live.');
    }

    public function update(ReviewRequest $request, Review $review): RedirectResponse
    {
        $this->authorize('update', $review);

        $review->update($request->validated());

        return back()->with('status', 'Your review has been updated.');
    }

    public function destroy(Request $request, Review $review): RedirectResponse
    {
        $this->authorize('delete', $review);

        $review->delete();

        return back()->with('status', 'Review deleted.');
    }
}
