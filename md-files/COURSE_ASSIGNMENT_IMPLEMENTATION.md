# Course Assignment & Login Visibility Implementation

## Overview
This document details the implementation of a course assignment system for lecturers and the login form visibility toggle feature.

## Features Implemented

### 1. Course Assignment System
**Purpose**: Allow administrators to assign specific courses to lecturers, enabling lecturers to view and manage exam results only for courses they're assigned to.

**Database Schema**:
- Created `course_lecturer` pivot table with:
  - `user_id` (references lecturers)
  - `subject_id` (references courses)
  - Unique constraint on user_id + subject_id
  - Cascading deletes
  - Timestamps

**Models**:
- `CourseLecturer`: Pivot model with relationships to User and Subject
- `User`: Added `assignedCourses()` relationship and `isAssignedToCourse($courseId)` helper
- `Subject`: Added `assignedLecturers()` relationship

**UI Components**:
- **Location**: `/admin/course-assignments`
- **Component**: `app/Livewire/Admin/CourseAssignmentManager.php`
- **Features**:
  - Search lecturers by name or email
  - Pagination (10 lecturers per page)
  - Modal-based course assignment with checkboxes
  - Badge display showing assigned courses
  - Inline remove buttons for each course assignment
  - Role restriction: Super Admin, Administrator, System only

**Navigation**:
- Added sidebar menu item under Settings section
- Visible only to Super Admin, Administrator, and System roles

### 2. Lecturer Access Control Mode
**Purpose**: Allow institutions to choose between two access control modes for lecturers viewing exam results.

**Configuration**:
- **Location**: `/admin/branding` (Theme Settings section)
- **Setting**: `LECTURER_ACCESS_MODE` in .env
- **Options**:
  - `exam_creator` (default): Lecturers see only exams they created
  - `course_assignment`: Lecturers see only exams for courses assigned to them

**Implementation**:
- Updated `ExamResultsComponent` to check the access mode:
  - `render()`: Filters exams based on selected mode
  - `loadExamResults()`: Security check respects access mode
  - `exportToExcel()`: Export permission respects access mode
  - `exportToPDF()`: Export permission respects access mode
- Super Admin and Administrator roles bypass all filtering (see all exams)

### 3. Regular Login Form Visibility Toggle
**Purpose**: Allow institutions using only SSO to hide the email/password login form.

**Configuration**:
- **Location**: `/admin/branding` (Theme Settings section)
- **Setting**: `SHOW_REGULAR_LOGIN` in .env
- **Default**: `true` (form visible)

**Implementation**:
- Updated all 3 login theme files:
  - `resources/views/custom-auth/themes/default/login.blade.php`
  - `resources/views/custom-auth/themes/modern/login.blade.php`
  - `resources/views/custom-auth/themes/college-original/login.blade.php`
- Wrapped regular login form and "OR" divider with:
  ```blade
  @if(config('branding.theme_settings.show_regular_login', true))
      {{-- Regular login form --}}
  @endif
  ```

## Files Modified

### New Files Created
1. `database/migrations/2025_12_15_174503_create_course_lecturer_table.php`
2. `app/Models/CourseLecturer.php`
3. `app/Livewire/Admin/CourseAssignmentManager.php`
4. `resources/views/livewire/admin/course-assignment-manager.blade.php`
5. `resources/views/admin/course-assignments.blade.php`

### Files Modified
1. `app/Models/User.php` - Added assignedCourses() relationship
2. `app/Models/Subject.php` - Added assignedLecturers() relationship
3. `app/Http/Controllers/Admin/BrandingController.php` - Added new settings support
4. `config/branding.php` - Added lecturer_access_mode and show_regular_login
5. `resources/views/admin/branding/index.blade.php` - Added UI toggles
6. `app/Livewire/Admin/ExamResultsComponent.php` - Updated filtering and security
7. `resources/views/custom-auth/themes/default/login.blade.php` - Added conditional
8. `resources/views/custom-auth/themes/modern/login.blade.php` - Added conditional
9. `resources/views/custom-auth/themes/college-original/login.blade.php` - Added conditional
10. `resources/views/components/app/sidebar.blade.php` - Added Course Assignments link
11. `routes/web.php` - Added /admin/course-assignments route

## Usage Guide

### Assigning Courses to Lecturers
1. Navigate to **Settings → Course Assignments** (`/admin/course-assignments`)
2. Find the lecturer using the search bar (search by name or email)
3. Click **Assign Courses** button for the lecturer
4. In the modal, select courses by checking the checkboxes
5. Click **Save Assignments**
6. To remove a course, click the **×** button on the course badge

### Switching Access Control Modes
1. Navigate to **Settings → Branding** (`/admin/branding`)
2. Scroll to **Theme Settings** section
3. Find **Lecturer Access Control Mode** dropdown
4. Select your preferred mode:
   - **Exam Creator Mode**: Lecturers see exams they created
   - **Course Assignment Mode**: Lecturers see exams for assigned courses
5. Click **Update Theme Settings**

### Hiding Regular Login Form
1. Navigate to **Settings → Branding** (`/admin/branding`)
2. Scroll to **Theme Settings** section
3. Find **Show Regular Login Form** checkbox
4. Uncheck to hide the email/password form (for SSO-only institutions)
5. Check to show the form (default)
6. Click **Update Theme Settings**

## Business Logic

### Access Control Rules
- **Super Admin/Administrator**: See all exams regardless of mode
- **Lecturer (exam_creator mode)**: See only exams where `exam.user_id === lecturer.id`
- **Lecturer (course_assignment mode)**: See only exams where `lecturer.assignedCourses->contains(exam.subject_id)`
- **Students**: Unchanged - see their own results only

### Security Considerations
- Course assignments require Super Admin/Administrator/System role
- All exam result security checks respect the configured access mode
- Export functions (Excel/PDF) apply the same security rules
- Default settings maintain backward compatibility

### Data Integrity
- Cascading deletes ensure orphaned assignments are cleaned up
- Unique constraint prevents duplicate course assignments
- Foreign keys maintain referential integrity

## Testing Checklist

### Course Assignment System
- [ ] Admin can access `/admin/course-assignments`
- [ ] Lecturers cannot access course assignments page
- [ ] Search functionality filters lecturers correctly
- [ ] Modal opens with all courses listed
- [ ] Assigning courses updates the database
- [ ] Removing courses updates the database
- [ ] Badge display shows all assigned courses

### Access Control Modes
- [ ] Switch to course_assignment mode in branding settings
- [ ] Lecturer sees only exams for assigned courses
- [ ] Switch to exam_creator mode in branding settings
- [ ] Lecturer sees only exams they created
- [ ] Super Admin sees all exams in both modes
- [ ] Export functions respect the active mode

### Login Form Visibility
- [ ] Toggle show_regular_login to false in branding
- [ ] Visit login page - regular form is hidden
- [ ] Only AuthCentral button is visible (if enabled)
- [ ] Toggle show_regular_login to true
- [ ] Regular form is visible again
- [ ] Test across all 3 themes (default, modern, college-original)

## Notes

### Backward Compatibility
All new features default to maintaining existing behavior:
- `LECTURER_ACCESS_MODE=exam_creator` (original behavior)
- `SHOW_REGULAR_LOGIN=true` (form visible)

### Configuration Cache
After changing .env values in production, run:
```bash
php artisan config:clear
```

### Migration
The course_lecturer table was created with:
```bash
php artisan migrate
```

## Future Enhancements
- Bulk course assignment (assign multiple courses to multiple lecturers)
- Course assignment import from CSV
- Audit log for course assignment changes
- Notification to lecturers when courses are assigned/removed
- Dashboard widget showing lecturers with no course assignments
