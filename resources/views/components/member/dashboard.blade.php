<x-member.Layout>
    <x-slot name="sidebar">
        <x-member.Sidebar/>
    </x-slot>
    <x-slot name="header">
        <x-member.Header/>
    </x-slot>
    <x-slot name="profile">
        <x-member.profile :user="$user"/>
    </x-slot>
    <x-slot name="MyBook">
    </x-slot>
    <x-slot name="Review">
    </x-slot>
    <x-slot name="ManageBooks">
    </x-slot>
</x-member.Layout>
