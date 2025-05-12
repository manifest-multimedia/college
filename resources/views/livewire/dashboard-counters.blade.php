<div>
    <div class="row g-5 g-xl-8 mb-5">
        <div class="col-xl-4 col-md-6">
            <x-dashboard.counter 
                title="Total Users" 
                :value="$totalUsers" 
                icon="fas fa-users" 
                color="primary" 
            />
        </div>
        
        <div class="col-xl-4 col-md-6">
            <x-dashboard.counter 
                title="Active Staff" 
                :value="$activeStaff" 
                icon="fas fa-user-tie" 
                color="success" 
            />
        </div>
        
        <div class="col-xl-4 col-md-6">
            <x-dashboard.counter 
                title="Total Students" 
                :value="$totalStudents" 
                icon="fas fa-user-graduate" 
                color="info" 
            />
        </div>
        
        <div class="col-xl-4 col-md-6">
            <x-dashboard.counter 
                title="Active Programs" 
                :value="$activePrograms" 
                icon="fas fa-graduation-cap" 
                color="warning" 
            />
        </div>
        <div class="col-xl-4 col-md-6">
            <x-dashboard.counter 
                title="SMS Credits" 
                :value="$smsCredits" 
                icon="fas fa-comment-dots"
                color="danger" 
            />
        </div>
        <div class="col-xl-4 col-md-6">
            <x-dashboard.counter 
                title="Total Exams" 
                :value="$totalExams" 
                icon="fas fa-file-alt" 
                color="dark" 
            />
        </div>
    </div>
</div>