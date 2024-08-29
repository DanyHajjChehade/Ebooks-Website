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
            <h1 class="p-relative">Add Book:</h1>
        <div class="p-20 bg-white rad-10 mt-25">
            <h2 class="mt-0 mb-10">Add Book</h2>
            <p class="mt-0 mb-20 c-grey fs-15">General Information on your book</p>
            <div class="mb-15">
                <label class="fs-14 c-grey d-block mb-10" for="first">photo</label>
                <input type="file" src="" alt="choose photo">
            </div>
            <div class="mb-15">
              <label class="fs-14 c-grey d-block mb-10" for="first">Title name</label>
              <input class="b-none border-ccc p-10 rad-6 d-block w-full" type="text" id="first" placeholder="Title name">
            </div>
            <div class="mb-15">
              <label class="fs-14 c-grey d-block mb-5" for="last">Description</label>
              <input class="b-none border-ccc p-10 rad-6 d-block w-full" id="last" type="text" placeholder="Description">
            </div>
            <div class="mb-15">
                <label class="fs-14 c-grey d-block mb-5" for="last">publication date</label>
                <input class="b-none border-ccc p-10 rad-6 d-block w-full" id="last" type="text" placeholder="publication date">
            </div>
            <a href="#" class="d-block fs-14 bg-blue c-white w-fit btn-shape ">Change</a>
        </div>

    </x-slot>
</x-member.Layout>
