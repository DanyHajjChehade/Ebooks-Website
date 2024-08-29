<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>{{ $setting->name }}</title>
    <!-- Core theme CSS (includes Bootstrap)-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('css/normalize.css') }}" />
    <link href="{{ asset('css/style.css') }}" rel="stylesheet" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;700&display=swap" rel="stylesheet" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body id="page-top">
    <!-- start header -->
    <div class="header" id="header">
        <div class="container">
          <a href="#page-top" class="logo">{{ $setting->name }}</a>
          <ul class="main-nav">
            <li><a href="{{ route('index') }}">Home</a></li>
            @guest
            <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Register</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
             @endguest
            @admin
            <li class="nav-item"><a class="nav-link" href="{{ route('dashboard.categories.index') }}">Dashboard</a></li>
            @endadmin
             @auth
            <li class="nav-item"><a class="nav-link" href="/profile/{{ auth()->user()->username }}">Profile</a></li>
            <li class="nav-item">
                <form class="nav-link" method="POST" action="{{ route('logout') }}">
                  @csrf
                  <div class="mysubmit-container">
                    <input class="mysubmit" type="submit" value="Logout">
                  </div>
                </form>
              </li>
            @endauth
            <li class="nav-item"><a class="nav-link" href="{{ route('cart.index') }}">Cart ({{ \Gloudemans\Shoppingcart\Facades\Cart::content()->count(); }})</a></li>
            <li>
              <a href="#">Other Links</a>
              <!-- Start Megamenu -->
              <div class="mega-menu">
                <div class="image">
                  <img src="{{ $setting->logo }}" alt="" />
                </div>
                <ul class="links">
                  <li>
                    <a href="/#categories"><i class="fas fa-folder fa-fw"></i>Categories</a>
                  </li>
                  <li>
                    <a href="/#testimonials"><i class="fas fa-comments fa-fw"></i> Testimonials</a>
                  </li>
                </ul>
                <ul class="links">
                    <li>
                        <a href="/#authors"><i class="fas fa-user fa-fw"></i> Authors</a>
                      </li>
                      <li>
                        <a href="/#comments"><i class="fas fa-comments fa-fw"></i>Write A Comment</a>
                      </li>
                </ul>
              </div>
              <!-- End Megamenu -->
            </li>
          </ul>
        </div>
      </div>
      <!-- End Header -->
      @if (session('success'))
            <div class="alert alert-success" id="success-message">
                {{ session('success') }}
            </div>
        @endif

    {{ $slot }}

    <!-- Start Footer -->
    <div class="footer">
        <div class="container">
          <div class="box">
            <h3>{{ $setting->name }}</h3>
            <ul class="social">
              <li>
                <a href="https://www.facebook.com/{{ $setting->facebook }}" class="facebook">
                  <i class="fab fa-facebook-f"></i>
                </a>
              </li>
              <li>
                <a href="https://www.twitter.com/{{ $setting->twitter }}" class="twitter">
                  <i class="fab fa-twitter"></i>
                </a>
              </li>
              <li>
                <a href="https://www.youtube.com/{{  $setting->youtube  }}" class="youtube">
                  <i class="fab fa-youtube"></i>
                </a>
              </li>
              <li>
                <a href="https://www.youtube.com/{{  $setting->youtube  }}" class="facebook">
                  <i class="fab fa-instagram"></i>
                </a>
              </li>

            </ul>
            <p class="text">
              {{ $setting->description }}
            </p>
          </div>
          <div class="box">
            <ul class="links">
              <li><a href="https://www.facebook.com/{{ $setting->facebook }}">https://facebook.com/{{ $setting->facebook}}</a></li>
              <li><a href="https://www.twitter.com/{{ $setting->twitter }}">https://twitter.com/{{ $setting->twitter}}</a></li>
              <li><a href="https://www.instagram.com/{{  $setting->instagram }}">https://instagram.com/{{ $setting->instagram}}</a></li>
              <li><a href="https://www.youtube.com/{{  $setting->youtube  }}">https://youtube.com/{{ $setting->youtube}}</a></li>
              <li><a href="https://www.tiktok.com/{{  $setting->tiktok}}">https://tiktok.com/{{ $setting->tiktok}}</a></li>
            </ul>
          </div>
          <div class="box">
            <div class="line">
              <i class="fas fa-map-marker-alt fa-fw"></i>
              <div class="info">{{ $setting->address }}</div>
            </div>
            <div class="line">
              <i class="far fa-clock fa-fw"></i>
              <div class="info">{{ $setting->email }}</div>
            </div>
            <div class="line">
              <i class="fas fa-phone-volume fa-fw"></i>
              <div class="info">
                <span>{{ $setting->phone }}</span>
              </div>
            </div>
          </div>
          <div class="box footer-gallery">
            <img src="{{ $setting->logo }}" alt="" />
          </div>
        </div>
        <p class="copyright">Made With &lt;3 By {{ $setting->name }} team</p>
      </div>

      <!-- End Footer -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Check if the success message exists
            const successMessage = document.getElementById('success-message');
            if (successMessage) {
                // Set a timeout to hide the message after 4 seconds
                setTimeout(() => {
                    successMessage.style.opacity = '0';
                    setTimeout(() => successMessage.remove(), 500); // Remove the element after transition
                }, 4000);
            }
        });
    </script>
</body>
</html>
