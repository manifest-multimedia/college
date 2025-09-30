@php
    $authService = app(\App\Services\AuthenticationService::class);
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <title>{{ config('branding.institution.name', config('app.name')) }} - Reset Password</title>
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
            --accent-color: {{ config('branding.colors.accent', '#F59E0B') }};
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #0ea5e9 0%, #1e40af 25%, #3730a3 50%, #f59e0b 100%);
            background-attachment: fixed;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
            overflow-x: hidden;
            overflow-y: hidden;
        }

        /* Decorative background shapes */
        body::before {
            content: '';
            position: fixed;
            top: -20vh;
            left: -20vw;
            width: 60vw;
            height: 120vh;
            background: linear-gradient(45deg, #f59e0b 0%, #eab308 100%);
            border-radius: 0 100% 0 0;
            z-index: 1;
        }

        body::after {
            content: '';
            position: fixed;
            bottom: -10vh;
            right: -10vw;
            width: 40vw;
            height: 80vh;
            background: linear-gradient(225deg, #1e293b 0%, #0f172a 100%);
            border-radius: 100% 0 0 0;
            z-index: 1;
        }

        .login-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 1200px;
            min-height: 600px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* Left side - Institution branding */
        .branding-side {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            padding: 2rem;
            color: white;
            min-height: 600px;
        }

        .institution-logo {
            width: 120px;
            height: 120px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
            border: 2px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .institution-logo img {
            width: 80px;
            height: 80px;
            border-radius: 12px;
        }

        .institution-logo i {
            font-size: 3rem;
            color: white;
        }

        .institution-name {
            font-size: 3rem;
            font-weight: 700;
            color: white;
            margin-bottom: 0.5rem;
            line-height: 1.1;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .institution-subtitle {
            font-size: 1.5rem;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 400;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        /* Right side - Reset Password form */
        .login-side {
            flex: 0 0 500px;
            background: white;
            padding: 3rem;
            border-radius: 24px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin-left: 2rem;
            max-height: none;
            overflow: visible;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .login-subtitle {
            font-size: 1rem;
            color: #6b7280;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-control {
            width: 100%;
            padding: 1rem 1.25rem;
            background: #f8fafc;
            border: 2px solid #f1f5f9;
            border-radius: 14px;
            color: #1f2937;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .form-control::placeholder {
            color: #9ca3af;
        }

        .form-control:focus {
            outline: none;
            border-color: #6366f1;
            background: white;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .password-toggle {
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: #6366f1 !important;
        }

        .submit-btn {
            width: 100%;
            padding: 1rem 1.5rem;
            background: #6366f1;
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(99, 102, 241, 0.3);
            margin-bottom: 1.5rem;
        }

        .submit-btn:hover {
            background: #4f46e5;
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(99, 102, 241, 0.4);
        }

        .back-to-login {
            text-align: center;
        }

        .back-to-login-text {
            font-size: 0.95rem;
            color: #6b7280;
        }

        .back-to-login-link {
            color: #6366f1;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .back-to-login-link:hover {
            color: #4f46e5;
            text-decoration: underline;
        }

        /* Status Messages */
        .alert {
            margin-bottom: 1rem;
            padding: 0.875rem 1rem;
            border-radius: 12px;
            font-size: 0.9rem;
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
        @media (max-width: 1400px) {
            body {
                align-items: flex-start;
                overflow-y: auto;
                padding: 1rem;
            }
            
            .login-container {
                align-items: flex-start;
                margin-top: 2rem;
                margin-bottom: 2rem;
            }
        }
        
        @media (max-width: 1024px) {
            body {
                align-items: flex-start;
                overflow-y: auto;
                padding: 1rem;
            }
            
            .login-container {
                flex-direction: column;
                gap: 2rem;
                align-items: center;
                margin-top: 1rem;
                margin-bottom: 1rem;
            }
            
            .login-side {
                flex: none;
                width: 100%;
                max-width: 500px;
                margin-left: 0;
            }
            
            .branding-side {
                text-align: center;
                padding: 2rem 1rem;
                align-items: center;
                min-height: auto;
            }
            
            .institution-name {
                font-size: 2.5rem;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 0.5rem;
                overflow-y: auto;
            }
            
            .login-side {
                padding: 2rem;
            }
            
            .institution-name {
                font-size: 2rem;
            }
            
            .institution-subtitle {
                font-size: 1.25rem;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 0.25rem;
                overflow-y: auto;
            }
            
            .login-container {
                margin-top: 0.5rem;
                margin-bottom: 0.5rem;
            }
            
            .login-side {
                padding: 1.5rem;
            }
            
            .institution-name {
                font-size: 1.75rem;
            }
            
            .login-title {
                font-size: 1.5rem;
            }
        }
    </style>
    
    <script>
        function togglePassword(inputName) {
            const passwordField = document.querySelector(`input[name="${inputName}"]`);
            const toggleIcon = document.querySelector(`.password-toggle[data-target="${inputName}"] i`);
            
            if (passwordField && toggleIcon) {
                if (passwordField.type === 'password') {
                    passwordField.type = 'text';
                    toggleIcon.className = 'fas fa-eye-slash';
                } else {
                    passwordField.type = 'password';
                    toggleIcon.className = 'fas fa-eye';
                }
            }
        }
    </script>
</head>

<body>
    <div class="login-container">
        <!-- Left Side - Institution Branding -->
        <div class="branding-side">
            <div class="institution-logo">
                @if(config('branding.logo.login'))
                    <img src="{{ asset(config('branding.logo.login')) }}" alt="Logo">
                @else
                    <i class="fas fa-graduation-cap"></i>
                @endif
            </div>
            <h1 class="institution-name">{{ config('branding.institution.name', config('app.name')) }}</h1>
            <p class="institution-subtitle">College Information System</p>
        </div>

        <!-- Right Side - Reset Password Form -->
        <div class="login-side">
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

            <div class="login-header">
                <h2 class="login-title">Reset Password</h2>
                <p class="login-subtitle">Enter your new password</p>
            </div>

            <form method="POST" action="{{ route('password.update') }}">
                @csrf

                <input type="hidden" name="token" value="{{ $request->route('token') }}">
                
                <div class="form-group">
                    <input type="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           name="email" 
                           value="{{ old('email', $request->email) }}" 
                           required 
                           autofocus 
                           autocomplete="username"
                           placeholder="Email Address">
                    @error('email')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-group">
                    <div style="position: relative;">
                        <input type="password" 
                               class="form-control @error('password') is-invalid @enderror" 
                               name="password" 
                               required 
                               autocomplete="new-password"
                               placeholder="New Password"
                               style="padding-right: 3rem;">
                        <button type="button" 
                                class="password-toggle" 
                                data-target="password"
                                onclick="togglePassword('password')"
                                style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: #9ca3af; cursor: pointer; font-size: 1rem;">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-group">
                    <div style="position: relative;">
                        <input type="password" 
                               class="form-control @error('password_confirmation') is-invalid @enderror" 
                               name="password_confirmation" 
                               required 
                               autocomplete="new-password"
                               placeholder="Confirm New Password"
                               style="padding-right: 3rem;">
                        <button type="button" 
                                class="password-toggle" 
                                data-target="password_confirmation"
                                onclick="togglePassword('password_confirmation')"
                                style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: #9ca3af; cursor: pointer; font-size: 1rem;">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('password_confirmation')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <button type="submit" class="submit-btn">
                    Reset Password
                </button>
            </form>

            <div class="back-to-login">
                <p class="back-to-login-text">
                    <span>Remember your password?</span>
                    <a class="back-to-login-link" href="{{ route('login') }}">Back to login</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>