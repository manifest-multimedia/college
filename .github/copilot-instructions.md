# College Management System - AI Agent Instructions

## Architecture Overview

This is a comprehensive college management system built on **Laravel 12** with **Livewire 3** components. The system manages the complete student lifecycle with integrated modules for academics, finance, examinations, course registration, elections, and communication.

### Core Modules & Data Flow

- **Student Management**: Central hub with relationships to all other modules via `students` table (uses `increments('id')`)
- **Finance Module**: Handles fee billing, payments, and clearances (60% payment threshold for course registration, 100% for final exams)
- **Course Registration**: Integrated with finance - requires minimum payment percentage before allowing registration
- **Exam System**: Online/offline exams with QR code clearance system for offline exams
- **Election System**: Digital voting platform with session management and result tracking

### Critical Integration Points

The system's power comes from cross-module integration:
- Finance → Course Registration (payment verification)
- Finance → Exam Clearance (payment-based exam access)
- Student → All modules (central relationship hub)
- Exam Clearance → QR Generation (for offline exam verification)

## Database Architecture Patterns

### Foreign Key Conventions
**CRITICAL**: The `students` table uses `$table->increments('id')` - all relationships must reference this correctly:

```php
// Correct foreign key setup for students table
$table->unsignedInteger('student_id');
$table->foreign('student_id')->references('id')->on('students');

// Other core tables also use increments('id')
$table->unsignedInteger('subject_id');
$table->foreign('subject_id')->references('id')->on('subjects');
```

### Key Models & Relationships
- `Student`: Core model with relationships to `CourseRegistration`, `FeePayment`, `ExamClearance`, `StudentFeeBill`
- `CourseRegistration`: Links students to subjects with payment percentage tracking
- `ExamClearance`: Manages exam access based on payment status and manual overrides
- `StudentFeeBill` + `FeePayment`: Finance module's payment tracking system

## Development Workflows

### Laravel 12 Specific Patterns
- **No Kernel**: Use `bootstrap/app.php` for middleware registration, not `app/Http/Kernel.php`
- **Auto-registered Commands**: Files in `app/Console/Commands/` are automatically available
- **Livewire Integration**: No need for `@livewireScripts` and `@livewireStyles` - built-in
- **Configuration**: Use `bootstrap/providers.php` for service providers

### Component Architecture
**Livewire Components Location**: `app/Livewire/[Module]/` 
- Finance: `app/Livewire/Finance/CourseRegistrationManager.php`
- Exam: `app/Livewire/ExamManagement.php`
- Election: `app/Livewire/ElectionManager.php`

### View Layouts
**Standard Layout**: Use `<x-dashboard.default title="Page Title">` for all dashboard views
```blade
<x-dashboard.default title="Course Registration">
    <livewire:finance.course-registration-manager :studentId="$studentId" />
</x-dashboard.default>
```

## Business Logic Patterns

### Finance Module Integration
```php
// Payment threshold constants used throughout
const PAYMENT_THRESHOLD = 60;  // Course registration
const EXAM_PAYMENT_THRESHOLD = 100;  // Final exams (mid-semester exams don't require full payment)
```

### Exam Clearance Workflow
1. Check payment percentage via `StudentFeeBill` and `FeePayment` models
2. Generate `ExamClearance` record if eligible
3. Create QR ticket with `ExamEntryTicket` for offline exams
4. Scanner validates QR codes via API endpoint

### Course Registration Business Rules
- Students need ≥60% payment to register for courses
- Registration tracked in `course_registrations` table with `payment_percentage_at_registration`
- Integration with academic year and semester filtering

## Navigation & UI Patterns

### Sidebar Navigation
Update `resources/views/components/app/sidebar.blade.php` when adding new features. Role-based navigation using `@hasrole('Student')` directives.

### Form Patterns
- Use Bootstrap classes with consistent card structure
- Card headers use `card-title` class for consistency
- Flash messages use `alert alert-success` / `alert alert-danger` classes

## Testing & Quality Assurance

### Database Testing
- Use **Laravel Tinker** for database structure inspection (not `php artisan db:show`)
- Check relationships by reviewing migrations and model files
- Use Laravel Boost MCP tools for testing in development

### Migration Safety
- ✅ **Allowed**: Modify migrations for unreleased features
- ❌ **Never**: Alter production migrations - create new ones instead
- Use `php artisan migrate:rollback` during development (not `migrate:fresh`)

## Development Commands

```bash
# Laravel Boost MCP tools available for enhanced development
# Use tinker for database inspection
# Run tests with: php artisan test
# Build assets: npm run build, npm run dev, or composer run dev
# Code formatting: vendor/bin/pint --dirty
```

## Project Documentation

- **Feature Requirements**: `exam-prd.md` and `prd.md` contain detailed business requirements
- **API Documentation**: Located in `resources/docs/` with LaRecipe integration
- **Module Documentation**: Each major module has comprehensive docs in `resources/docs/1.0/[module]/`

## Key Configuration Files

- `bootstrap/app.php`: Middleware and service provider registration (replaces Kernel in Laravel 12)
- `routes/web.php`: Contains grouped routes by module (finance, exam, election, etc.)
- Custom config files: `config/communication.php`, `config/school.php`, `config/permission.php`