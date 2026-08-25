<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ $title ?? 'College360 Online Examination' }}</title>
  
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">

  <!-- Stylesheets -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  @if(file_exists(public_path('frontend/css/bootstrap.min.css')))
    <link rel="stylesheet" href="{{ asset('frontend/css/bootstrap.min.css') }}">
  @endif

  @livewireStyles

  <style>
    :root {
      --exam-primary: {{ config('branding.colors.primary', '#3B82F6') }};
      --exam-navy: #0B192C;
      --exam-dark-blue: #1E3E62;
    }
    body {
      font-family: 'Inter', system-ui, -apple-system, sans-serif;
      margin: 0;
      padding: 0;
      min-height: 100vh;
      min-height: 100dvh;
      background-color: #f8fafc;
      color: #1e293b;
    }
    .heading-font {
      font-family: 'Outfit', 'Inter', sans-serif;
    }
  </style>
</head>
<body>

{{ $slot }}

@livewireScripts
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
