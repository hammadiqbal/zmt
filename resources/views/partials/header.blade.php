<!DOCTYPE html>
<html lang="en">

<head>
    
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/lib/images/favicon.png') }}">
    <title>{{ config('app.name') }}</title>
    <!-- Bootstrap Core CSS -->
    <link href="{{ asset('assets/lib/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    {{-- <link href="{{ asset('assets/lib/plugins/jquery.steps/css/jquery.steps.css')}}" rel="stylesheet"> --}}

    <link href="{{ asset ('assets/lib/plugins/bootstrap-switch/bootstrap-switch.min.css') }}" rel="stylesheet">
    <link href="{{ asset ('assets/lib/plugins/select2/select2.min.css') }}" rel="stylesheet">
    <link href="{{ asset ('assets/lib/plugins/bootstrap-select/bootstrap-select.min.css') }}" rel="stylesheet" />
    <link href="{{ asset ('assets/lib/plugins/multiselect/css/multi-select.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset ('assets/lib/plugins/bootstrap-tagsinput/dist/bootstrap-tagsinput.css')}}" rel="stylesheet" />

    {{-- <link href="{{ asset ('assets/lib/plugins/cropper/cropper.min.css') }}" rel="stylesheet"> --}}

    <!-- Custom CSS -->
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
    <!-- You can change the theme colors from here -->
    <link href="{{ asset('assets/css/colors/default-dark.css') }}" id="theme" rel="stylesheet">

    <link href="{{ asset('assets/lib/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/lib/plugins/bootstrap-daterangepicker/daterangepicker.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/lib/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- icheck -->
    <link href="{{ asset('assets/lib/plugins/icheck/skins/all.css') }}" rel="stylesheet">
    <!-- icheck -->
    <link href="{{ asset('assets/lib/plugins/sweetalert/sweetalert2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/lib/plugins/dropify/dist/css/dropify.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/lib/plugins/nestable/nestable.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/lib/plugins/css-chart/css-chart.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .modal-fullscreen {
        width: 100vw;
        max-width: none;
        height: 100vh;
        margin: 0;
    }

    .modal-fullscreen .modal-content {
        height: 100%;
        border: 0;
        border-radius: 0;
    }

    .modal-fullscreen .modal-body {
        overflow-y: auto;
    }
    </style>
</head>

<body class="fix-header fix-sidebar card-no-border">
    <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- ============================================================== -->
    <div class="preloader">
        <svg class="circular" viewBox="25 25 50 50">
            <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10" /> </svg>
    </div>
    <div id="ajax-loader" style="display: none;">
        <img src="{{ asset('assets/lib/images/loader.gif') }}" width="70" alt="Loading...">
    </div>
    <!-- ============================================================== -->
    <!-- Main wrapper - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <div id="main-wrapper">
