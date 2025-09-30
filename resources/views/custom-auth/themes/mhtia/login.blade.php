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
            @foreach(config('branding.colors', []) as $name => $color)
            --brand-{{ $name }}: {{ $color }};
            @endforeach
        }

        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: 'Inter', sans-serif;
        }

        .mhtia-container {
            min-height: 100vh;
            display: flex;
        }

        .branding-side {
            flex: 1;
            background: linear-gradient(135deg, var(--brand-primary, #2563EB) 0%, var(--brand-secondary, #1e40af) 100%);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem;
        }

        .branding-side::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle at 25% 25%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                              radial-gradient(circle at 75% 75%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
        }

        .branding-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: white;
            max-width: 500px;
        }

        .institution-logo {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .institution-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            line-height: 1.2;
        }

        .institution-subtitle {
            font-size: 1.25rem;
            font-weight: 400;
            opacity: 0.9;
            margin-bottom: 2rem;
        }

        .features-list {
            text-align: left;
            max-width: 400px;
            margin: 0 auto;
        }

        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }

        .feature-icon {
            width: 24px;
            height: 24px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            flex-shrink: 0;
        }

        .login-side {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: #f8fafc;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-title {
            font-size: 2rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .login-subtitle {
            color: #64748b;
            font-size: 1rem;
        }

        .login-form {
            background: white;
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .form-control {
            padding: 0.75rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.2s ease;
            background: #f9fafb;
        }

        .form-control:focus {
            border-color: var(--brand-primary, #2563EB);
            background: white;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            outline: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--brand-primary, #2563EB), var(--brand-accent, #1d4ed8));
            border: none;
            padding: 0.875rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.4);
        }

        .btn-outline-primary {
            border: 2px solid var(--brand-primary, #2563EB);
            color: var(--brand-primary, #2563EB);
            background: transparent;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-outline-primary:hover {
            background: var(--brand-primary, #2563EB);
            color: white;
            transform: translateY(-1px);
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e5e7eb;
        }

        .divider span {
            padding: 0 1rem;
            color: #9ca3af;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .alert {
            border-radius: 8px;
            border: none;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background: #ecfdf5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .alert-danger {
            background: #fef2f2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        .footer-links {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
        }

        .footer-links a {
            color: #6b7280;
            text-decoration: none;
            font-size: 0.875rem;
            margin: 0 0.5rem;
            transition: color 0.2s ease;
        }

        .footer-links a:hover {
            color: var(--brand-primary, #2563EB);
        }

        @media (max-width: 768px) {
            .mhtia-container {
                flex-direction: column;
            }

            .branding-side {
                min-height: 40vh;
                padding: 2rem;
            }

            .institution-title {
                font-size: 2rem;
            }

            .features-list {
                display: none;
            }

            .login-form {
                padding: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="mhtia-container">
        <!-- Branding Side -->
        <div class="branding-side">
            <div class="branding-content">
                <div class="institution-logo">
                    @php
                        $authLogo = config('branding.logo.auth') ?? config('branding.logo.primary');
                    @endphp
                    
                    @if($authLogo && $authLogo !== '/images/logos/default-logo.svg')
                        <img src="{{ asset($authLogo) }}" alt="Logo" style="width: 40px; height: 40px; object-fit: contain;">
                    @else
                        <i class="fas fa-graduation-cap" style="font-size: 2rem; color: white;"></i>
                    @endif
                </div>
                
                <h1 class="institution-title">
                    {{ config('branding.institution.name', config('app.name')) }}
                </h1>
                
                <p class="institution-subtitle">
                    Professional Academic Management System
                </p>

                <div class="features-list">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt" style="font-size: 0.75rem;"></i>
                        </div>
                        <span>Secure Authentication & Access Control</span>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line" style="font-size: 0.75rem;"></i>
                        </div>
                        <span>Comprehensive Academic Analytics</span>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-users" style="font-size: 0.75rem;"></i>
                        </div>
                        <span>Integrated Student & Staff Management</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Login Side -->
        <div class="login-side">
            <div class="login-container">
                <div class="login-header">
                    <h2 class="login-title">Welcome Back</h2>
                    <p class="login-subtitle">Sign in to continue to your dashboard</p>
                </div>

                <div class="login-form">
                    <!-- Status Messages -->
                    @if (session('status'))
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>{{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <!-- AuthCentral SSO Login -->
                    <div class="form-group">
                        <a href="{{ $authService->getAuthCentralLoginUrl() }}" 
                           class="btn btn-primary w-100 d-flex align-items-center justify-content-center">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            Continue with AuthCentral SSO
                        </a>
                        <p class="text-center text-muted mt-2 mb-0" style="font-size: 0.8rem;">
                            Single Sign-On - Quick & Secure Access
                        </p>
                    </div>

                    <!-- Divider -->
                    <div class="divider">
                        <span>OR</span>
                    </div>

                    <!-- Email/Password Form -->
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
                                   placeholder="Enter your email address">
                        </div>

                        <div class="form-group">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   required 
                                   placeholder="Enter your password">
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                <label class="form-check-label" for="remember" style="font-size: 0.9rem; color: #6b7280;">
                                    Remember me
                                </label>
                            </div>
                            <a href="{{ route('password.request') }}" 
                               style="color: var(--brand-primary, #2563EB); font-size: 0.9rem; text-decoration: none;">
                                Forgot password?
                            </a>
                        </div>

                        <button type="submit" class="btn btn-outline-primary w-100 mb-3">
                            <i class="fas fa-envelope me-2"></i>
                            Sign In with Email
                        </button>
                    </form>

                    <!-- Registration Links -->
                    <div class="text-center">
                        <p style="color: #6b7280; font-size: 0.9rem; margin-bottom: 0.5rem;">
                            Don't have an account?
                        </p>
                        <div class="d-flex justify-content-center gap-3">
                            @if ($authService->getStaffSignupUrl())
                                <a href="{{ $authService->getStaffSignupUrl() }}" 
                                   style="color: var(--brand-primary, #2563EB); font-size: 0.875rem; text-decoration: none;">
                                    Staff Registration
                                </a>
                            @endif
                            @if ($authService->getStudentSignupUrl())
                                <a href="{{ $authService->getStudentSignupUrl() }}" 
                                   style="color: var(--brand-primary, #2563EB); font-size: 0.875rem; text-decoration: none;">
                                    Student Registration
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Footer Links -->
                @if(config('branding.institution.website_url') || config('branding.institution.support_email'))
                    <div class="footer-links">
                        @if(config('branding.institution.website_url'))
                            <a href="{{ config('branding.institution.website_url') }}" target="_blank">
                                <i class="fas fa-globe me-1"></i>Visit Website
                            </a>
                        @endif
                        @if(config('branding.institution.support_email'))
                            <a href="mailto:{{ config('branding.institution.support_email') }}">
                                <i class="fas fa-envelope me-1"></i>Need Help?
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>