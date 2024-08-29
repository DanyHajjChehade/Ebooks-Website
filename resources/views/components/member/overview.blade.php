<div class="overview bg-white rad-10 d-flex align-center">
    <div class="avatar-box txt-c p-20">
        <img class="rounded-photo"
        src="{{ $user->image ? asset($user->image) : asset('profile/anonymous.png') }}"
        alt="Profile Image">

        <h3 class="m-0">{{ $user->username }}</h3>
    </div>
    <div class="info-box w-full txt-c-mobile">
        <!-- Start Information Row -->
        <div class="box p-20 d-flex align-center">
            <h4 class="c-grey fs-15 m-0 w-full">General Information</h4>
            <div class="fs-14">
                <span class="c-grey">Full Name</span>
                <span>{{ $user->first_name }} {{ $user->last_name }}</span>
            </div>
            <div class="fs-14">
                <span class="c-grey">Address:</span>
                <span>{{ $user->address }}</span>
            </div>
        </div>
        <!-- End Information Row -->
        <!-- Start Information Row -->
        <div class="box p-20 d-flex align-center">
            <h4 class="c-grey w-full fs-15 m-0">Personal Information</h4>
            <div class="fs-14">
                <span class="c-grey">Email:</span>
                <span>{{ $user->email }}</span>
            </div>
            <div class="fs-14">
                <span class="c-grey">Status:</span>
                <span>@if ($user->usertype==1)
                    Admin
                    @else
                    User
                @endif</span>
            </div>
        </div>
        <!-- End Information Row -->
        <div class="box p-20 d-flex align-center">
            <h4 class="c-grey w-full fs-15 m-0">Request to become an admin</h4>
            <div class="fs-14">
                Confident that you skills and experience align well with the responsibilities and goals of the role? request to become an admin.
                <br><br>
                <form method="POST" action="{{ route('user.request', $user->id) }}">
                    @csrf
                    <button type="submit" class="d-block fs-14 bg-blue c-white w-fit btn-shape ">Make A Request</button>
                </form>
            </div>
        </div>
    </div>
</div>
