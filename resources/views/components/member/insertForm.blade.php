<div class="p-20 bg-white rad-10 mt-25">
    <h2 class="mt-0 mb-10">General Info</h2>
    <p class="mt-0 mb-20 c-grey fs-15">General Information About Your Account</p>
    <form action="{{ route('user.profile.update', $user->id) }}" method="post" enctype="multipart/form-data">
        @csrf
        @method('put')
        @if($errors->any())
        {!! implode('', $errors->all('<div>:message</div>')) !!}
    @endif
    <div class="mb-15">
      <label class="fs-14 c-grey d-block mb-10" for="first">First Name</label>
      <input class="b-none border-ccc p-10 rad-6 d-block w-full" type="text" id="first_name" name="first_name" value="{{$user->first_name}}">
    </div>
    <div class="mb-15">
      <label class="fs-14 c-grey d-block mb-5" for="last">Last Name</label>
      <input class="b-none border-ccc p-10 rad-6 d-block w-full" id="last_name" name="last_name" type="text" value="{{$user->last_name}}">
    </div>
    <div class="mb-15">
        <label class="fs-14 c-grey d-block mb-5" for="username">Username</label>
        <input class="b-none border-ccc p-10 rad-6 d-block w-full" id="username" name="username" type="text" value="{{$user->username}}">
    </div>
    <div class="mb-15">
        <label class="fs-14 c-grey d-block mb-5" for="address">Address</label>
        <input class="b-none border-ccc p-10 rad-6 d-block w-full" id="address" name="address" type="text" value="{{$user->address}}">
    </div>
    <div class="mb-15">
        <label class="fs-14 c-grey d-block mb-5" for="image">Profile Photo</label>
        <input class="form-control dropify" id="validationCustom05" type="file" name="image"
        data-default-file="{{ asset($user->image) }}">
    </div>
    </div>
    <button type="submit" class="d-block fs-14 bg-blue c-white w-fit btn-shape ">Change</button>
</div>
