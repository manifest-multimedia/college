<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ $title ?? 'College Elections' }} - Presbyterian Nursing and Midwifery Training College</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('images/app-logo.png') }}" />
    
    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @stack('styles')
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #3490dc;
            --secondary-color: #6c757d;
            --accent-color: #f39c12;
            --header-color: #130061;
            --danger-color: #e3342f;
            --success-color: #38c172;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            color: #333;
            padding-top: 4.5rem;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .navbar {
            background-color: var(--header-color);
        }
        
        .navbar-brand img {
            height: 40px;
        }
        
        .content-wrapper {
            flex: 1;
            padding: 2rem 0;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #2779bd;
            border-color: #2779bd;
        }
        
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.25rem 1.5rem;
        }
        
        .card-title {
            margin-bottom: 0;
            font-weight: 600;
            color: var(--header-color);
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        footer {
            background-color: #fff;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1rem 0;
        }
        
        /* Badge customization */
        .badge-light-primary {
            background-color: rgba(52, 144, 220, 0.1);
            color: var(--primary-color);
        }
        
        .badge-light-warning {
            background-color: rgba(243, 156, 18, 0.1);
            color: var(--accent-color);
        }
        
        .badge-light-danger {
            background-color: rgba(227, 52, 47, 0.1);
            color: var(--danger-color);
        }
        
        /* Alert customization */
        .alert-light-primary {
            background-color: rgba(52, 144, 220, 0.1);
            color: var(--primary-color);
            border-color: rgba(52, 144, 220, 0.2);
        }
        
        .alert-light-warning {
            background-color: rgba(243, 156, 18, 0.1);
            color: var(--accent-color);
            border-color: rgba(243, 156, 18, 0.2);
        }
        
        .alert-light-danger {
            background-color: rgba(227, 52, 47, 0.1);
            color: var(--danger-color);
            border-color: rgba(227, 52, 47, 0.2);
        }
        
        /* Animation for alerts */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .alert {
            animation: fadeIn 0.3s ease-in-out;
        }
        
        /* Audio player styling */
        audio {
            display: none;
        }
    </style>
   
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('public.elections.index') }}">
                <img src="{{ asset('images/app-logo.png') }}" alt="College Logo" class="me-2">
                <span>PNMTC Elections</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('public.elections.index') }}">
                            <i class="fas fa-home me-1"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">
                            <i class="fas fa-sign-in-alt me-1"></i> Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="content-wrapper">
        <div class="container">
            {{ $slot }}
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center">
        <div class="container">
            <p class="mb-0">&copy; {{ date('Y') }} Presbyterian Nursing and Midwifery Training College. All rights reserved.</p>
        </div>
    </footer>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    
    
    <script>
        // Generic function to play sound
        function playSound(soundId) {
            const audioElement = document.getElementById(soundId);
            if (audioElement) {
                audioElement.currentTime = 0;
                audioElement.play();
            }
        }
        
        // Listen for Livewire events
        document.addEventListener('livewire:initialized', () => {
            // Listen for verification failure events
            Livewire.on('verification-failed', () => {
                playSound('error-alert');
            });
            
            // Listen for vote success events
            Livewire.on('vote-submitted', () => {
                playSound('success-alert');
            });
        });
        
        // Add any additional JavaScript below
    </script>
    
    <!-- Sound elements -->
    <audio id="error-alert" src="{{ asset('sounds/error_alert.mp3') }}" preload="auto"></audio>
    <audio id="success-alert" src="{{ asset('sounds/success.mp3') }}" preload="auto"></audio>

    @stack('scripts')
    
    {{ $scripts ?? '' }}
</body>
</html>