@php
    $authService = app(\App\Services\AuthenticationService::class);
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <title>{{ config('branding.institution.name', config('app.name')) }} - Login</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('css/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset(config('branding.logo.favicon', '/favicon.ico')) }}">

    <style>
        :root {
            --primary-color: {{ config('branding.colors.primary', '#3B82F6') }};
            --secondary-color: {{ config('branding.colors.secondary', '#64748B') }};
            --accent-color: {{ config('branding.colors.accent', '#10B981') }};
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
            background: radial-gradient(circle, rgba(59, 130, 246, 0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
            z-index: 1;
        }

        body::after {
            content: '';
            position: absolute;
            top: 10%;
            right: 10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.08) 0%, transparent 70%);
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

        .auth-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 380px;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 1.75rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.15);
            animation: slideUp 0.6s ease-out;
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

        .institution-header {
            text-align: center;
            margin-bottom: 1.25rem;
        }

        .institution-logo {
            width: 55px;
            height: 55px;
            margin: 0 auto 0.5rem;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: white;
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3);
        }

        .institution-name {
            font-size: 1.375rem;
            font-weight: 700;
            color: white;
            margin-bottom: 0.125rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .institution-tagline {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 400;
        }

        .welcome-section {
            text-align: center;
            margin-bottom: 1.25rem;
        }

        .welcome-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: white;
            margin-bottom: 0.125rem;
        }

        .welcome-subtitle {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .sso-section {
            margin-bottom: 1rem;
        }

        .sso-btn {
            width: 100%;
            padding: 0.65rem 1rem;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(59, 130, 246, 0.25);
        }

        .sso-btn:hover {
            background: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(59, 130, 246, 0.4);
            color: white;
            text-decoration: none;
        }

        .sso-description {
            text-align: center;
            font-size: 0.7rem;
            margin-top: 0.375rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .divider {
            text-align: center;
            margin: 1rem 0;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: rgba(255, 255, 255, 0.3);
        }

        .divider span {
            background: rgba(255, 255, 255, 0.2);
            padding: 0 0.75rem;
            font-size: 0.7rem;
            color: rgba(255, 255, 255, 0.8);
            position: relative;
            backdrop-filter: blur(10px);
        }

        .form-group {
            margin-bottom: 0.875rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.375rem;
            font-size: 0.8rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.9);
        }

        .form-control {
            width: 100%;
            padding: 0.625rem 0.75rem;
            background: rgba(255, 255, 255, 0.15);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            color: white;
            font-size: 0.8rem;
            transition: all 0.3s ease;
            font-family: inherit;
            backdrop-filter: blur(10px);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .form-control:focus {
            outline: none;
            border-color: rgba(255, 255, 255, 0.4);
            background: rgba(255, 255, 255, 0.2);
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.1);
        }

        .form-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        .form-check-input {
            margin: 0;
            accent-color: var(--primary-color);
        }

        .form-check-label {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.8);
            margin: 0;
        }

        .forgot-link {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .forgot-link:hover {
            color: white;
            text-decoration: underline;
        }

        .submit-btn {
            width: 100%;
            padding: 0.65rem 1rem;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            backdrop-filter: blur(10px);
        }

        .submit-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .registration-section {
            text-align: center;
            margin-top: 1rem;
        }

        .registration-text {
            font-size: 0.8rem;
            margin-bottom: 0.375rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .registration-links {
            display: flex;
            justify-content: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .registration-link {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .registration-link:hover {
            color: white;
            text-decoration: underline;
        }

        .support-section {
            text-align: center;
            margin-top: 0.875rem;
        }

        .support-link {
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            font-size: 0.7rem;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            transition: color 0.3s ease;
        }

        .support-link:hover {
            color: rgba(255, 255, 255, 0.8);
        }

        /* Status Messages */
        .alert {
            margin-bottom: 0.875rem;
            padding: 0.625rem 0.875rem;
            border-radius: 8px;
            font-size: 0.8rem;
        }

        .alert-success {
            background: #d1fae5;
            border: 1px solid #10b981;
            color: #065f46;
        }

        .alert-danger {
            background: #fee2e2;
            border: 1px solid #ef4444;
            color: #991b1b;
        }

        /* Responsive Design */
        @media (max-width: 480px) {
            .glass-card {
                padding: 1.5rem 1.25rem;
                margin: 0.5rem;
            }
            
            .institution-name {
                font-size: 1.25rem;
            }
            
            .welcome-title {
                font-size: 1rem;
            }
            
            .registration-links {
                flex-direction: column;
                gap: 0.375rem;
            }
        }
    </style>
</head>

<body>
    <div class="auth-container">
        @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="glass-card">
            <!-- Institution Branding -->
            <div class="institution-header">
                <div class="institution-logo">
                    @if(config('branding.logo.login'))
                        <img src="{{ asset(config('branding.logo.login')) }}" alt="Logo" style="width: 35px; height: 35px; border-radius: 50%;">
                    @else
                        <i class="fas fa-graduation-cap"></i>
                    @endif
                </div>
                <h1 class="institution-name">{{ config('branding.institution.name', config('app.name')) }}</h1>
                <p class="institution-tagline">Modern Academic Management</p>
            </div>

            <!-- Welcome Section -->
            <div class="welcome-section">
                <h2 class="welcome-title">Welcome Back</h2>
                <p class="welcome-subtitle">Sign in to continue your journey</p>
            </div>

            {{-- AuthCentral SSO Login Option --}}
            @if($authService->getAuthCentralLoginUrl())
                <div class="sso-section">
                    <a href="{{ $authService->getAuthCentralLoginUrl() }}" class="sso-btn">
                        <i class="fas fa-sign-in-alt"></i>
                        Continue with AuthCentral SSO
                    </a>
                    <p class="sso-description">Single Sign-On - Quick & Secure</p>
                </div>

                {{-- Divider --}}
                <div class="divider">
                    <span>OR</span>
                </div>
            @endif

            {{-- Email/Password Login Form --}}
            <form method="POST" action="{{ route('regular.login') }}">
                @csrf
                
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           id="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           required 
                           autofocus 
                           autocomplete="username"
                           placeholder="Enter your email address">
                    @error('email')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           id="password" 
                           name="password" 
                           required 
                           autocomplete="current-password"
                           placeholder="Enter your password">
                    @error('password')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label" for="remember">
                            Remember me
                        </label>
                    </div>
                    <a class="forgot-link" href="{{ route('password.request') }}">
                        Forgot password?
                    </a>
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-envelope"></i>
                    Sign In with Email
                </button>
            </form>

            {{-- Registration Links --}}
            <div class="registration-section">
                <p class="registration-text">Don't have an account?</p>
                <div class="registration-links">
                    @if ($authService->getStaffSignupUrl())
                        <a class="registration-link" href="{{ $authService->getStaffSignupUrl() }}">Staff Registration</a>
                    @endif
                    @if ($authService->getStudentSignupUrl())
                        <a class="registration-link" href="{{ $authService->getStudentSignupUrl() }}">Student Registration</a>
                    @endif
                </div>
            </div>

            {{-- Support Link --}}
            <div class="support-section">
                <a class="support-link" href="mailto:{{ config('branding.support.email', 'support@college.edu') }}">
                    <i class="fas fa-envelope"></i>
                    Support
                </a>
            </div>
        </div>
    </div>
</body>
</html>