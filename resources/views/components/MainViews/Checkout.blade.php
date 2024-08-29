<x-Home.Layout>
    @if( Cart::count() )
    <style>
        ul.custom-bullets {
          list-style: none; /* Remove default bullets */
          padding-left: 1em; /* Optional: add some padding to align bullets */
        }

        ul.custom-bullets li::before {
          content: '●'; /* Unicode for a black circle */
          color: black; /* Bullet color */
          display: inline-block;
          width: 1em; /* Bullet spacing */
          margin-left: -1em; /* Bullet positioning */
        }
        ul.custom-bullets li {
    margin-left: 1.5em; /* Adjust the left margin as needed */
  }
      </style>
    <section id="bootstrap__checkout_page" class="mt-5">
        <div class="container">
            <div class="row">
                <div class="col-xl-3 col-lg-3">
                    <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                        <button class="nav-link common_btn active" id="v-pills-home-tab" data-bs-toggle="pill" data-bs-target="#v-pills-home" type="button" role="tab" aria-controls="v-pills-home" aria-selected="true">Stripe</button>
                        <button class="nav-link common_btn" id="v-pills-profile-tab" data-bs-toggle="pill" data-bs-target="#v-pills-profile" type="button" role="tab" aria-controls="v-pills-profile" aria-selected="false">Bank Payment</button>
                    </div>
                </div>
                <div class="col-xl-5 col-lg-5">
                    <div class="tab-content" id="v-pills-tabContent">
                        <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">
                            <div class="row">
                                <div class="col-12">
                                    <form method="POST" action="{{ route('payment.checkout') }}">
                                        @csrf
                                        <div class="card shadow-sm mb-4">
                                            <div class="card-body">
                                                <img class="s-img bdr-4" width="166" src="https://s.yimg.com/fz/api/res/1.2/WVtKzvhzDzbBukOmxRiqzA--~C/YXBwaWQ9c3JjaGRkO2ZpPWZpdDtoPTI0MDtxPTgwO3c9MzMy/https://s.yimg.com/zb/imgv1/a4226c38-6f1e-3c4d-b60a-b677b2869e62/t_500x300">

                                                <div class="row mb-3">
                                                  <ul class="custom-bullets">
                                                    <b>Available Payment Methods:</b>
                                                    <li>VISA</li>
                                                    <li>Master Card</li>
                                                    <li>American Express</li>
                                                    <li>JCB</li>
                                                  </ul>
                                                </div>
                                                <div class="mb-3">

                                                </div>
                                                <button type="submit" class="btn btn-primary common_btn">Checkout</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="v-pills-profile" role="tabpanel" aria-labelledby="v-pills-profile-tab">
                            <div class="card shadow-sm mb-4">
                                <div class="card-body">
                                    <p>The bank payment is coming soon, bringing relief from financial worries. Excitement is building as we anticipate the stability and opportunities it will bring. Soon, we'll have the funds we need to meet our obligations.</p>
                                    <!-- Additional content for bank payment option -->
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="v-pills-messages" role="tabpanel" aria-labelledby="v-pills-messages-tab">
                            <div class="card shadow-sm mb-4">
                                <div class="card-body">
                                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Libero, tempora cum optio cumque rerum dolor impedit exercitationem? Eveniet suscipit repellat, quae natus hic assumenda consequatur excepturi ducimus.</p>
                                    <!-- Additional content for mobile banking option -->
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="v-pills-settings" role="tabpanel" aria-labelledby="v-pills-settings-tab">
                            <div class="card shadow-sm mb-4">
                                <div class="card-body">
                                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Libero, tempora cum optio cumque rerum dolor impedit exercitationem? Eveniet suscipit repellat, quae natus hic assumenda consequatur excepturi ducimus.</p>
                                    <!-- Additional content for cash on delivery option -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4">
                    <div class="wsus__pay_booking_summary shadow mb-4" style="background-color: #ffffff; padding: 20px;">

                        @foreach ($cart as $cartitem)
                        <h5><b>Book Name: </b>{{ $cartitem->name }}</h5>
                        <p>Book Quanity: <span>{{ $cartitem->qty }}</span></p>
                        <p>Book Price: <span>${{ $cartitem->price }}</span></p>
                        <p>Book Total: <span>${{ $cartitem->price * $cartitem->qty }}</span></p>
                        @endforeach

                        <p><b>Shipping Fee:</b> <span>$0.00</span></p>
                        <p><b>Tax:</b> <span>$0.00</span></p>
                        <h6><b>Total:</b> <span>${{ Cart::subtotal() }}</span></h6>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @elseif (session('purchased'))
    <!-- hay bdna n3mela 3l if iza kenet lcart fadye btbayen -->
    <section id="bootstrap__cart_view">
        <div class="container">
            <div class="row justify-content-center align-items-center" style="height: 100vh;">
                <div class="col-md-6 text-center">
                    <div class="card p-4 shadow">
                        <h2 class="mb-4">Your order  is done sucessfully</h2>
                        <p class="lead mb-4">Explore our Books and find something you love.</p>
                        <a href="{{ route('index') }}" class="btn btn-primary btn-lg">Explore Books</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
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
</x-Home.Layout>
