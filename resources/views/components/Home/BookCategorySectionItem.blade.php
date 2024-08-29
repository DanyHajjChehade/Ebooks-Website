<div class="col-md-4 mb-4">
    <div class="card h-100">
        <img src="{{ asset($book->book_image) }}" class="card-img-top" alt="">
        <div class="card-body" style="position: relative;">
            <h4 class="card-title">{{ $book->title }}</h4>
            <p class="card-text" style="overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 4; /* number of lines to show */
            -webkit-box-orient: vertical;">{{ $book->description }}</p>
            <ul>
                <li class="card-text"><b>Category:</b> {{ $book->category->name }}</li>
                <li class="card-text"><b>Author:</b> {{ $book->author->first_name }} {{ $book->author->last_name }}</li>
                <li class="card-text"><b>Price:</b>
                    @if ($book->discount_price)
                        <span style="text-decoration: line-through; color: red;">{{ $book->price }}$</span>
                    @else
                        <span style="color: blue;">{{ $book->price }}$</span>
                    @endif
                </li>
                @if ($book->discount_price)
                    <li class="card-text"><b>Discount Price:</b> <span style="color: blue;">{{ $book->discount_price }}$</span></li>
                @endif
                @if($book->copies_owned==0)
                    <li style="color:red;"><b>Out of Stock</b></li>
                @else
                    <li class="card-text"><b>Owned Copies:</b> {{ $book->copies_owned }}</li>
                @endif
            </ul>

            @if (session('book-success-' . $book->id))
                <div style="color:green;"><b>{{ session('book-success-' . $book->id) }}</b></div>
                @php
                    session()->forget('book-success-' . $book->id);
                @endphp
            @endif

            @if ($cart->where('id',$book->id)->count())
                <div style="color:#2196f3;"><b>In Cart</b></div>
            @else
                <form method="POST" action="{{ route('cart.store') }}">
                    @csrf
                    <input type="hidden" name="book_id" value="{{ $book->id }}">
                    <input type="number" min="1" max="{{ $book->copies_owned }}" value="1" name="quantity" class="form-control form-control-sm mt-2">
                    <button type="submit" class="btn btn-primary mt-2">Add to Cart</button>
                </form>
            @endif
        </div>
    </div>
</div>
