<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ trans('panel.site_title') }}</title>
    <meta name="description" content="Schleuse Eins is a community based off location event series. Our focus is to create a high quality club experience in extraordinary places. See you on the dancefloor!">
    <link rel="icon" type="image/jpeg" href="/img/schleuse.jpeg">

    <meta property="og:title" content="{{ trans('panel.site_title') }}" />
    <meta property="og:type" content="website">
    <meta property="og:image" content="https://schleuse.eu/img/schleuse.jpeg" />
    <meta property="og:url" content="https://schleuse.eu/" />
    <meta property="og:description" name="description" content="Schleuse Eins is a community based off location event series. Our focus is to create a high quality club experience in extraordinary places. See you on the dancefloor!">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ trans('panel.site_title') }}">
    <meta name="twitter:description" content="Schleuse Eins is a community based off location event series. Our focus is to create a high quality club experience in extraordinary places. See you on the dancefloor!">
    <meta name="twitter:image" content="http://schleuse.eu/img/schleuse.jpeg">

    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet" />
    <link href="/css/dps/coreui.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" rel="stylesheet" />
    <link href="https://use.fontawesome.com/releases/v5.2.0/css/all.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.5.1/min/dropzone.min.css" rel="stylesheet" />
    <link href="{{ asset('css/fonts.css') }}" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,500;0,800;1,800&display=swap" rel="stylesheet">
    <link href="{{ asset('css/custom.css') }}?v=20240214" rel="stylesheet" />
    <link href="{{ asset('css/site.css') }}?v=20240130" rel="stylesheet" />
    @yield('styles')
    <script src="https://kit.fontawesome.com/cbac56de10.js" crossorigin="anonymous"></script>
</head>

<body class="header-fixed {{ $siteType ?? '' }}">
@include('partials.site.navigation')
<div class="flex-row align-items-center p-0" id="page-content">
    <div class="container-fluid p-0">
        @yield("content")
    </div>
</div>
<!--<script type="text/javascript" src="https://unpkg.com/@coreui/coreui@3.2.2/dist/js/coreui.min.js"></script>-->
@yield('scripts')
<div class="bar-wrap"><div class="bar"></div></div>
</body>

</html>
