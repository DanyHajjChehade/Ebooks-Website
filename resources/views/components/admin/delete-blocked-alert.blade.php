{{-- A2: a blocked author/category delete comes back as a validation error on the `delete` key. --}}
@props(['noun' => 'item'])
@error('delete')
    <x-alert variant="danger" :title="'This '.$noun.' can’t be deleted yet'">{{ $message }}</x-alert>
@enderror
