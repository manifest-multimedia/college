<!DOCTYPE html>
<html lang="en">

<head>
    <title>{{ config('app.name', 'Laravel') }} - {{ $title ?? 'Dashboard' }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,400;0,500;0,700;1,400&display=swap">
    <link rel="stylesheet" href="{{ asset('dashboard/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dashboard/css/style.css') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/pnmtc-logo.png') }}">
</head>

<body>
    <x-partials.header pageTitle="Student Dashboard" pageDescription="Student Dashboard" />
    
    <main class="py-4">
        {{ $slot }}
    </main>
    
    <x-partials.footer />

    <script src="{{ asset('dashboard/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('dashboard/js/main.js') }}"></script>
</body>

</html>