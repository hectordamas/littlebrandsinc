<!DOCTYPE html>
<html lang="en">

<head>
    @yield('title')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="#">
    <meta name="keywords"
        content="Admin , Responsive, Landing, Bootstrap, App, Template, Mobile, iOS, Android, apple, creative app">
    <meta name="author" content="#">
    <!-- Favicon -->
    <link rel="icon" href="{{ asset('assets/img/favicon.png') }}" type="image/x-icon">

    <!-- Google font -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,800" rel="stylesheet">

    <!-- Bootstrap -->
    <link rel="stylesheet" href="{{ asset('assets/files/bower_components/bootstrap/dist/css/bootstrap.min.css') }}">

    <!-- Radial chart -->
    <link rel="stylesheet" href="{{ asset('assets/files/assets/pages/chart/radial/css/radial.css') }}">

    <!-- Feather icons -->
    <link rel="stylesheet" href="{{ asset('assets/files/assets/icon/feather/css/feather.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />

    <!-- Main style -->
    <link rel="stylesheet" href="{{ asset('assets/files/assets/css/style.css') }}">


    <!---Datatables-->
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/files/bower_components/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/files/assets/pages/data-table/css/buttons.dataTables.min.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/files/bower_components/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/files/assets/pages/data-table/extensions/buttons/css/buttons.dataTables.min.css') }}">

    <!-- Scrollbar -->
    <link rel="stylesheet" href="{{ asset('assets/files/assets/css/jquery.mCustomScrollbar.css') }}">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!--Custom CSS-->
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">

    @yield('styles')

</head>
<!-- Menu sidebar static layout -->

<body>
    <!-- Pre-loader start -->
    <div class="theme-loader">
        <div class="ball-scale">
            <div class='contain'>
                <div class="ring">
                    <div class="frame"></div>
                </div>
                <div class="ring">
                    <div class="frame"></div>
                </div>
                <div class="ring">
                    <div class="frame"></div>
                </div>
                <div class="ring">
                    <div class="frame"></div>
                </div>
                <div class="ring">
                    <div class="frame"></div>
                </div>
                <div class="ring">
                    <div class="frame"></div>
                </div>
                <div class="ring">
                    <div class="frame"></div>
                </div>
                <div class="ring">
                    <div class="frame"></div>
                </div>
                <div class="ring">
                    <div class="frame"></div>
                </div>
                <div class="ring">
                    <div class="frame"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- Pre-loader end -->
    <div id="pcoded" class="pcoded">
        <div class="pcoded-overlay-box"></div>
        <div class="pcoded-container navbar-wrapper">

            <nav class="navbar header-navbar pcoded-header">
                <div class="navbar-wrapper">

                    <div class="navbar-logo text-center">
                        <a class="mobile-menu d-lg-none" id="mobile-collapse" href="#!">
                            <i class="feather icon-menu"></i>
                        </a>

                        <a href="{{ url('/') }}">
                            <img src="{{ asset('assets/img/lbinc-admin.png') }}" alt="Little Brands Inc Logo"
                                style="max-height: 50px;" />
                        </a>

                        <a class="mobile-options">
                            <i class="feather icon-more-horizontal"></i>
                        </a>
                    </div>

                    <div class="navbar-container">
                        <ul class="nav-left">
                            <li class="header-search">
                                <div class="main-search morphsearch-search">
                                    <div class="input-group">
                                        <span class="input-group-prepend search-close">
                                            <i class="feather icon-x input-group-text"></i>
                                        </span>
                                        <input type="text" class="form-control" placeholder="Enter Keyword">
                                        <span class="input-group-append search-btn">
                                            <i class="feather icon-search input-group-text"></i>
                                        </span>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <a href="#!" onclick="javascript:toggleFullScreen()"
                                    class="waves-effect waves-light">
                                    <i class="full-screen feather icon-maximize"></i>
                                </a>
                            </li>
                        </ul>
                        <ul class="nav-right">
                            <li class="header-notification">
                                <div class="dropdown-primary dropdown">
                                    <div class="dropdown-toggle" data-bs-toggle="dropdown">
                                        <i class="feather icon-bell"></i>
                                        <span class="badge bg-c-pink">5</span>
                                    </div>
                                    <ul class="show-notification notification-view dropdown-menu"
                                        data-dropdown-in="fadeIn" data-dropdown-out="fadeOut">
                                        <li>
                                            <h6>Notifications</h6>
                                            <label class="form-label label label-danger">New</label>
                                        </li>
                                        <li>
                                            <div class="d-flex">
                                                <div class="flex-shrink-0">
                                                    <img class="d-flex align-self-center img-radius"
                                                        src="../files/assets/images/avatar-4.jpg"
                                                        alt="Generic placeholder image">
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h5 class="notification-user">John Doe</h5>
                                                    <p class="notification-msg">Lorem ipsum dolor sit amet,
                                                        consectetuer
                                                        elit.</p>
                                                    <span class="notification-time">30 minutes ago</span>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="d-flex">
                                                <div class="flex-shrink-0">
                                                    <img class="d-flex align-self-center img-radius"
                                                        src="../files/assets/images/avatar-3.jpg"
                                                        alt="Generic placeholder image">
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h5 class="notification-user">Joseph William</h5>
                                                    <p class="notification-msg">Lorem ipsum dolor sit amet,
                                                        consectetuer
                                                        elit.</p>
                                                    <span class="notification-time">30 minutes ago</span>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="d-flex">
                                                <div class="flex-shrink-0">
                                                    <img class="d-flex align-self-center img-radius"
                                                        src="../files/assets/images/avatar-4.jpg"
                                                        alt="Generic placeholder image">
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h5 class="notification-user">Sara Soudein</h5>
                                                    <p class="notification-msg">Lorem ipsum dolor sit amet,
                                                        consectetuer
                                                        elit.</p>
                                                    <span class="notification-time">30 minutes ago</span>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="user-profile header-notification">
                                <div class="dropdown-primary dropdown">
                                    <div class="dropdown-toggle" data-bs-toggle="dropdown">
                                        <img src="{{ asset('assets/img/user.png') }}" class="img-radius"
                                            alt="{{ Auth::user()->name }} Image Profile">
                                        <span>{{ Auth::user()->name }}</span>
                                        <i class="feather icon-chevron-down"></i>
                                    </div>
                                    <ul class="show-notification profile-notification dropdown-menu"
                                        data-dropdown-in="fadeIn" data-dropdown-out="fadeOut">
                                        <li>
                                            <a href="user-profile.html">
                                                <i class="feather icon-user"></i> Perfil
                                            </a>
                                        </li>
                                        <li>
                                            <form action="{{ route('logout') }}" method="POST" id="logout-form">
                                                @csrf</form>
                                            <a href="javascript:void(0);"
                                                onclick="document.getElementById('logout-form').submit()">
                                                <i class="feather icon-log-out"></i> Cerrar Sesión
                                            </a>
                                        </li>
                                    </ul>

                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>


            <!-- Sidebar inner chat end-->
            <div class="pcoded-main-container">
                <div class="pcoded-wrapper">
                    <nav class="pcoded-navbar">
                        <div class="pcoded-inner-navbar main-menu">
                            <div class="pcoded-navigatio-lavel">Menú</div>
                            <ul class="pcoded-item pcoded-left-item">

                                <li>
                                    <a href="{{ url('/') }}">
                                        <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                                        <span class="pcoded-mtext">Inicio</span>
                                    </a>
                                </li>

                                <!--Inscripciones-->
                                <li class="pcoded-hasmenu">
                                    <a href="javascript:void(0)">
                                        <span class="pcoded-micon">
                                            <i class="far fa-address-book"></i>
                                        </span>
                                        <span class="pcoded-mtext">Inscripciones y Clientes</span>
                                    </a>
                                    <ul class="pcoded-submenu">
                                        <li class="">
                                            <a href="{{ url('enrollment') }}">
                                                <span class="pcoded-mtext">Inscripciones</span>
                                            </a>
                                        </li>
                                        <li class="">
                                            <a href="{{ url('students') }}">
                                                <span class="pcoded-mtext">Estudiantes</span>
                                            </a>
                                        </li>
                                        <li class="">
                                            <a href="{{ url('parents') }}">
                                                <span class="pcoded-mtext">Padres</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>

                                <!--Finanzas-->
                                <li class="pcoded-hasmenu">
                                    <a href="javascript:void(0)">
                                        <span class="pcoded-micon"><i class="fas fa-file-invoice-dollar"></i></span>
                                        <span class="pcoded-mtext">Finanzas y Facturacíón</span>
                                    </a>
                                    <ul class="pcoded-submenu">
                                        <li class=" ">
                                            <a href="widget-statistic.html">
                                                <span class="pcoded-mtext">Movimientos</span>
                                            </a>
                                        </li>
                                        <li class=" ">
                                            <a href="widget-data.html">
                                                <span class="pcoded-mtext">Cuentas por Cobrar</span>
                                            </a>
                                        </li>
                                        <li class="">
                                            <a href="widget-chart.html">
                                                <span class="pcoded-mtext">Cuentas por Pagar</span>
                                            </a>
                                        </li>

                                    </ul>
                                </li>

                                <!--Operaciones-->
                                <li class="pcoded-hasmenu">
                                    <a href="javascript:void(0)">
                                        <span class="pcoded-micon"><i class="fas fa-project-diagram"></i></span>
                                        <span class="pcoded-mtext">Operaciones</span>
                                    </a>
                                    <ul class="pcoded-submenu">
                                        <li class=" ">
                                            <a href="{{ url('courses') }}">
                                                <span class="pcoded-mtext">Cursos</span>
                                            </a>
                                        </li>
                                        <li class=" ">
                                            <a href="{{ url('calendar') }}">
                                                <span class="pcoded-mtext">Calendario</span>
                                            </a>
                                        </li>
                                        <li class="">
                                            <a href="{{ url('trainers') }}">
                                                <span class="pcoded-mtext">Entrenadores</span>
                                            </a>
                                        </li>

                                    </ul>
                                </li>

                                <!--Usuarios-->
                                <li>
                                    <a href="{{ url('users') }}">
                                        <span class="pcoded-micon"><i class="fas fa-user"></i></span>
                                        <span class="pcoded-mtext">Usuarios</span>
                                    </a>
                                </li>

                                <!-- Sedes-->
                                <li>
                                    <a href="{{ url('branches') }}">
                                        <span class="pcoded-micon"><i class="fas fa-building"></i></span>
                                        <span class="pcoded-mtext">Sedes</span>
                                    </a>
                                </li>

                            </ul>
                        </div>
                    </nav>
                    <div class="pcoded-content">
                        <div class="pcoded-inner-content">
                            <div class="main-body">
                                <div class="page-wrapper">
                                    <div class="page-body">
                                        @yield('content')
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="{{ asset('assets/files/bower_components/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/files/bower_components/jquery-ui/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/files/bower_components/popper.js/dist/umd/popper.min.js') }}"></script>
    <script src="{{ asset('assets/files/bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>

    <!-- jquery slimscroll js -->
    <script src="{{ asset('assets/files/bower_components/jquery-slimscroll/jquery.slimscroll.js') }}"></script>

    <!-- modernizr js -->
    <script src="{{ asset('assets/files/bower_components/modernizr/modernizr.js') }}"></script>
    <script src="{{ asset('assets/files/bower_components/modernizr/feature-detects/css-scrollbars.js') }}"></script>

    <!-- Chart js -->
    <script src="{{ asset('assets/files/bower_components/chart.js/dist/Chart.js') }}"></script>

    <!-- Gauge + AmCharts -->
    <script src="{{ asset('assets/files/assets/pages/widget/gauge/gauge.min.js') }}"></script>
    <script src="{{ asset('assets/files/assets/pages/widget/amchart/amcharts.js') }}"></script>
    <script src="{{ asset('assets/files/assets/pages/widget/amchart/serial.js') }}"></script>
    <script src="{{ asset('assets/files/assets/pages/widget/amchart/gauge.js') }}"></script>
    <script src="{{ asset('assets/files/assets/pages/widget/amchart/pie.js') }}"></script>
    <script src="{{ asset('assets/files/assets/pages/widget/amchart/light.js') }}"></script>

    <!-- Custom js -->
    <script src="{{ asset('assets/files/assets/js/pcoded.min.js') }}"></script>
    <script src="{{ asset('assets/files/assets/js/vartical-layout.min.js') }}"></script>
    <script src="{{ asset('assets/files/assets/js/jquery.mCustomScrollbar.concat.min.js') }}"></script>
    <script src="{{ asset('assets/files/assets/pages/dashboard/crm-dashboard.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


    <!--Datables-->
    <script src="{{ asset('assets/files/bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/files/bower_components/datatables.net-buttons/js/dataTables.buttons.min.js') }}">
    </script>
    <script src="{{ asset('assets/files/assets/pages/data-table/js/jszip.min.js') }}"></script>
    <script src="{{ asset('assets/files/assets/pages/data-table/js/pdfmake.min.js') }}"></script>
    <script src="{{ asset('assets/files/assets/pages/data-table/js/vfs_fonts.js') }}"></script>
    <script src="{{ asset('assets/files/assets/pages/data-table/extensions/buttons/js/dataTables.buttons.min.js') }}">
    </script>
    <script src="{{ asset('assets/files/assets/pages/data-table/extensions/buttons/js/buttons.flash.min.js') }}">
    </script>
    <script src="{{ asset('assets/files/assets/pages/data-table/extensions/buttons/js/jszip.min.js') }}"></script>
    <script src="{{ asset('assets/files/assets/pages/data-table/extensions/buttons/js/vfs_fonts.js') }}"></script>
    <script src="{{ asset('assets/files/assets/pages/data-table/extensions/buttons/js/buttons.colVis.min.js') }}">
    </script>
    <script src="{{ asset('assets/files/bower_components/datatables.net-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('assets/files/bower_components/datatables.net-buttons/js/buttons.html5.min.js') }}"></script>

    <script src="{{ asset('assets/files/assets/pages/data-table/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/files/bower_components/datatables.net-responsive/js/dataTables.responsive.min.js') }}">
    </script>
    <script
        src="{{ asset('assets/files/bower_components/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js') }}">
    </script>
    <script src="{{ asset('assets/files/assets/pages/data-table/extensions/buttons/js/extension-btns-custom.js') }}">
    </script>

    <!--SweetAlert2-->
    <script src="{{ asset('assets/sweetalert2/sweetalert2.all.min.js') }}"></script>

    <!-- Custom Scripts -->
    <script src="{{ asset('assets/files/assets/js/script.js') }}"></script>

    <!-- Custom Alert Scripts -->   
    @if (session()->has('success'))
        <script>
            Swal.fire({
                text: "{{ session('success') }}",
                icon: "success",
                confirmButtonText: "Continuar",
                confirmButtonColor: '#28a745'
            });
        </script>
    @endif

    @if (session()->has('error'))
        <script>
            Swal.fire({
                text: "{{ session('error') }}",
                icon: "error",
                confirmButtonText: "Entendido!",
                confirmButtonColor: '#dc3545'
            });
        </script>
    @endif

    @foreach ($errors->all() as $error)
        <script>
            Swal.fire({
                text: "{{ $error }}",
                icon: "error",
                confirmButtonText: "Entendido!",
                confirmButtonColor: '#dc3545'
            });
        </script>
    @endforeach

    <script src="{{ asset('assets/files/assets/js/custom.js') }}"></script>

    @yield('scripts')
</body>

</html>
