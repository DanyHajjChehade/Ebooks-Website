<!-- {{-- @if ($userType === 'member') --}} -->
<li>
    <a class="active d-flex align-center fs-14 c-black rad-6 p-10" @if(Request::is('profile/*')) disactive @endif" href="/profile/{{ auth()->user()->username }}">
        <i class="fa-solid fa-user fa-fw"></i>
        <span>Profile</span>
    </a>
</li>
<li>
    <a class="d-flex align-center fs-14 c-black rad-6 p-10" @if(Request::is('orders/*')) active @endif" href="{{ route('user.orders',['user'=>auth()->user()]) }}">
        <i class="fa-solid fa-cart-shopping fa-fw"></i>
        <span>Orders</span>
    </a>
</li>
<li>
    <a class="d-flex align-center fs-14 c-black rad-6 p-10"  href="{{ route('index') }}">
        <i class="fa-solid fa-home fa-fw"></i>
        <span>Home Page</span>
    </a>
</li>



