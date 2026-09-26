<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;

class BookPolicy
{
    /**
     * Storefront visibility: published books for everyone, drafts for admins (preview).
     */
    public function view(?User $user, Book $book): bool
    {
        return $book->isPubliclyVisible() || (bool) $user?->isAdmin();
    }

    /**
     * Only customers who own the book (or admins) may download the file.
     */
    public function download(User $user, Book $book): bool
    {
        return $user->isAdmin() || $user->ownsBook($book);
    }
}
