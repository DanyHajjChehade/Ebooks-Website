<div class="head bg-white p-15 d-flex">
    <div class="icons d-flex align-center">
        <span class="notification p-relative">
            <img class="rounded-photo" src="{{ auth()->user()->image ? asset(auth()->user()->image) : asset('profile/anonymous.png') }}" alt="User Avatar">
        </span>
    </div>
</div>
