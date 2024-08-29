<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Book Planet</title>
    <!-- Core theme CSS (includes Bootstrap)-->
    <link rel="stylesheet" href="{{ asset('css/all.min.css') }}" />
    <link href="{{ asset('css/style.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/framework.css') }}" />
    <link href="{{ asset('css/master.css') }}" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="{{asset('dashboard')}}/dropify.css">
    <style>
            .rounded-photo {
        border-radius: 50%;
        width: 200px; /* Adjust the width as needed */
        height: 200px; /* Adjust the height as needed */
        object-fit: cover; /* Ensures the image covers the entire area */
        border: 2px solid #fff; /* Optional: add a border around the image */
    }
    </style>


    <!-- Flag icon-->
    <link rel="stylesheet" type="text/css" href="{{asset('dashboard')}}/assets/css/vendors/flag-icon.css">

    <!-- ico-font-->
    <link rel="stylesheet" type="text/css" href="{{asset('dashboard')}}/assets/css/vendors/icofont.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;500&display=swap" rel="stylesheet" />
</head>
<body id="page-top">
    <div class="page d-flex">
        {{ $sidebar }}
        <div class="content w-full">
          <!-- Start Head -->
            {{ $header }}
          <!-- End Head -->
          @if (session('success'))
            <div class="alert alert-success" id="success-message">
                {{ session('success') }}
            </div>
            @elseif (session('error'))
            <div class="alert alert-error" id="error-message">
                {{ session('error') }}
            </div>
            @elseif (session('already'))
            <div class="alert alert-error" id="already-message">
                {{ session('already') }}
            </div>
        @endif


            <!-- Start Welcome Widget -->
            {{ $profile }}
            {{ $MyBook }}
            {{ $Review }}
            {{ $ManageBooks }}
        </div>
      </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <script src="{{asset('dashboard')}}/assets/js/icons/feather-icon/feather.min.js"></script>
  <script src="{{asset('dashboard')}}/assets/js/icons/feather-icon/feather-icon.js"></script>
  <script src="{{asset('dashboard')}}/assets/js/jquery-3.3.1.min.js"></script>
    <script src="{{asset('dashboard')}}/dropify.js"></script>
    <script>
        $('.dropify').dropify();
    </script>


        @stack('javascripts')

</body>
</html>
