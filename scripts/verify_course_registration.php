<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Livewire\StudentCourseRegistration;
use App\Models\CourseRegistration;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

echo "========================================================\n";
echo "Course Registration Automated Verification Script\n";
echo "========================================================\n\n";

$allPassed = true;

function runTest($title, callable $test) {
    global $allPassed;
    echo "Testing: $title... ";
    try {
        $test();
        echo "✅ PASSED\n";
    } catch (\Throwable $e) {
        $allPassed = false;
        echo "❌ FAILED: " . $e->getMessage() . "\n";
        echo "   at " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
}

// TEST 1: Eligible Student (student.eligible@college.test)
runTest('Scenario 1: student.eligible - Can see all available Y1 Semester 1 courses & register', function () {
    $user = User::where('email', 'student.eligible@college.test')->firstOrFail();
    Auth::login($user);

    $component = new StudentCourseRegistration();
    $component->mount();

    if (!$component->registrationAllowed) {
        throw new \Exception("Expected registrationAllowed to be true, got false. Message: {$component->registrationMessage}");
    }

    $codes = $component->availableSubjects->pluck('course_code')->toArray();
    
    // Check that expected courses are present
    $expected = ['NUR 111', 'NUR 112', 'NUR 113', 'GEN 101', 'NUR 114'];
    foreach ($expected as $exp) {
        if (!in_array($exp, $codes)) {
            throw new \Exception("Expected course $exp to be available, but available were: " . implode(', ', $codes));
        }
    }

    // Should NOT see Year 2 courses
    if (in_array('NUR 211', $codes)) {
        throw new \Exception("Year 1 student should not see Year 2 course NUR 211");
    }

    // Test submitting registration
    $subject1 = Subject::where('course_code', 'NUR 111')->firstOrFail();
    $subject2 = Subject::where('course_code', 'NUR 112')->firstOrFail();
    $component->toggleSubject($subject1->id);
    $component->toggleSubject($subject2->id);

    $component->submitRegistration();

    // Verify registrations were saved
    $registered = CourseRegistration::where('student_id', $component->student->id)
        ->where('academic_year_id', $component->currentAcademicYear->id)
        ->where('semester_id', $component->currentSemester->id)
        ->pluck('subject_id')
        ->toArray();

    if (!in_array($subject1->id, $registered) || !in_array($subject2->id, $registered)) {
        throw new \Exception("Failed to register selected courses. Registered IDs: " . implode(', ', $registered));
    }
});

// TEST 2: Threshold Student (student.threshold@college.test)
runTest('Scenario 2: student.threshold - Eligible at 60% payment threshold', function () {
    $user = User::where('email', 'student.threshold@college.test')->firstOrFail();
    Auth::login($user);

    $component = new StudentCourseRegistration();
    $component->mount();

    if (!$component->registrationAllowed) {
        throw new \Exception("Expected registrationAllowed to be true at 60% threshold, got false. Message: {$component->registrationMessage}");
    }

    if (abs($component->paymentPercentage - 60.0) > 0.01) {
        throw new \Exception("Expected payment percentage to be 60.0%, got {$component->paymentPercentage}%");
    }

    if ($component->availableSubjects->isEmpty()) {
        throw new \Exception("Available subjects should not be empty for threshold student");
    }
});

// TEST 3: Unpaid Student (student.unpaid@college.test)
runTest('Scenario 3: student.unpaid - Ineligible due to fee payment under 60%', function () {
    $user = User::where('email', 'student.unpaid@college.test')->firstOrFail();
    Auth::login($user);

    $component = new StudentCourseRegistration();
    $component->mount();

    if ($component->registrationAllowed) {
        throw new \Exception("Expected registrationAllowed to be false for 30% payment, got true");
    }

    if (!str_contains($component->registrationMessage, '60%')) {
        throw new \Exception("Expected message to mention 60% threshold requirement, got: {$component->registrationMessage}");
    }
});

// TEST 4: No Bill Student (student.nobill@college.test)
runTest('Scenario 4: student.nobill - Ineligible due to missing fee bill', function () {
    $user = User::where('email', 'student.nobill@college.test')->firstOrFail();
    Auth::login($user);

    $component = new StudentCourseRegistration();
    $component->mount();

    if ($component->registrationAllowed) {
        throw new \Exception("Expected registrationAllowed to be false when no fee bill exists, got true");
    }

    if (!str_contains(strtolower($component->registrationMessage), 'no fee bill found')) {
        throw new \Exception("Expected message to mention 'no fee bill found', got: {$component->registrationMessage}");
    }
});

// TEST 5: Already Registered Student (student.registered@college.test)
runTest('Scenario 5: student.registered - Existing registrations loaded and pre-selected', function () {
    $user = User::where('email', 'student.registered@college.test')->firstOrFail();
    Auth::login($user);

    $component = new StudentCourseRegistration();
    $component->mount();

    if ($component->existingRegistrations->count() !== 4) {
        throw new \Exception("Expected 4 existing registrations, found: " . $component->existingRegistrations->count());
    }

    if (count($component->selectedSubjects) !== 4) {
        throw new \Exception("Expected 4 pre-selected subjects, found: " . count($component->selectedSubjects));
    }
});

// TEST 6: Year 2 Student (student.year2@college.test)
runTest('Scenario 6: student.year2 - Sees Year 2 Semester 1 courses', function () {
    $user = User::where('email', 'student.year2@college.test')->firstOrFail();
    Auth::login($user);

    $component = new StudentCourseRegistration();
    $component->mount();

    if (!$component->registrationAllowed) {
        throw new \Exception("Expected registrationAllowed to be true, got false. Message: {$component->registrationMessage}");
    }

    $codes = $component->availableSubjects->pluck('course_code')->toArray();

    if (!in_array('NUR 211', $codes) || !in_array('NUR 212', $codes)) {
        throw new \Exception("Year 2 student should see NUR 211 and NUR 212. Available: " . implode(', ', $codes));
    }

    if (in_array('NUR 111', $codes)) {
        throw new \Exception("Year 2 student should not see Year 1 course NUR 111. Available: " . implode(', ', $codes));
    }
});

echo "\n========================================================\n";
if ($allPassed) {
    echo "🎉 ALL 6 SCENARIOS PASSED SUCCESSFULLY!\n";
} else {
    echo "⚠️ SOME SCENARIOS FAILED!\n";
    exit(1);
}
echo "========================================================\n";
