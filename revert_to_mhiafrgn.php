<?php

/**
 * Emergency Reversion Script - Revert ALL students back to MHIAFRGN format
 *
 * This reverts the changes made at 2025-11-19 22:26:18 (10:26 PM)
 * when AI Sensei executed the reassignment with wrong sequences.
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Student;
use Illuminate\Support\Facades\DB;

echo "==============================================\n";
echo "  REVERT TO ORIGINAL MHIAFRGN FORMAT\n";
echo "==============================================\n\n";

try {
    DB::beginTransaction();

    // Get all students with STU/I/25/26/ format
    $students = Student::where('student_id', 'like', 'STU/I/25/26/%')->get();

    echo "Found {$students->count()} students with STU/I/25/26/ format\n\n";

    if ($students->isEmpty()) {
        echo "No students to revert!\n";
        exit(0);
    }

    $reverted = 0;
    $failed = 0;

    foreach ($students as $student) {
        // Find the original MHIAFRGN ID from backup
        $backup = DB::table('student_id_changes')
            ->where('student_id', $student->id)
            ->where('old_student_id', 'like', 'MHIAFRGN%')
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $backup) {
            echo "  ✗ Student {$student->id} ({$student->first_name} {$student->last_name}): No MHIAFRGN backup found\n";
            $failed++;

            continue;
        }

        $oldId = $student->student_id;
        $originalId = $backup->old_student_id;

        // Update student back to original ID
        $student->student_id = $originalId;
        $student->save();

        // Mark current change as reverted
        DB::table('student_id_changes')
            ->where('student_id', $student->id)
            ->where('new_student_id', $oldId)
            ->where('status', 'active')
            ->update(['status' => 'reverted']);

        echo "  ✓ Student {$student->id} ({$student->first_name} {$student->last_name}): {$oldId} → {$originalId}\n";
        $reverted++;
    }

    DB::commit();

    echo "\n==============================================\n";
    echo "REVERSION COMPLETED\n";
    echo "==============================================\n";
    echo "Total students: {$students->count()}\n";
    echo "Successfully reverted: {$reverted}\n";
    echo "Failed: {$failed}\n\n";

    if ($reverted > 0) {
        echo "✓ All students have been restored to their original MHIAFRGN format!\n";
        echo "✓ You can now use AI Sensei to apply the correct custom pattern.\n\n";
    }

} catch (\Exception $e) {
    DB::rollBack();
    echo "\n✗ ERROR: {$e->getMessage()}\n";
    echo "✗ No changes were made to the database.\n\n";
    exit(1);
}
