@php
    use App\Models\User;
    use App\Models\Student;
    use App\Models\CollegeClass;
    use Spatie\Permission\Models\Role;

    $totalUsers = User::count();
    
    $studentRole = Role::where('name', 'Student')->first();
    $parentRole = Role::where('name', 'Parent')->first();
    
    $excludeRoleIds = [];
    if ($studentRole) {
        $excludeRoleIds[] = $studentRole->id;
    }
    if ($parentRole) {
        $excludeRoleIds[] = $parentRole->id;
    }
    
    if (!empty($excludeRoleIds)) {
        $activeStaff = User::whereHas('roles', function ($query) use ($excludeRoleIds) {
            $query->whereNotIn('id', $excludeRoleIds);
        })->count();
    } else {
        $activeStaff = User::count();
    }
    
    $totalStudents = Student::count();
    $activePrograms = CollegeClass::count();
@endphp

<div class="col-xl-3 col-md-6">
    <x-dashboard.counter 
        title="Total Users" 
        :value="$totalUsers" 
        icon="fas fa-users" 
        color="primary" 
    />
</div>

<div class="col-xl-3 col-md-6">
    <x-dashboard.counter 
        title="Active Staff" 
        :value="$activeStaff" 
        icon="fas fa-user-tie" 
        color="success" 
    />
</div>

<div class="col-xl-3 col-md-6">
    <x-dashboard.counter 
        title="Total Students" 
        :value="$totalStudents" 
        icon="fas fa-user-graduate" 
        color="warning" 
    />
</div>

<div class="col-xl-3 col-md-6">
    <x-dashboard.counter 
        title="Active Programs" 
        :value="$activePrograms" 
        icon="fas fa-graduation-cap" 
        color="info" 
    />
</div>
