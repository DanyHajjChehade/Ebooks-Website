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
        <div class="profile-page m-20">
            <h1 class="p-relative">Update:</h1>
            <div class="p-20 bg-white rad-10 mt-25">
                <h2 class="mt-0 mb-10">Update information</h2>
                <p class="mt-0 mb-20 c-grey fs-15">General Information About Your Book</p>
                <div class="mb-15">
                    <label class="fs-14 c-grey d-block mb-10" for="first">photo</label>
                    <input type="file" src="" alt="choose photo">
                </div>
                <div class="mb-15">
                <label class="fs-14 c-grey d-block mb-10" for="first">BookName</label>
                <input class="b-none border-ccc p-10 rad-6 d-block w-full" type="text" id="first" placeholder="BookName">
                </div>
                <div class="mb-15">
                <label class="fs-14 c-grey d-block mb-5" for="last">BookDescription</label>
                <input class="b-none border-ccc p-10 rad-6 d-block w-full" id="last" type="text" placeholder="BookDescription">
                </div>
                <div class="mb-15">
                    <label class="fs-14 c-grey d-block mb-5" for="last">Publish date</label>
                    <input class="b-none border-ccc p-10 rad-6 d-block w-full" id="last" type="text" placeholder="Publish date">
                </div>
                <a href="#" class="d-block fs-14 bg-blue c-white w-fit btn-shape ">update</a>
            </div>
        </div>
    </x-slot>
</x-member.Layout>
