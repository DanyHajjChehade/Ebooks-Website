{{-- Admin users (DESIGN.md §5.4): toggle admin with a confirm; never on your own row. --}}
@php
    $role = $filters['role'] ?? null;
    $q = $filters['q'] ?? null;
    $tab = fn (?string $r) => route('admin.users.index', array_filter(['role' => $r, 'q' => $q]));
    $me = auth()->id();
@endphp
<x-layouts.admin title="Users">
    <x-admin.page-header title="Users" :breadcrumbs="[['Admin', route('admin.dashboard')], ['Users']]"/>
    <x-admin.status-tabs label="Role" :tabs="[
        ['All', $tab(null), $role === null],
        ['Admins', $tab('admin'), $role === 'admin'],
        ['Customers', $tab('customer'), $role === 'customer'],
    ]"/>
    <x-admin.filters :action="route('admin.users.index')" label="Search users" placeholder="Name or email" :q="$q" :active="$q || $role">
        @if ($role)<input type="hidden" name="role" value="{{ $role }}">@endif
    </x-admin.filters>

    @if ($users->isEmpty())
        <x-empty-state heading="No users match" icon="users">Try a different name or email.</x-empty-state>
    @else
        <div class="table-wrap">
            <table class="table">
                <caption class="sr-only">Users</caption>
                <thead><tr><th scope="col">User</th><th scope="col">Joined</th><th scope="col" class="num">Orders</th><th scope="col" class="num">Books</th><th scope="col">Role</th><th scope="col" class="actions"><span class="sr-only">Actions</span></th></tr></thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <th class="min-w-64" scope="row">
                                <span class="flex items-center gap-3">
                                    <x-avatar :name="$user->name" :id="$user->id" size="sm"/>
                                    <span class="grid min-w-0">
                                        <span class="flex flex-wrap items-center gap-2 font-semibold">{{ $user->name }}@if ($user->id === $me)<x-badge variant="outline">You</x-badge>@endif</span>
                                        <span class="truncate font-normal text-muted">{{ $user->email }}</span>
                                    </span>
                                </span>
                            </th>
                            <td class="whitespace-nowrap"><time datetime="{{ $user->created_at->toIso8601String() }}">{{ $user->created_at->format('M j, Y') }}</time></td>
                            <td class="num">{{ $user->orders_count }}</td>
                            <td class="num">{{ $user->books_count }}</td>
                            <td>@if ($user->is_admin)<x-badge variant="accent">Admin</x-badge>@else<span class="text-muted">Customer</span>@endif</td>
                            <td class="actions">
                                @if ($user->id === $me)
                                    <span class="inline-block max-w-40 whitespace-normal text-left text-xs text-muted">You can’t change your own role.</span>
                                @else
                                    <form method="POST" action="{{ route('admin.users.toggle-admin', $user) }}">
                                        @csrf
                                        @method('PATCH')
                                        @if ($user->is_admin)
                                            <button class="btn btn-danger-quiet btn-sm" type="submit" data-confirm="Remove {{ $user->name }}’s admin access?" data-confirm-body="They keep their account, library and orders, but can’t open the admin any more." data-confirm-ok="Remove admin" data-confirm-cancel="Keep as admin" data-confirm-busy="Saving…">Remove admin</button>
                                        @else
                                            <button class="btn btn-secondary btn-sm" type="submit" data-confirm="Make {{ $user->name }} an admin?" data-confirm-body="Admins can edit the catalogue, see every order and issue refunds." data-confirm-ok="Make admin" data-confirm-cancel="Keep as customer" data-confirm-variant="primary" data-confirm-busy="Saving…">Make admin</button>
                                        @endif
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <x-pagination :paginator="$users"/>
    @endif
</x-layouts.admin>
