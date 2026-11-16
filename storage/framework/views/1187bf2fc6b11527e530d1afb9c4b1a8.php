<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo e(config('branding.institution.name', config('app.name'))); ?> - Reset Password</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="<?php echo e(asset('css/bootstrap/bootstrap.min.css')); ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo e(asset(config('branding.logo.favicon', '/favicon.ico'))); ?>">

    <style>
        :root {
            --primary-color: <?php echo e(config('branding.colors.primary', '#3B82F6')); ?>;
            --secondary-color: <?php echo e(config('branding.colors.secondary', '#64748B')); ?>;
            --accent-color: <?php echo e(config('branding.colors.accent', '#10B981')); ?>;
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
            top: -30%;
            right: -30%;
            width: 150%;
            height: 150%;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.08) 0%, transparent 70%);
            animation: rotate 30s linear infinite;
            z-index: 1;
        }

        body::after {
            content: '';
            position: absolute;
            bottom: -20%;
            left: -20%;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            animation: pulse 5s ease-in-out infinite;
            z-index: 1;
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.6; }
            50% { transform: scale(1.15); opacity: 0.3; }
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
            font-size: 1.25rem;
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
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .welcome-subtitle {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 1rem;
        }

        .reset-description {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 0.875rem;
            margin-bottom: 1.25rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .reset-description p {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.5;
            margin: 0;
        }

        .form-group {
            margin-bottom: 1rem;
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
            padding: 0.75rem 1rem;
            background: rgba(255, 255, 255, 0.15);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            color: white;
            font-size: 0.85rem;
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

        .submit-btn {
            width: 100%;
            padding: 0.75rem 1rem;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            box-shadow: 0 4px 20px rgba(59, 130, 246, 0.25);
            margin-bottom: 1rem;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(59, 130, 246, 0.4);
        }

        .back-section {
            text-align: center;
        }

        .back-text {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 0.25rem;
        }

        .back-link {
            color: white;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
        }

        .back-link:hover {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: underline;
            transform: translateX(-2px);
        }

        /* Status Messages */
        .alert {
            margin-bottom: 0.875rem;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            font-size: 0.8rem;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.2);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #10b981;
            backdrop-filter: blur(10px);
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
            backdrop-filter: blur(10px);
        }

        .invalid-feedback {
            display: block;
            margin-top: 0.25rem;
            font-size: 0.75rem;
            color: #ef4444;
        }

        /* Responsive Design */
        @media (max-width: 480px) {
            .glass-card {
                padding: 1.5rem 1.25rem;
                margin: 0.5rem;
            }
            
            .institution-name {
                font-size: 1.125rem;
            }
            
            .welcome-title {
                font-size: 1rem;
            }
        }
    </style>
</head>

<body>
    <div class="auth-container">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
            <div class="alert alert-success" role="alert">
                <?php echo e(session('status')); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
            <div class="alert alert-danger" role="alert">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div><?php echo e($error); ?></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="glass-card">
            <!-- Institution Branding -->
            <div class="institution-header">
                <div class="institution-logo">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(config('branding.logo.auth') || config('branding.logo.primary')): ?>
                        <img src="<?php echo e(asset(config('branding.logo.auth', config('branding.logo.primary')))); ?>" alt="Logo" style="width: 35px; height: 35px; border-radius: 50%;">
                    <?php else: ?>
                        <i class="fas fa-graduation-cap"></i>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <h1 class="institution-name"><?php echo e(config('branding.institution.name', config('app.name'))); ?></h1>
                <p class="institution-tagline">Modern Academic Management</p>
            </div>

            <!-- Welcome Section -->
            <div class="welcome-section">
                <h2 class="welcome-title">
                    <i class="fas fa-key"></i>
                    Reset Password
                </h2>
                <p class="welcome-subtitle">Enter your email to receive a password reset link</p>
            </div>

            <!-- Reset Description -->
            <div class="reset-description">
                <p><?php echo e(__('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.')); ?></p>
            </div>

            
            <form method="POST" action="<?php echo e(route('password.email')); ?>">
                <?php echo csrf_field(); ?>
                
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" 
                           class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                           id="email" 
                           name="email" 
                           value="<?php echo e(old('email')); ?>" 
                           required 
                           autofocus 
                           autocomplete="username"
                           placeholder="Enter your email address">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback">
                            <?php echo e($message); ?>

                        </div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-envelope"></i>
                    Email Password Reset Link
                </button>
            </form>

            
            <div class="back-section">
                <p class="back-text">Remember your password?</p>
                <a class="back-link" href="<?php echo e(route('login')); ?>">
                    <i class="fas fa-arrow-left"></i>
                    Back to Login
                </a>
            </div>
        </div>
    </div>
</body>
</html><?php /**PATH /home/johnsonsebire/www/college/resources/views/custom-auth/themes/modern/forgot-password.blade.php ENDPATH**/ ?>