<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserIndexRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(UserIndexRequest $request): View
    {
        $filters = $request->filters();

        return view('admin.users.index', [
            'users' => User::query()
                ->withCount(['orders' => fn (Builder $q) => $q->where('status', 'paid'), 'books'])
                ->when($filters['q'], function (Builder $query, string $term) {
                    $like = '%'.str_replace(['%', '_'], ' ', $term).'%';
                    $query->where(fn (Builder $q) => $q->whereLike('name', $like)->orWhereLike('email', $like));
                })
                ->when($filters['role'], fn (Builder $q, string $role) => $q->where('is_admin', $role === 'admin'))
                ->latest()
                ->latest('id')
                ->paginate(20)
                ->withQueryString(),
            'filters' => $filters,
        ]);
    }

    public function toggleAdmin(User $user): RedirectResponse
    {
        if (Gate::denies('toggleAdmin', $user)) {
            return back()->with('error', 'You cannot change your own admin role.');
        }

        $user->is_admin = ! $user->is_admin;
        $user->save();

        return back()->with('status', $user->is_admin
            ? "{$user->name} is now an administrator."
            : "{$user->name} is no longer an administrator.");
    }
}
