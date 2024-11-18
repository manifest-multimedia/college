<!DOCTYPE html>
<html lang="en">

<head>
    <title>{{ config('app.name', 'Laravel') }} - {{ $title }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,400;0,500;0,700;1,400&display=swap">
    <link rel="stylesheet" href="{{ asset('backend/css/bootstrap/bootstrap.min.css') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/pnmtc-logo.png') }}">
</head>

<body>
    <style>
        footer {
            position: relative;
            margin-bottom: -50px;
            /* adjust this value to match the footer height */
        }
    </style>
    <x-partials.header pageTitle="Dashboard" pageDescription="Dashboard" />
    {{ $slot }}
    <x-partials.footer />

    <script src="{{ asset('backend/js/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('backend/js/main.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="{{ asset('backend/js/charts-demo.js') }}"></script>
</body>

</html>
