<x-member.Layout>
    <x-slot name="sidebar">
        <x-member.Sidebar></x-member.Sidebar>
    </x-slot>
    <x-slot name="header">
        <x-member.Header></x-member.Header>
    </x-slot>
    <x-slot name="profile">
    </x-slot>
    <x-slot name="MyBook">
    </x-slot>
    <x-slot name="Review">
    </x-slot>
    <x-slot name="ManageBooks">
        <x-member.ManageBookSection :orders="$orders"/>
    </x-slot>
</x-member.Layout>

