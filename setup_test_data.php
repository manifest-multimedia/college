<?php

/**
 * Create sample data for MCP testing
 */

require_once __DIR__.'/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    // First check if we have existing subjects to use for testing
    $existingSubjects = App\Models\Subject::limit(5)->get();

    if ($existingSubjects->count() > 0) {
        echo "✅ Using existing courses for testing:\n";
        foreach ($existingSubjects as $subject) {
            echo "   - {$subject->course_code}: {$subject->name}\n";
        }
        $testCourse = $existingSubjects->first();
    } else {
        echo "No existing subjects found. You may need to create some subjects first.\n";
        echo "For now, we'll test with a mock course code.\n";
        $testCourse = (object) ['course_code' => 'MOCK101', 'name' => 'Mock Course', 'id' => 1];
    }

    echo "\n✅ Test course ready:\n";
    echo "   Code: {$testCourse->course_code}\n";
    echo "   Name: {$testCourse->name}\n";
    echo "   ID: {$testCourse->id}\n\n";

    // List all courses
    echo "📚 All available courses:\n";
    $courses = App\Models\Subject::select('course_code', 'name')->limit(10)->get();

    foreach ($courses as $course) {
        echo "   - {$course->course_code}: {$course->name}\n";
    }

    if ($courses->count() === 0) {
        echo "   No courses found in database.\n";
        echo "   The MCP server will still work, but you may need to create courses first.\n";
    }

    echo "\n🎉 Sample data setup complete!\n";
    echo "Now you can run: php mcp_test.php --run\n";

} catch (Exception $e) {
    echo '❌ Error setting up sample data: '.$e->getMessage()."\n";
    echo "Stack trace:\n".$e->getTraceAsString()."\n";
}
