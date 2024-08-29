<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <title>
        Sazao || e-Commerce HTML Template
    </title>
    <link rel="icon" type="image/png" href="{{asset('Auth/images/favicon.png')}}">
    <link rel="stylesheet" href="{{asset('Auth/css/all.min.css')}}">
    <link rel="stylesheet" href="{{asset('Auth/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('Auth/css/select2.min.css')}}">
    <link rel="stylesheet" href="{{asset('Auth/css/slick.css')}}">
    <link rel="stylesheet" href="{{asset('Auth/css/jquery.nice-number.min.css')}}">
    <link rel="stylesheet" href="{{asset('Auth/css/jquery.calendar.css')}}">
    <link rel="stylesheet" href="{{asset('Auth/css/add_row_custon.css')}}">
    <link rel="stylesheet" href="{{asset('Auth/css/mobile_menu.css')}}">
    <link rel="stylesheet" href="{{asset('Auth/css/jquery.exzoom.css')}}">
    <link rel="stylesheet" href="{{asset('Auth/css/multiple-image-video.css')}}">
    <link rel="stylesheet" href="{{asset('Auth/css/ranger_style.css')}}">
    <link rel="stylesheet" href="{{asset('Auth/css/jquery.classycountdown.css')}}">
    <link rel="stylesheet" href="{{asset('Auth/css/venobox.min.css')}}">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <link rel="stylesheet" href="{{asset('Auth/css/style.css')}}">
    <link rel="stylesheet" href="{{asset('Auth/css/responsive.css')}}">
    <!-- <link rel="stylesheet" href="css/rtl.css"> -->
</head>

<body>



    <!--============================
        Main Content Start
    ==============================-->
        @yield('content')
    <!--============================
       Main Content End
    ==============================-->






    <!--jquery library js-->
    <script src="{{asset('Auth/js/jquery-3.6.0.min.js')}}"></script>
    <!--bootstrap js-->
    <script src="{{asset('Auth/js/bootstrap.bundle.min.js')}}"></script>
    <!--font-awesome js-->
    <script src="{{asset('Auth/js/Font-Awesome.js')}}"></script>
    <!--select2 js-->
    <script src="{{asset('Auth/js/select2.min.js')}}"></script>
    <!--slick slider js-->
    <script src="{{asset('Auth/js/slick.min.js')}}"></script>
    <!--simplyCountdown js-->
    <script src="{{asset('Auth/js/simplyCountdown.js')}}"></script>
    <!--product zoomer js-->
    <script src="{{asset('Auth/js/jquery.exzoom.js')}}"></script>
    <!--nice-number js-->
    <script src="{{asset('Auth/js/jquery.nice-number.min.js')}}"></script>
    <!--counter js-->
    <script src="{{asset('Auth/js/jquery.waypoints.min.js')}}"></script>
    <script src="{{asset('Auth/js/jquery.countup.min.js')}}"></script>
    <!--add row js-->
    <script src="{{asset('Auth/js/add_row_custon.js')}}"></script>
    <!--multiple-image-video js-->
    <script src="{{asset('Auth/js/multiple-image-video.js')}}"></script>
    <!--sticky sidebar js-->
    <script src="{{asset('Auth/js/sticky_sidebar.js')}}"></script>
    <!--price ranger js-->
    <script src="{{asset('Auth/js/ranger_jquery-ui.min.js')}}"></script>
    <script src="{{asset('Auth/js/ranger_slider.js')}}"></script>
    <!--isotope js-->
    <script src="{{asset('Auth/js/isotope.pkgd.min.js')}}"></script>
    <!--venobox js-->
    <script src="{{asset('Auth/js/venobox.min.js')}}"></script>
    <!--Toaster js-->
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <!--Sweetalert js-->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!--classycountdown js-->
    <script src="{{asset('Auth/js/jquery.classycountdown.js')}}"></script>
    <!--toaster-->
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <!--main/custom js-->
    <script src="{{asset('Auth/js/main.js')}}"></script>

    <script>
        @if ($errors->any())
            @foreach ($errors->all() as $error)
                    toastr.error("{{ $error }}")
            @endforeach
        @endif
      </script>
</body>

</html>
