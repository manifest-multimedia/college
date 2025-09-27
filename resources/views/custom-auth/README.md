# Custom Authentication Views

This directory contains customized authentication views that use our `backend.auth` layout component. These views are configured in `app/Providers/FortifyServiceProvider.php` to override Laravel Fortify's default authentication views.

## Purpose

By placing our authentication views in this separate `custom-auth` directory and configuring Fortify to use them, we ensure that:

1. **Framework Updates Won't Overwrite Our Views**: When Laravel, Fortify, or Jetstream are updated, our custom authentication views will remain intact.
2. **Consistent UI/UX**: All authentication views use our custom `backend.auth` layout for a unified look and feel.
3. **Easy Maintenance**: All authentication views are centralized in one location.

## Views Included

- `login.blade.php` - Login form with AuthCentral/Regular authentication support
- `register.blade.php` - User registration form
- `forgot-password.blade.php` - Password reset request form
- `reset-password.blade.php` - Password reset form
- `confirm-password.blade.php` - Password confirmation for secure areas
- `verify-email.blade.php` - Email verification page
- `two-factor-challenge.blade.php` - Two-factor authentication form

## Configuration

These views are registered in `app/Providers/FortifyServiceProvider.php`:

```php
// Custom authentication views that won't be overwritten by Laravel updates
Fortify::loginView(function () {
    return view('custom-auth.login');
});

Fortify::registerView(function () {
    return view('custom-auth.register');
});

// ... other view registrations
```

## Layout Component

All views use the `<x-backend.auth>` component which provides:
- Consistent styling with Bootstrap classes
- Error message handling
- Status message display
- Responsive design
- Custom branding

## Authentication Service Integration

The login and registration views integrate with our `AuthenticationService` to support:
- AuthCentral external authentication
- Regular Laravel authentication
- Dynamic authentication method switching via environment variables

## Development Notes

- When modifying authentication flows, update views in this directory
- Test both AuthCentral and regular authentication modes
- Ensure consistent styling with the backend.auth layout
- Keep view logic minimal - business logic should be in controllers/services