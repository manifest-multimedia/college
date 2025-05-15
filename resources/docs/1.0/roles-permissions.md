# Roles and Permissions

---

- [Introduction](#introduction)
- [Key Concepts](#key-concepts)
- [Default Roles](#default-roles)
- [Permission Structure](#permission-structure)
- [Role Management](#role-management)
- [Permission Management](#permission-management)
- [Using Permissions in Code](#using-permissions)

<a name="introduction"></a>
## Introduction

The College Portal implements a comprehensive Role-Based Access Control (RBAC) system that manages user permissions throughout the application. This system ensures that users can only access features and data appropriate for their position and responsibilities within the institution.

The access control system is built on Laravel's authentication system and uses the Spatie Permission package (`spatie/laravel-permission`) to provide a flexible and powerful permission management solution.

<a name="key-concepts"></a>
## Key Concepts

### Roles

A role represents a user's position or function within the system. Each role contains a set of permissions that determine what actions the user can perform. Users can be assigned one or multiple roles.

### Permissions

Permissions are granular access rights that define specific actions a user can perform. Permissions are assigned to roles, and users inherit permissions from their assigned roles.

### Role Hierarchy

The system supports a hierarchical structure where some roles inherit permissions from other roles. For example, an Administrator role might inherit all permissions from other roles, plus have additional administrative permissions.

### Direct Permissions

In addition to role-based permissions, the system also supports assigning permissions directly to users when needed for special cases.

<a name="default-roles"></a>
## Default Roles

The College Portal comes with several pre-configured roles:

### Administrator

Full access to all system features and settings. Administrators can:
- Manage users, roles, and permissions
- Configure system settings
- Access all modules and features
- Override certain system constraints
- View audit logs and system reports

### Academic Officer

Manages academic-related functions:
- Manage courses and programs
- Handle student registration
- Process grade submissions
- Generate academic reports
- Manage academic calendars

### Finance Officer

Handles financial operations:
- Manage fee structures
- Process student payments
- Generate financial reports
- Configure payment settings
- Manage scholarships and financial aid

### Faculty

Teaching staff with academic responsibilities:
- Manage assigned courses
- Upload course materials
- Submit grades
- Track student attendance
- Communicate with students

### Student

Basic access for enrolled students:
- View personal academic records
- Register for courses
- Access learning materials
- View financial statements
- Participate in online activities

### Parent/Guardian

Limited access for monitoring student progress:
- View ward's academic performance
- Check attendance records
- View financial statements
- Communicate with faculty

<a name="permission-structure"></a>
## Permission Structure

Permissions in the College Portal follow a standardized naming convention:

```
module.resource.action
```

Where:
- `module` is the system module (e.g., `academic`, `finance`, `exam`)
- `resource` is the entity being acted upon (e.g., `course`, `payment`, `grade`)
- `action` is the operation being performed (e.g., `view`, `create`, `update`, `delete`)

### Examples

```
academic.course.view
finance.payment.create
exam.grade.update
user.role.manage
```

### Permission Categories

Permissions are organized into functional categories:

#### View Permissions
Allow users to read or view data:
- `*.*.view` - View a specific resource
- `*.*.list` - View a list of resources
- `*.*.export` - Export data

#### Edit Permissions
Allow users to modify data:
- `*.*.create` - Create new resources
- `*.*.update` - Modify existing resources
- `*.*.delete` - Remove resources

#### Management Permissions
Allow users to manage system configuration:
- `*.*.manage` - Full control over a resource
- `*.*.approve` - Approve actions or requests
- `*.*.reject` - Reject actions or requests

#### System Permissions
Control access to system-wide features:
- `system.settings.*` - System configuration
- `system.backup.*` - Database and file backups
- `system.logs.*` - System and audit logs

<a name="role-management"></a>
## Role Management

### Managing Roles via User Interface

Administrators can manage roles through the user interface:

1. Navigate to **Settings → Access Control → Roles**
2. Use the interface to:
   - View all defined roles
   - Create new roles
   - Edit existing roles
   - Assign permissions to roles
   - Delete roles (with appropriate safeguards)

### Role Creation Process

To create a new role:

1. Click "Add New Role" button
2. Enter role details:
   - Name: Unique identifier (e.g., `course-coordinator`)
   - Display Name: User-friendly name (e.g., "Course Coordinator")
   - Description: Explanation of the role's purpose
3. Select permissions to assign to the role
4. Save the role

### Role Assignment

To assign roles to users:

1. Navigate to **Settings → User Management → Users**
2. Select a user to edit
3. In the "Roles" tab, assign or remove roles
4. Save changes

Multiple roles can be assigned to a single user, and the user will have all permissions from all assigned roles.

<a name="permission-management"></a>
## Permission Management

### Viewing All Permissions

Administrators can view all available permissions:

1. Navigate to **Settings → Access Control → Permissions**
2. The interface displays all permissions organized by module
3. Use search and filter options to find specific permissions

### Creating Custom Permissions

While most permissions are created automatically during system installation or module activation, administrators can create custom permissions:

1. Navigate to **Settings → Access Control → Permissions**
2. Click "Add New Permission" button
3. Enter permission details:
   - Name: Using the `module.resource.action` convention
   - Display Name: User-friendly description
   - Description: Detailed explanation of the permission
4. Save the permission

### Permission Assignment

Permissions are typically assigned to roles rather than directly to users. However, for exceptional cases, direct permission assignment is available:

1. Navigate to **Settings → User Management → Users**
2. Select a user to edit
3. In the "Direct Permissions" tab, assign or remove specific permissions
4. Save changes

<a name="using-permissions"></a>
## Using Permissions in Code

### Middleware

The system provides middleware for route protection:

```php
Route::middleware(['permission:finance.payment.create'])->group(function () {
    Route::post('/payments', [PaymentController::class, 'store']);
});

Route::middleware(['role:administrator'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
});
```

### Blade Directives

In Blade templates, you can use the provided directives:

```php
@role('administrator')
    <a href="{{ route('admin.settings') }}">System Settings</a>
@endrole

@permission('academic.course.update')
    <button type="submit">Update Course</button>
@endpermission

@hasanyrole('administrator|academic-officer')
    <div class="admin-panel">
        <!-- Administrative content -->
    </div>
@endhasanyrole
```

### In Livewire Components

For Livewire components, use the provided traits and methods:

```php
namespace App\Livewire;

use Livewire\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CourseManagement extends Component
{
    use AuthorizesRequests;
    
    public function updateCourse()
    {
        $this->authorize('academic.course.update');
        
        // Course update logic
    }
    
    public function render()
    {
        return view('livewire.course-management')->with([
            'canCreateCourse' => auth()->user()->can('academic.course.create'),
            'canDeleteCourse' => auth()->user()->can('academic.course.delete'),
        ]);
    }
}
```

### In Controllers

For controller methods:

```php
namespace App\Http\Controllers;

class CourseController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:academic.course.view')->only(['index', 'show']);
        $this->middleware('permission:academic.course.create')->only(['create', 'store']);
        $this->middleware('permission:academic.course.update')->only(['edit', 'update']);
        $this->middleware('permission:academic.course.delete')->only('destroy');
    }
    
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('academic.course.update')) {
            abort(403, 'Unauthorized action.');
        }
        
        // Update logic
    }
}
```