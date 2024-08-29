<body id="page-top">
    <div class="page d-flex">
        <x-slot name="sidebar">
            <x-member.Sidebar></x-member.Sidebar>
        </x-slot>
        <div class="content w-full">
            <x-slot name="header">
                <x-member.Header></x-member.Header>
            </x-slot>

        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
