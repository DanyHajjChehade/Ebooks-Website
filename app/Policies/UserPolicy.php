<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Admins may promote/demote other users, never themselves (avoids
     * accidentally locking the last admin out).
     */
    public function toggleAdmin(User $actor, User $target): bool
    {
        return $actor->isAdmin() && ! $actor->is($target);
    }
}
