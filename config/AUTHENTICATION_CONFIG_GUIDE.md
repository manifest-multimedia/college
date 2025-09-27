# Authentication Configuration Guide

## Overview

The `config/authentication.php` file provides centralized configuration for the dual authentication system, supporting both AuthCentral (external) and Regular (Laravel) authentication methods.

## Configuration Sections

### 1. Authentication Method Selection

```php
'method' => env('AUTH_METHOD', 'authcentral'),
```

**Environment Variable:** `AUTH_METHOD`  
**Options:** `authcentral` | `regular`  
**Default:** `authcentral`

Controls which authentication system is active:
- `authcentral`: External AuthCentral service authentication
- `regular`: Standard Laravel email/password authentication

### 2. AuthCentral Configuration

```php
'authcentral' => [
    'login_url' => env('AUTHCENTRAL_LOGIN_URL', 'https://auth.pnmtc.edu.gh/login'),
    'api_url' => env('AUTHCENTRAL_API_URL', 'https://auth.pnmtc.edu.gh/api/user'),
    'signup_url' => env('AUTHCENTRAL_SIGNUP_URL', 'https://auth.pnmtc.edu.gh/sign-up'),
    'student_registration_url' => env('AUTHCENTRAL_STUDENT_REGISTRATION_URL', 'https://auth.pnmtc.edu.gh/student/register'),
],
```

**Used when:** `AUTH_METHOD=authcentral`

- **login_url**: AuthCentral login page URL
- **api_url**: AuthCentral user data API endpoint
- **signup_url**: AuthCentral staff registration URL
- **student_registration_url**: AuthCentral student registration URL

### 3. Regular Authentication Configuration

```php
'regular' => [
    'default_role' => env('AUTH_REGULAR_DEFAULT_ROLE', 'Staff'),
    'allow_registration' => env('AUTH_ALLOW_REGISTRATION', false),
    'allow_staff_registration' => env('AUTH_ALLOW_STAFF_REGISTRATION', true),
    'allow_student_registration' => env('AUTH_ALLOW_STUDENT_REGISTRATION', true),
    'student_default_status' => env('AUTH_STUDENT_DEFAULT_STATUS', 'active'),
],
```

**Used when:** `AUTH_METHOD=regular`

- **default_role**: Default role assigned to new regular users (deprecated, use roles section)
- **allow_registration**: Master toggle for registration (deprecated)
- **allow_staff_registration**: Enable/disable staff registration via `/staff/register`
- **allow_student_registration**: Enable/disable student registration via `/students/register`
- **student_default_status**: Default status for new student records

### 4. Registration Flow Configuration

```php
'registration' => [
    'redirect_after_staff_registration' => env('AUTH_STAFF_REGISTRATION_REDIRECT', '/dashboard'),
    'redirect_after_student_registration' => env('AUTH_STUDENT_REGISTRATION_REDIRECT', '/student-dashboard'),
    'require_email_verification' => env('AUTH_REQUIRE_EMAIL_VERIFICATION', false),
    'auto_login_after_registration' => env('AUTH_AUTO_LOGIN_AFTER_REGISTRATION', true),
],
```

- **redirect_after_staff_registration**: Where to redirect staff after successful registration
- **redirect_after_student_registration**: Where to redirect students after successful registration
- **require_email_verification**: Whether to require email verification before login
- **auto_login_after_registration**: Whether to automatically log in users after registration

### 5. Security Configuration

```php
'security' => [
    'prevent_authcentral_password_login' => env('AUTH_PREVENT_AUTHCENTRAL_PASSWORD_LOGIN', true),
    'prevent_authcentral_password_reset' => env('AUTH_PREVENT_AUTHCENTRAL_PASSWORD_RESET', true),
    'log_authentication_attempts' => env('AUTH_LOG_ATTEMPTS', true),
    'session_lifetime' => env('AUTH_SESSION_LIFETIME', 120),
],
```

- **prevent_authcentral_password_login**: Block AuthCentral users from email/password login
- **prevent_authcentral_password_reset**: Block AuthCentral users from password reset
- **log_authentication_attempts**: Enable authentication attempt logging
- **session_lifetime**: Session lifetime in minutes

### 6. Role Assignment Configuration

```php
'roles' => [
    'staff_default' => env('AUTH_STAFF_DEFAULT_ROLE', 'Staff'),
    'student_default' => env('AUTH_STUDENT_DEFAULT_ROLE', 'Student'),
    'authcentral_fallback' => env('AUTH_AUTHCENTRAL_FALLBACK_ROLE', 'Staff'),
],
```

- **staff_default**: Default role for staff registrations
- **student_default**: Default role for student registrations
- **authcentral_fallback**: Fallback role for AuthCentral users without roles

## Environment Variables Reference

### Core Configuration
```env
AUTH_METHOD=authcentral                     # Authentication method (authcentral|regular)
```

### AuthCentral URLs
```env
AUTHCENTRAL_LOGIN_URL=https://auth.pnmtc.edu.gh/login
AUTHCENTRAL_API_URL=https://auth.pnmtc.edu.gh/api/user
AUTHCENTRAL_SIGNUP_URL=https://auth.pnmtc.edu.gh/sign-up
AUTHCENTRAL_STUDENT_REGISTRATION_URL=https://auth.pnmtc.edu.gh/student/register
```

### Regular Authentication
```env
AUTH_REGULAR_DEFAULT_ROLE=Staff             # Deprecated - use AUTH_STAFF_DEFAULT_ROLE
AUTH_ALLOW_REGISTRATION=false               # Deprecated - use specific registration flags
AUTH_ALLOW_STAFF_REGISTRATION=true          # Enable staff registration
AUTH_ALLOW_STUDENT_REGISTRATION=true        # Enable student registration
AUTH_STUDENT_DEFAULT_STATUS=active          # Default status for student records
```

### Registration Flow
```env
AUTH_STAFF_REGISTRATION_REDIRECT=/dashboard
AUTH_STUDENT_REGISTRATION_REDIRECT=/student-dashboard
AUTH_REQUIRE_EMAIL_VERIFICATION=false
AUTH_AUTO_LOGIN_AFTER_REGISTRATION=true
```

### Security Settings
```env
AUTH_PREVENT_AUTHCENTRAL_PASSWORD_LOGIN=true
AUTH_PREVENT_AUTHCENTRAL_PASSWORD_RESET=true
AUTH_LOG_ATTEMPTS=true
AUTH_SESSION_LIFETIME=120
```

### Role Assignment
```env
AUTH_STAFF_DEFAULT_ROLE=Staff
AUTH_STUDENT_DEFAULT_ROLE=Student
AUTH_AUTHCENTRAL_FALLBACK_ROLE=Staff
```

## Usage in Code

### AuthenticationService Methods

```php
// Get configuration sections
$authService->getAuthCentralConfig()    // AuthCentral settings
$authService->getRegularConfig()        // Regular auth settings
$authService->getRegistrationConfig()   // Registration flow settings
$authService->getSecurityConfig()       // Security settings
$authService->getRoleConfig()          // Role assignment settings

// Method detection
$authService->isAuthCentral()          // Check if using AuthCentral
$authService->isRegular()              // Check if using regular auth

// URL generation
$authService->getStaffSignupUrl()      // Get staff registration URL
$authService->getStudentSignupUrl()    // Get student registration URL
```

### Configuration Access

```php
// Direct config access
config('authentication.method')                    // Current auth method
config('authentication.regular.allow_staff_registration')  // Staff registration enabled
config('authentication.roles.student_default')     // Default student role
config('authentication.security.log_authentication_attempts')  // Logging enabled
```

## Migration Guide

### From Basic to Enhanced Configuration

1. **Update Environment Variables**
   ```env
   # Old (deprecated)
   AUTH_ALLOW_REGISTRATION=true
   AUTH_REGULAR_DEFAULT_ROLE=Staff
   
   # New (recommended)
   AUTH_ALLOW_STAFF_REGISTRATION=true
   AUTH_ALLOW_STUDENT_REGISTRATION=true
   AUTH_STAFF_DEFAULT_ROLE=Staff
   AUTH_STUDENT_DEFAULT_ROLE=Student
   ```

2. **Role Configuration**
   - Replace direct role strings with configuration references
   - Use role mapping for consistent role assignment
   - Update custom controllers to use AuthenticationService methods

3. **Security Settings**
   - Review and set security flags based on requirements
   - Enable logging for audit trails
   - Configure appropriate session lifetimes

## Best Practices

1. **Environment-Specific Settings**
   - Use different settings for development/staging/production
   - Test both authentication methods in staging
   - Document any custom configuration changes

2. **Security Considerations**
   - Always enable AuthCentral password login prevention
   - Enable authentication logging for security auditing
   - Use appropriate session lifetimes for your environment

3. **Role Management**
   - Ensure all referenced roles exist in the database
   - Use consistent role names across the application
   - Test role assignment for both authentication methods

4. **URL Configuration**
   - Use HTTPS URLs for production AuthCentral endpoints
   - Test all AuthCentral URLs before deployment
   - Ensure callback URLs are properly configured

## Troubleshooting

### Common Issues

1. **Registration Disabled**
   - Check `AUTH_ALLOW_STAFF_REGISTRATION` / `AUTH_ALLOW_STUDENT_REGISTRATION`
   - Verify authentication method is set correctly

2. **Role Assignment Problems**
   - Ensure role names exist in Spatie permissions tables
   - Check role configuration in `authentication.roles` section
   - Verify AuthenticationService is using updated role methods

3. **AuthCentral URL Issues**
   - Verify all AuthCentral URLs are accessible
   - Check callback URL configuration
   - Ensure API endpoints return expected data format

4. **Configuration Not Loading**
   - Clear config cache: `php artisan config:clear`
   - Verify .env file syntax
   - Check for typos in environment variable names