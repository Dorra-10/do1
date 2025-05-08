@extends('layouts.app')

@section('content')

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12 mt-5">
                    <h3 class="page-title mt-3">Good Morning {{ Auth::user()->name }}!</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card board1 fill">
                    <div class="card-body">
                        <div class="dash-widget-header">
                        <div>
                            <h3 class="card_widget_header">{{ $totalUsers }}</h3>
                            <h6 class="text-muted">
                                <a href="{{ route('users.index') }}" class="text-muted text-decoration-none">Total Users</a>
                            </h6>
                        </div>

                            <div class="ml-auto mt-md-3 mt-lg-0">
                                <span class="opacity-7 text-muted">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#009688" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users">
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="8.5" cy="7" r="4"></circle>
                                        <line x1="20" y1="8" x2="20" y2="14"></line>
                                        <line x1="23" y1="11" x2="17" y2="11"></line>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card board1 fill">
                    <div class="card-body">
                        <div class="dash-widget-header">
                        <div>
                            <h3 class="card_widget_header">{{ $totalDocuments }}</h3>
                            <h6 class="text-muted">
                                <a href="{{ route('documents.index') }}" class="text-muted text-decoration-none">Total Documents</a>
                            </h6>
                        </div>

                            <div class="ml-auto mt-md-3 mt-lg-0">
                                <span class="opacity-7 text-muted">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#009688" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                        <polyline points="14 2 14 8 20 8"/>
                                        <line x1="16" y1="13" x2="8" y2="13"/>
                                        <line x1="16" y1="17" x2="8" y2="17"/>
                                        <polyline points="10 9 9 9 8 9"/>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card board1 fill">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <div>
                                <h3 class="card_widget_header">{{ $totalImports }}</h3>
                                <h6 class="text-muted">
                              
                                    <a href="{{ route('impoexpo.impo.index') }}" class="text-primary ms-1">Total Imports</a>
                                </h6>
                            </div>

                            <div class="ml-auto mt-md-3 mt-lg-0">
                                <span class="opacity-7 text-muted">
                                    <i class="fa-solid fa-file-import" style="color: #009688; font-size: 24px;"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card board1 fill">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <div>
                                <h3 class="card_widget_header">{{ $totalExports }}</h3>
                                <h6 class="text-muted">
                                <a href="{{ route('impoexpo.expo.index') }}" class="text-primary ms-1">Total Exports</a>
                                </h6>
                            </div>
                            <div class="ml-auto mt-md-3 mt-lg-0">
                                <span class="opacity-7 text-muted">
                                    <i class="fa-solid fa-file-export" style="color: #009688; font-size: 24px;"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

		<div class="row justify-content-center">
    <div class="col-md-12 col-lg-6">
        <div class="card card-chart">
            <div class="card-header">
                <h4 class="card-title">Projects by Type</h4>
            </div>
            <div class="card-body">
                <div id="donut-chart" style="min-height: 350px;"></div>
            </div>
        </div>
    </div>
</div>


        </div>
    </div>
</div>

<script src="assets/plugins/raphael/raphael.min.js"></script>
<script src="assets/plugins/morris/morris.min.js"></script>
<script src="assets/js/chart.morris.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
    const projectTypeStats = @json($projectTypeStats);

    const labels = Object.keys(projectTypeStats);
    const values = Object.values(projectTypeStats);

    const options = {
        series: values,
        chart: {
            type: 'donut',
            height: 350
        },
        labels: labels,
        dataLabels: {
            enabled: true
        },
        plotOptions: {
            pie: {
                donut: {
                    labels: {
                        show: true,
                        name: {
                            show: true,
                            fontSize: '22px',
                            fontWeight: 600
                        },
                        value: {
                            show: true,
                            fontSize: '18px'
                        },
                        total: {
                            show: true,
                            label: 'Total Projects',
                            fontSize: '16px',
                            formatter: function (w) {
                                return w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                            }
                        }
                    }
                }
            }
        },
        colors: ['#26A69A', '#00897B', '#00796B', '#004D40'], // Couleurs foncées
        legend: {
            position: 'bottom'
        }
    };

    const chart = new ApexCharts(document.querySelector("#donut-chart"), options);
    chart.render();
</script>

@endsection
