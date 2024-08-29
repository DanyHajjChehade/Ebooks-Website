
<div class="container mt-5 mb-5">
    @if(Cart::count())
    <h2 class="mb-4 text-primary">Shopping Cart</h2>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="table-primary">
                <tr>
                    <th class="text-primary">Books</th>
                    <th class="text-primary">Quantity</th>
                    <th class="text-primary">Price</th>
                    <th class="text-primary">Total</th>
                    <th class="text-primary">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cart as $cartitem)
                <x-Home.CartItemSection :cartitem="$cartitem"/>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-end">
        <h4>Total: ${{ Cart::subtotal() }}</h4>
    </div>
    <div class="d-flex justify-content-end mt-3">
        <a href="{{ url()->previous() }}" class="btn btn-danger btn-lg shadow-sm">Back</a>
        &nbsp;&nbsp;
        <a href="{{ route('payment.index') }}" class="btn btn-primary btn-lg shadow-sm">
            <i class="fa-solid fa-credit-card me-2"></i> Proceed to Checkout
        </a>
    </div>
@else
    <!-- hay bdna n3mela 3l if iza kenet lcart fadye btbayen -->
    <section id="bootstrap__cart_view">
        <div class="container">
            <div class="row justify-content-center align-items-center" style="height: 100vh;">
                <div class="col-md-6 text-center">
                    <div class="card p-4 shadow">
                        <h2 class="mb-4">Your Shopping Cart is Empty</h2>
                        <p class="lead mb-4">Explore our Books and find something you love.</p>
                        <a href="{{ route('index') }}" class="btn btn-primary btn-lg">Explore Books</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif

</div>
