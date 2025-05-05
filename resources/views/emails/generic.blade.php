<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'College Notification' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #003366;
            color: white;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .footer {
            background-color: #f4f4f4;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #666;
            margin-top: 20px;
            border-radius: 4px;
        }
        .content {
            padding: 20px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .logo {
            max-height: 60px;
            margin-bottom: 10px;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #003366;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        @if(isset($emailData['logo_url']))
            <img src="{{ $emailData['logo_url'] }}" alt="College Logo" class="logo">
        @endif
        <h2>{{ $emailData['subject'] ?? 'College Notification' }}</h2>
    </div>
    
    <div class="content">
        {!! $content !!}
        
        @if(isset($emailData['action_url']) && isset($emailData['action_text']))
            <div style="text-align: center; margin-top: 25px;">
                <a href="{{ $emailData['action_url'] }}" class="button">{{ $emailData['action_text'] }}</a>
            </div>
        @endif
    </div>
    
    <div class="footer">
        <p>&copy; {{ date('Y') }} College. All rights reserved.</p>
        @if(isset($emailData['unsubscribe_url']))
            <p><a href="{{ $emailData['unsubscribe_url'] }}">Unsubscribe</a></p>
        @endif
    </div>
</body>
</html>