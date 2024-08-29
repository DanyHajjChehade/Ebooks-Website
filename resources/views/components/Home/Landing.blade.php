<div class="landing">
    <div class="container">
        <div class="text">
            <h1>Welcome, To {{ $setting->name }}</h1>
            <p>{{ $setting->description }}</p>
        </div>
        <div class="image">
            <img src="{{ asset('imgs/landing-image.png') }}" alt="" />
        </div>
    </div>
    <a href="#books" class="go-down"><i class="fas fa-angle-double-down fa-2x"></i></a>
</div>
