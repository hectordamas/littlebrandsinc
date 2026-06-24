<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @yield('title')
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta name="csrf-token" content="{{ csrf_token() }}">


    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="#" />
    <meta name="keywords"
        content="Admin , Responsive, Landing, Bootstrap, App, Template, Mobile, iOS, Android, apple, creative app" />
    <meta name="author" content="#" />

    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Google font-->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,800" rel="stylesheet" />
    <link rel="icon" href="{{ asset('assets/img/favicon.png') }}" type="image/x-icon" />

    <link rel="stylesheet" href="{{ asset('assets/files/bower_components/bootstrap/dist/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/files/assets/icon/themify-icons/themify-icons.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/files/assets/icon/icofont/css/icofont.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/files/assets/css/style.css') }}" />

    @yield('styles')

    <style>
        .login-bg {
            position: relative;
            min-height: 100vh;
            padding: 60px 0;
            background: #000;
        }

        /* Split screen with real photos of the programs */
        .login-bg::before,
        .login-bg::after {
            content: '';
            position: fixed;
            top: 0;
            bottom: 0;
            width: 50%;
            background-size: cover;
            background-position: center;
            z-index: 0;
        }

        .login-bg::before {
            left: 0;
            background-image: url('{{ asset('landing_page/assets/images/img6.jpeg') }}');
        }

        .login-bg::after {
            right: 0;
            background-image: url('{{ asset('landing_page/assets/images/img4.jpeg') }}');
        }

        @media (max-width: 767.98px) {
            .login-bg::before {
                width: 100%;
                height: 50%;
                top: 0;
                bottom: auto;
            }
            .login-bg::after {
                width: 100%;
                height: 50%;
                top: 50%;
                bottom: 0;
                left: 0;
            }
        }

        /* Overlay */
        .login-bg .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(2px);
            z-index: 1;
        }

        /* Contenido encima */
        .login-bg .content-wrapper {
            position: relative;
            z-index: 2;
        }
    </style>

</head>


<body class="fix-menu">
    <!-- Pre-loader start -->
    <div class="theme-loader">
        <div class="ball-scale">
            <div class="contain">
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



    <section class="login-block login-bg">
        <div class="overlay"></div>

        <div class="content-wrapper">
            @yield('content')
        </div>
    </section>

    <script src="{{ asset('assets/files/bower_components/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/files/bower_components/jquery-ui/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/files/bower_components/popper.js/dist/umd/popper.min.js') }}"></script>
    <script src="{{ asset('assets/files/bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/files/bower_components/jquery-slimscroll/jquery.slimscroll.js') }}"></script>
    <script src="{{ asset('assets/files/bower_components/modernizr/modernizr.js') }}"></script>
    <script src="{{ asset('assets/files/bower_components/modernizr/feature-detects/css-scrollbars.js') }}"></script>
    <script src="{{ asset('assets/files/bower_components/i18next/i18next.min.js') }}"></script>
    <script src="{{ asset('assets/files/bower_components/i18next-xhr-backend/i18nextXHRBackend.min.js') }}"></script>
    <script
        src="{{ asset('assets/files/bower_components/i18next-browser-languagedetector/i18nextBrowserLanguageDetector.min.js') }}">
    </script>
    <script src="{{ asset('assets/files/bower_components/jquery-i18next/jquery-i18next.min.js') }}"></script>
    <script src="{{ asset('assets/files/assets/js/common-pages.js') }}"></script>
    @yield('scripts')
</body>

</html>
