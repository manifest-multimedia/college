<!DOCTYPE html>
<html>
<head>
    <title>New Feature Request: {{ $feature_title }}</title>
</head>
<body>
    <h1>New Feature Request: {{ $feature_title }}</h1>
    <p><strong>Description:</strong></p>
    <p>{{ $feature_description }}</p>
    <p><strong>User Name:</strong> {{ $user_name ?? 'N/A' }}</p>
    <p><strong>User Email:</strong> {{ $user_email ?? 'N/A' }}</p>
</body>
</html>