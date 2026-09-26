<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReviewPolicy
{
    /**
     * Only owners of the book may review it, once.
     */
    public function create(User $user, Book $book): Response
    {
        if (! $user->ownsBook($book)) {
            return Response::deny('Only readers who bought this book can review it.');
        }

        if ($user->reviews()->where('book_id', $book->getKey())->exists()) {
            return Response::deny('You’ve already reviewed this book.');
        }

        return Response::allow();
    }

    public function update(User $user, Review $review): bool
    {
        return $review->user_id === $user->getKey();
    }

    public function delete(User $user, Review $review): bool
    {
        return $review->user_id === $user->getKey() || $user->isAdmin();
    }
}
