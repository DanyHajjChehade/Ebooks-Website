<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Multikart admin is super flexible, powerful, clean &amp; modern responsive bootstrap 4 admin template with unlimited possibilities.">
    <meta name="keywords"
        content="admin template, Multikart admin template, dashboard template, flat admin template, responsive admin template, web app">
    <meta name="author" content="pixelstrap">
    <link rel="icon" href="{{asset('dashboard')}}/assets/images/dashboard/favicon.png" type="image/x-icon">
    <link rel="shortcut icon" href="{{asset('dashboard')}}/assets/images/dashboard/favicon.png" type="image/x-icon">
    <title>{{ $setting->name }} Admin Template</title>

    <!-- Google font-->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Work+Sans:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,500;1,600;1,700;1,800;1,900&display=swap">

    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap">


    <!-- Font Awesome-->
    <link rel="stylesheet" type="text/css" href="{{asset('dashboard')}}/assets/css/vendors/font-awesome.css">

    <!-- Flag icon-->
    <link rel="stylesheet" type="text/css" href="{{asset('dashboard')}}/assets/css/vendors/flag-icon.css">

    <!-- ico-font-->
    <link rel="stylesheet" type="text/css" href="{{asset('dashboard')}}/assets/css/vendors/icofont.css">

    <!-- Prism css-->
    <link rel="stylesheet" type="text/css" href="{{asset('dashboard')}}/assets/css/vendors/prism.css">

    <!-- Chartist css -->
    <link rel="stylesheet" type="text/css" href="{{asset('dashboard')}}/assets/css/vendors/chartist.css">

    <!-- Bootstrap css-->
    <link rel="stylesheet" type="text/css" href="{{asset('dashboard')}}/assets/css/vendors/bootstrap.css">

    <!-- App css-->
    <link rel="stylesheet" type="text/css" href="{{asset('dashboard')}}/assets/css/style.css">

    <link rel="stylesheet" type="text/css" href="{{asset('dashboard')}}/dropify.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">

</head>

<body>

    <!-- page-wrapper Start-->
    <div class="page-wrapper">

        <!-- Page Header Start-->
        <div class="page-main-header">
            <div class="main-header-right row">

                <div class="mobile-sidebar w-auto">
                    <div class="media-body text-end switch-sm">
                        <label class="switch">
                            <a href="javascript:void(0)">
                                <i id="sidebar-toggle" data-feather="align-left"></i>
                            </a>
                        </label>
                    </div>
                </div>
                <div class="nav-right col">
                    <ul class="nav-menus">

                        <li>
                            <a class="text-dark" href="#!" onclick="javascript:toggleFullScreen()">
                                <i data-feather="maximize-2"></i>
                            </a>
                        </li>



                        <li class="onhover-dropdown">
                            <div class="media align-items-center">
                                <img class="align-self-center pull-right img-50 blur-up lazyloaded"
                                    src="{{asset($setting->logo)}}" alt="header-user">
                                <div class="dotted-animation">
                                    <span class="animate-circle"></span>
                                    <span class="main-circle"></span>
                                </div>
                            </div>

                    <div class="d-lg-none mobile-toggle pull-right">
                        <i data-feather="more-horizontal"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Page Header Ends -->

        <!-- Page Body Start-->
        <div class="page-body-wrapper">

            @include("dashboard.layout.sidebar")
            <div class="page-body">
                @yield("body")
            </div>

            <!-- footer start-->
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-6 footer-copyright text-start">
                            <p class="mb-0">Copyright 2024 © {{ $setting->name }} All rights reserved.</p>
                        </div>
                        <div class="col-md-6 pull-right text-end">
                            <p class=" mb-0">Hand crafted & made with<i class="fa fa-heart"></i></p>
                        </div>
                    </div>
                </div>
            </footer>
            <!-- footer end-->
        </div>
    </div>

    <!-- latest jquery-->
    <script src="{{asset('dashboard')}}/assets/js/jquery-3.3.1.min.js"></script>

    <!-- Bootstrap js-->
    <script src="{{asset('dashboard')}}//assets/js/bootstrap.bundle.min.js"></script>

    <!-- feather icon js-->
    <script src="{{asset('dashboard')}}/assets/js/icons/feather-icon/feather.min.js"></script>
    <script src="{{asset('dashboard')}}/assets/js/icons/feather-icon/feather-icon.js"></script>

    <!-- Sidebar jquery-->
    <script src="{{asset('dashboard')}}/assets/js/sidebar-menu.js"></script>

    <!--chartist js-->
    <script src="{{asset('dashboard')}}/assets/js/chart/chartist/chartist.js"></script>

    <!--chartjs js-->
    <script src="{{asset('dashboard')}}/assets/js/chart/chartjs/chart.min.js"></script>

    <!-- lazyload js-->
    <script src="{{asset('dashboard')}}/assets/js/lazysizes.min.js"></script>

    <!--copycode js-->
    <script src="{{asset('dashboard')}}/assets/js/prism/prism.min.js"></script>
    <script src="{{asset('dashboard')}}/assets/js/clipboard/clipboard.min.js"></script>
    <script src="{{asset('dashboard')}}/assets/js/custom-card/custom-card.js"></script>

    <!--counter js-->
    <script src="{{asset('dashboard')}}/assets/js/counter/jquery.waypoints.min.js"></script>
    <script src="{{asset('dashboard')}}/assets/js/counter/jquery.counterup.min.js"></script>
    <script src="{{asset('dashboard')}}/assets/js/counter/counter-custom.js"></script>

    <!--peity chart js-->
    <script src="{{asset('dashboard')}}/assets/js/chart/peity-chart/peity.jquery.js"></script>

    <!-- Apex Chart Js -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <!--sparkline chart js-->
    <script src="{{asset('dashboard')}}/assets/js/chart/sparkline/sparkline.js"></script>

    <!--Customizer admin-->
    <script src="{{asset('dashboard')}}/assets/js/admin-customizer.js"></script>

    <!--dashboard custom js-->
    <script src="{{asset('dashboard')}}/assets/js/dashboard/default.js"></script>

    <!--right sidebar js-->
    <script src="{{asset('dashboard')}}/assets/js/chat-menu.js"></script>

    <!--height equal js-->
    <script src="{{asset('dashboard')}}/assets/js/height-equal.js"></script>

    <!-- lazyload js-->
    <script src="{{asset('dashboard')}}/assets/js/lazysizes.min.js"></script>

    <!--script admin-->
    <script src="{{asset('dashboard')}}/assets/js/admin-script.js"></script>

    <script src="{{asset('dashboard')}}/dropify.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $('.dropify').dropify();
    </script>


        @stack('javascripts')
</body>

</html>

