@extends('layouts.admin')

@section('title')
    <title>Incio - {{ env('APP_NAME') }}</title>
@endsection

@section('content')
    <div class="row">

        <div class="col-md-12">
            <h5 class="mb-3">Acceso Directo</h5>
        </div>

        <div class="col-xl-3 col-md-6 ">
            <a href="/inscripciones-y-clientes" class="card card-access">
                <div class="card-block">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <h6 class="text-c-yellow m-b-0">Inscripciones y Clientes</h6>
                        </div>
                        <div class="col-4 text-end">
                            <i class="fa-regular fa-address-book f-28"></i>
                        </div>
                    </div>
                </div>

            </a>
        </div>
        <div class="col-xl-3 col-md-6 ">
            <a href="/finanzas-y-facturacion" class="card card-access">
                <div class="card-block">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <h6 class="text-c-green m-b-0">Finanzas y Facturación</h6>
                        </div>
                        <div class="col-4 text-end">
                            <i class="fa-solid fa-file-invoice-dollar f-28"></i>
                        </div>
                    </div>
                </div>

            </a>
        </div>
        <div class="col-xl-3 col-md-6 ">
            <a href="/programacion-y-operaciones" class="card card-access">
                <div class="card-block">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <h6 class="text-c-pink m-b-0">Programación y Operaciones</h6>
                        </div>
                        <div class="col-4 text-end">
                            <i class="fa-solid fa-diagram-project f-28"></i>
                        </div>
                    </div>
                </div>

            </a>
        </div>


        <div class="col-md-12">
            <h5 class="mb-3">Resumen</h5>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-c-yellow text-white">
                <div class="card-block">
                    <div class="row align-items-center">
                        <div class="col">
                            <p class="m-b-5">New Customer</p>
                            <h4 class="m-b-0">852</h4>
                        </div>
                        <div class="col col-auto text-end">
                            <i class="feather icon-user f-50 text-c-yellow"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-c-green text-white">
                <div class="card-block">
                    <div class="row align-items-center">
                        <div class="col">
                            <p class="m-b-5">Income</p>
                            <h4 class="m-b-0">$5,852</h4>
                        </div>
                        <div class="col col-auto text-end">
                            <i class="feather icon-credit-card f-50 text-c-green"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-c-pink text-white">
                <div class="card-block">
                    <div class="row align-items-center">
                        <div class="col">
                            <p class="m-b-5">Ticket</p>
                            <h4 class="m-b-0">42</h4>
                        </div>
                        <div class="col col-auto text-end">
                            <i class="feather icon-book f-50 text-c-pink"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-c-blue text-white">
                <div class="card-block">
                    <div class="row align-items-center">
                        <div class="col">
                            <p class="m-b-5">Orders</p>
                            <h4 class="m-b-0">$5,242</h4>
                        </div>
                        <div class="col col-auto text-end">
                            <i class="feather icon-shopping-cart f-50 text-c-blue"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-header-left ">
                        <h5>Monthly View</h5>
                        <span class="text-muted">For more details about usage,
                            please refer <a href="https://www.amcharts.com/online-store/" target="_blank">amCharts</a>
                            licences.</span>
                    </div>
                </div>
                <div class="card-block-big">
                    <div id="monthly-graph" style="height:250px"></div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-4 col-md-12">
            <div class="card feed-card">
                <div class="card-header">
                    <h5>Feeds</h5>
                </div>
                <div class="card-block">
                    <div class="row m-b-30">
                        <div class="col-auto p-r-0">
                            <i class="feather icon-bell bg-simple-c-blue feed-icon"></i>
                        </div>
                        <div class="col">
                            <h6 class="m-b-5">You have 3 pending tasks. <span class="text-muted f-right f-13">Just
                                    Now</span>
                            </h6>
                        </div>
                    </div>
                    <div class="row m-b-30">
                        <div class="col-auto p-r-0">
                            <i class="feather icon-shopping-cart bg-simple-c-pink feed-icon"></i>
                        </div>
                        <div class="col">
                            <h6 class="m-b-5">New order received <span class="text-muted f-right f-13">Just
                                    Now</span>
                            </h6>
                        </div>
                    </div>
                    <div class="row m-b-30">
                        <div class="col-auto p-r-0">
                            <i class="feather icon-file-text bg-simple-c-green feed-icon"></i>
                        </div>
                        <div class="col">
                            <h6 class="m-b-5">You have 3 pending tasks. <span class="text-muted f-right f-13">Just
                                    Now</span>
                            </h6>
                        </div>
                    </div>
                    <div class="row m-b-30">
                        <div class="col-auto p-r-0">
                            <i class="feather icon-shopping-cart bg-simple-c-pink feed-icon"></i>
                        </div>
                        <div class="col">
                            <h6 class="m-b-5">New order received <span class="text-muted f-right f-13">Just
                                    Now</span>
                            </h6>
                        </div>
                    </div>
                    <div class="row m-b-30">
                        <div class="col-auto p-r-0">
                            <i class="feather icon-file-text bg-simple-c-green feed-icon"></i>
                        </div>
                        <div class="col">
                            <h6 class="m-b-5">You have 3 pending tasks. <span class="text-muted f-right f-13">Just
                                    Now</span>
                            </h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>
@endsection
