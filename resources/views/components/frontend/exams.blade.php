<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">


  <title>{{ $title }}</title>
  <link rel="stylesheet" href="{{ asset('frontend/css/bootstrap.min.css') }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

{{ $slot }}

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('frontend/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
