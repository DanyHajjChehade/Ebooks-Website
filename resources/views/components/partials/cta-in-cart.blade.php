{{-- In-cart CTA state (rendered by the server, and cloned by cart.js after a fetch add). --}}
@if ($bar)
    <a class="btn btn-primary" href="{{ route('cart.index') }}"><x-icon name="lock"/>Check out</a>
@else
    <span class="badge badge-accent justify-self-start"><x-icon name="shopping-bag"/>In your cart</span>
    <div class="flex flex-wrap items-center gap-3">
        <a class="btn btn-primary btn-lg min-w-64 max-sm:w-full" href="{{ route('cart.index') }}"><x-icon name="lock"/>Check out</a>
        <a class="btn btn-ghost btn-lg max-sm:w-full" href="{{ route('books.index') }}">Keep browsing</a>
    </div>
@endif
