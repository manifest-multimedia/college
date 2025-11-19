<?php

/**
 * EMERGENCY REVERSION SCRIPT
 *
 * This script reverts all student IDs back to their original values
 * Run this in Laravel Tinker or as an Artisan command
 */

// Get all affected students with their FIRST (original) ID change
$affectedStudents = DB::table('student_id_changes')
    ->select('student_id')
    ->groupBy('student_id')
    ->havingRaw('COUNT(*) > 1') // Only students with multiple changes
    ->pluck('student_id');

echo "Found {$affectedStudents->count()} students with multiple ID changes\n\n";

$reverted = 0;
$failed = 0;

foreach ($affectedStudents as $studentId) {
    try {
        // Get the FIRST (original) change record
        $originalChange = DB::table('student_id_changes')
            ->where('student_id', $studentId)
            ->orderBy('created_at', 'asc')
            ->first();

        if (! $originalChange) {
            echo "❌ No change record found for student ID {$studentId}\n";
            $failed++;

            continue;
        }

        // Get the student
        $student = App\Models\Student::find($studentId);

        if (! $student) {
            echo "❌ Student {$studentId} not found\n";
            $failed++;

            continue;
        }

        $currentId = $student->student_id;
        $originalId = $originalChange->old_student_id;

        // Revert to original ID
        $student->student_id = $originalId;
        $student->save();

        // Mark all changes as reverted
        DB::table('student_id_changes')
            ->where('student_id', $studentId)
            ->update(['status' => 'reverted']);

        echo "✅ Reverted student {$studentId}: {$currentId} → {$originalId}\n";
        $reverted++;

    } catch (\Exception $e) {
        echo "❌ Error reverting student {$studentId}: {$e->getMessage()}\n";
        $failed++;
    }
}

echo "\n=== REVERSION SUMMARY ===\n";
echo "Total affected: {$affectedStudents->count()}\n";
echo "Successfully reverted: {$reverted}\n";
echo "Failed: {$failed}\n";

// Verify reversion
echo "\n=== VERIFICATION ===\n";
$nonMhiafIds = DB::table('students')
    ->where('student_id', 'NOT LIKE', 'MHIAFRGN%')
    ->where('student_id', 'NOT LIKE', 'COLLEGE%')
    ->count();

echo "Students with non-MHIAF/COLLEGE IDs: {$nonMhiafIds}\n";

if ($nonMhiafIds > 0) {
    echo "⚠️ WARNING: Some students still have unexpected ID formats\n";

    $samples = DB::table('students')
        ->where('student_id', 'NOT LIKE', 'MHIAFRGN%')
        ->where('student_id', 'NOT LIKE', 'COLLEGE%')
        ->limit(5)
        ->get(['id', 'student_id', 'first_name', 'last_name']);

    echo "\nSample students with unexpected IDs:\n";
    foreach ($samples as $sample) {
        echo "- ID {$sample->id}: {$sample->student_id} ({$sample->first_name} {$sample->last_name})\n";
    }
} else {
    echo "✅ All students have been reverted to original ID formats\n";
}
