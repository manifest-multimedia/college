# Dual Authentication System with Role-Based Registration

## Overview

This system provides flexible authentication supporting both AuthCentral (external) and regular Laravel authentication, with role-based registration routes that distinguish between staff and students.

## Authentication Methods

### AuthCentral Authentication
- **Environment Variable**: `AUTH_METHOD=authcentral`
- **Staff Registration**: External AuthCentral signup URL
- **Student Registration**: External AuthCentral student registration URL
- **Login**: Redirects to AuthCentral login page

### Regular Authentication
- **Environment Variable**: `AUTH_METHOD=regular`
- **Staff Registration**: `/staff/register` - Creates users with "Staff" role
- **Student Registration**: `/students/register` - Creates users with "Student" role + student record
- **Login**: Standard email/password authentication

## Registration Routes

### Staff Registration
- **Route**: `GET/POST /staff/register`
- **Route Name**: `staff.register`
- **Controller**: `RegularAuthController@showStaffRegistrationForm` / `registerStaff`
- **Available**: Only when `AUTH_METHOD=regular` and `AUTH_ALLOW_REGISTRATION=true`
- **User Role**: "Staff" (or configured default role)

### Student Registration
- **Route**: `GET/POST /students/register`
- **Route Name**: `students.register`
- **Controller**: `RegularAuthController@showStudentRegistrationForm` / `registerStudent`
- **Available**: For both AuthCentral and Regular authentication methods
- **User Role**: "Student"
- **Additional**: Creates linked record in `students` table

### Legacy Routes
- **Route**: `GET /register` → Redirects to `/staff/register`
- **Route**: `GET /sign-up` → Context-aware redirect:
  - AuthCentral: External signup URL
  - Regular: `/staff/register`

## Configuration

### Environment Variables
```env
# Authentication method selection
AUTH_METHOD=regular  # or 'authcentral'

# Regular authentication settings
AUTH_ALLOW_REGISTRATION=true
AUTH_REGULAR_DEFAULT_ROLE=Staff

# AuthCentral URLs (when AUTH_METHOD=authcentral)
AUTHCENTRAL_LOGIN_URL=https://auth.example.edu/login
AUTHCENTRAL_API_URL=https://auth.example.edu/api/user
AUTHCENTRAL_SIGNUP_URL=https://auth.example.edu/sign-up
AUTHCENTRAL_STUDENT_REGISTRATION_URL=https://auth.example.edu/student/register
```

### Configuration File
- **Location**: `config/authentication.php`
- **Purpose**: Centralized authentication settings
- **Benefits**: Easy switching between authentication methods

## User Registration Flow

### Staff Registration (Regular Auth)
1. User visits `/staff/register`
2. Fills registration form (name, email, password)
3. System creates user with "Staff" role
4. User is automatically logged in
5. Redirected to main dashboard

### Student Registration (Any Auth Method)
1. User visits `/students/register`
2. Fills registration form (name, email, password)
3. System creates:
   - User account with "Student" role
   - Linked record in `students` table
4. User is automatically logged in
5. Redirected to student dashboard

## Login Experience

### Login Page Features
- **Dynamic Authentication**: Automatically detects auth method
- **Registration Links**: Shows both "Staff Registration" and "Student Registration"
- **AuthCentral Integration**: Single-click external authentication
- **Regular Auth**: Standard email/password form with "Forgot Password" link

### Role-Based Redirects
- **Students**: Redirect to `route('student.dashboard')`
- **Staff/Admin**: Redirect to `route('dashboard')`

## Security Features

### AuthCentral User Protection
- AuthCentral users cannot login with email/password
- System detects AuthCentral users and blocks regular login attempts
- Clear error messages guide users to correct authentication method

### Password Reset Protection
- Only regular authentication users can reset passwords
- AuthCentral users are blocked from password reset with helpful messages

### Role Assignment
- **Automatic**: Users get appropriate roles based on registration type
- **Fallback**: Default roles assigned if no specific role provided
- **Audit Trail**: All role assignments logged

## Database Integration

### Users Table
- Standard Laravel users table
- Used for authentication and role management
- Links to students table via foreign key

### Students Table
- Detailed student information
- Linked to users table via `user_id` foreign key
- Auto-created for student registrations

## Custom Views Directory

### Location
- **Path**: `resources/views/custom-auth/`
- **Purpose**: Framework update protection
- **Configuration**: Registered in `FortifyServiceProvider`

### Available Views
- `login.blade.php` - Dual authentication login
- `register.blade.php` - Role-aware registration form
- `forgot-password.blade.php` - Password reset request
- `reset-password.blade.php` - Password reset form
- `confirm-password.blade.php` - Password confirmation
- `verify-email.blade.php` - Email verification
- `two-factor-challenge.blade.php` - 2FA authentication

## API Endpoints

### Authentication Service Methods
- `getAuthMethod()` - Current authentication method
- `isAuthCentral()` / `isRegular()` - Method detection
- `getStaffSignupUrl()` - Staff registration URL
- `getStudentSignupUrl()` - Student registration URL
- `createStaffUser()` / `createStudentUser()` - User creation

## Testing

### Route Testing
```bash
# List all registration routes
php artisan route:list --name=register

# List all authentication routes
php artisan route:list | grep -E "(login|register|password)"
```

### Manual Testing Scenarios
1. **Staff Registration** (Regular Auth)
   - Set `AUTH_METHOD=regular` and `AUTH_ALLOW_REGISTRATION=true`
   - Visit `/staff/register`
   - Complete registration form
   - Verify "Staff" role assigned

2. **Student Registration** (Any Auth Method)
   - Visit `/students/register`
   - Complete registration form
   - Verify "Student" role assigned
   - Verify student record created

3. **AuthCentral Integration**
   - Set `AUTH_METHOD=authcentral`
   - Visit `/login`
   - Verify external redirect to AuthCentral

## Migration Path

### From Single Registration to Role-Based
- Legacy `/register` routes automatically redirect to staff registration
- Existing functionality preserved
- New student registration capabilities added
- Zero breaking changes for existing users

## Troubleshooting

### Common Issues
1. **Registration Disabled**: Check `AUTH_ALLOW_REGISTRATION` environment variable
2. **Wrong Roles**: Verify role names exist in database
3. **Student Records**: Ensure Student model exists and is configured
4. **View Errors**: Clear view cache with `php artisan view:clear`

### Debug Commands
```bash
# Clear all caches
php artisan optimize:clear

# Check current configuration
php artisan tinker
>>> config('authentication')

# Verify routes
php artisan route:list --name=register
```