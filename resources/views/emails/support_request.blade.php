<!DOCTYPE html>
<html>
<head>
    <title>Support Request: {{ $user_name ?? "N/A"}}</title>
</head>
<body>
    <h1>New Support Request Received from: {{ $user_name ?? 'N/A' }}</h1>
    <p><strong>Message:</strong></p>
    <p>{{ $message }}</p>
    <p><strong>User Name:</strong> {{ $user_name ?? 'N/A' }}</p>
    <p><strong>User Email:</strong> {{ $user_email ?? 'N/A' }}</p>
</body>
</html>