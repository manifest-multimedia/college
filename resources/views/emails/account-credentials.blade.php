<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Account Credentials</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #1e293b;
            background-color: #f1f5f9;
            margin: 0;
            padding: 24px;
        }
        .container {
            max-width: 580px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: #ffffff;
            padding: 32px 28px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.02em;
        }
        .header p {
            margin: 6px 0 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .content {
            padding: 32px 28px;
        }
        .greeting {
            font-size: 16px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 12px;
        }
        .text {
            font-size: 14px;
            color: #475569;
            margin-bottom: 20px;
        }
        .card-credentials {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin: 24px 0;
        }
        .cred-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed #cbd5e1;
            font-size: 14px;
        }
        .cred-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        .cred-label {
            color: #64748b;
            font-weight: 500;
        }
        .cred-value {
            color: #0f172a;
            font-weight: 600;
            font-family: monospace;
            font-size: 15px;
        }
        .alert-box {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 14px 16px;
            border-radius: 6px;
            margin: 20px 0;
            font-size: 13px;
            color: #1e40af;
        }
        .btn-wrapper {
            text-align: center;
            margin: 28px 0 12px 0;
        }
        .btn {
            display: inline-block;
            background-color: #2563eb;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 28px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
        }
        .footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 20px 28px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ config('branding.institution.name', config('app.name', 'College Portal')) }}</h1>
            <p>Account Credential Notification</p>
        </div>
        <div class="content">
            <div class="greeting">Hello {{ $data['name'] ?? 'User' }},</div>
            <p class="text">
                Your account password has been reset by the System Administrator. You can use the temporary credentials below to log into the portal:
            </p>

            <div class="card-credentials">
                @if(!empty($data['student_id']))
                <div class="cred-row">
                    <span class="cred-label">Student ID:</span>
                    <span class="cred-value">{{ $data['student_id'] }}</span>
                </div>
                @endif
                <div class="cred-row">
                    <span class="cred-label">Login Email:</span>
                    <span class="cred-value">{{ $data['email'] }}</span>
                </div>
                <div class="cred-row">
                    <span class="cred-label">Temporary Password:</span>
                    <span class="cred-value">{{ $data['password'] }}</span>
                </div>
            </div>

            @if(!empty($data['force_change']))
            <div class="alert-box">
                <strong>Password Change Required:</strong> For your security, you will be prompted to create your own new, personal password immediately upon your initial login.
            </div>
            @endif

            <div class="btn-wrapper">
                <a href="{{ $data['login_url'] ?? route('login') }}" class="btn" target="_blank">Sign In to Portal</a>
            </div>
        </div>
        <div class="footer">
            <p>If you did not request or expect this reset, please contact the institution IT helpdesk immediately.</p>
            <p>&copy; {{ date('Y') }} {{ config('branding.institution.name', config('app.name', 'College Portal')) }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
