<!DOCTYPE html>
<html lang="en">
<head>
    <title>Server Error - {{ config('branding.institution.name', config('app.name')) }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset(config('branding.logo.favicon', '/favicon.ico')) }}">

    <style>
        :root {
            --primary-color: {{ config('branding.colors.primary', '#3B82F6') }};
            --secondary-color: {{ config('branding.colors.secondary', '#64748B') }};
            --accent-color: {{ config('branding.colors.accent', '#10B981') }};
            --danger-color: #DC2626;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #1e293b 0%, #334155 50%, #475569 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            position: relative;
            overflow: hidden;
        }

        /* Animated Background Elements */
        body::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(220, 38, 38, 0.08) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
            z-index: 1;
        }

        body::after {
            content: '';
            position: absolute;
            bottom: 10%;
            left: 10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.08) 0%, transparent 70%);
            border-radius: 50%;
            animation: pulse 4s ease-in-out infinite;
            z-index: 1;
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.8; }
            50% { transform: scale(1.1); opacity: 0.4; }
        }

        .error-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 500px;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2.5rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.15);
            animation: slideUp 0.6s ease-out;
            text-align: center;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .error-icon-wrapper {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100px;
            height: 100px;
            background: rgba(220, 38, 38, 0.15);
            border-radius: 50%;
            margin-bottom: 1.5rem;
            animation: iconPulse 2s ease-in-out infinite;
        }

        @keyframes iconPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .error-icon {
            font-size: 3rem;
            color: var(--danger-color);
        }

        .error-code {
            font-size: 1.25rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 0.5rem;
            letter-spacing: 0.5px;
        }

        .error-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: white;
            margin-bottom: 1rem;
            line-height: 1.3;
        }

        .error-message {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .error-details {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .error-details p {
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.8);
            margin: 0;
            line-height: 1.5;
        }

        .error-details i {
            color: var(--danger-color);
            margin-right: 0.5rem;
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.875rem 2rem;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            gap: 0.5rem;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        .btn-primary:hover {
            background: color-mix(in srgb, var(--primary-color) 90%, black);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
        }

        .institution-branding {
            margin-bottom: 1.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
        }

        .institution-logo {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, 0.25);
            padding: 0.5rem;
        }

        .institution-logo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .institution-logo i {
            font-size: 1.75rem;
            color: rgba(255, 255, 255, 0.9);
        }

        .institution-name {
            font-size: 1.125rem;
            font-weight: 600;
            color: white;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .support-info {
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.7);
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.15);
        }

        .support-info a {
            color: var(--accent-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .support-info a:hover {
            color: color-mix(in srgb, var(--accent-color) 80%, white);
            text-decoration: underline;
        }

        /* Responsive Design */
        @media (max-width: 480px) {
            .glass-card {
                padding: 1.75rem 1.5rem;
            }

            .error-title {
                font-size: 1.5rem;
            }

            .error-icon-wrapper {
                width: 80px;
                height: 80px;
            }

            .error-icon {
                font-size: 2.5rem;
            }

            .btn {
                padding: 0.75rem 1.5rem;
                font-size: 0.9375rem;
            }
        }
    </style>
</head>

<body>
    <div class="error-container">
        <div class="glass-card">
            <!-- Institution Branding -->
            <div class="institution-branding">
                <div class="institution-logo">
                    @if(config('branding.logo.auth') || config('branding.logo.primary'))
                        <img src="{{ asset(config('branding.logo.auth', config('branding.logo.primary'))) }}" alt="Logo">
                    @else
                        <i class="fas fa-graduation-cap"></i>
                    @endif
                </div>
                @if(config('branding.theme_settings.show_institution_name', true))
                    <div class="institution-name">{{ config('branding.institution.name', config('app.name')) }}</div>
                @endif
            </div>

            <!-- Error Icon -->
            <div class="error-icon-wrapper">
                <i class="fas fa-exclamation-triangle error-icon"></i>
            </div>

            <!-- Error Information -->
            <div class="error-code">Error 500</div>
            <h1 class="error-title">Something Went Wrong</h1>
            <p class="error-message">
                We're sorry, but something unexpected happened on our server. 
                Our technical team has been notified and is working to fix the issue.
            </p>

            <!-- Additional Details -->
            <div class="error-details">
                <p>
                    <i class="fas fa-tools"></i>
                    Please try again in a few moments or contact support if the problem persists.
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <button onclick="window.location.reload()" class="btn btn-primary">
                    <i class="fas fa-sync-alt"></i>
                    Try Again
                </button>
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                        <i class="fas fa-home"></i>
                        Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-secondary">
                        <i class="fas fa-sign-in-alt"></i>
                        Return to Login
                    </a>
                @endauth
            </div>

            <!-- Support Information -->
            @if(config('branding.institution.support_email') || config('branding.institution.website_url'))
                <div class="support-info">
                    Need immediate help? 
                    @if(config('branding.institution.support_email'))
                        Contact us at <a href="mailto:{{ config('branding.institution.support_email') }}">{{ config('branding.institution.support_email') }}</a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</body>
</html>
