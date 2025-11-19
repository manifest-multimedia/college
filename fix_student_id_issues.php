<?php

/**
 * Fix Student ID Issues Script
 *
 * Issues to fix:
 * 1. Students have {YEAR} literal text instead of actual year (23)
 * 2. Failed updates due to uniqueness conflicts
 * 3. Reversion failing due to backup issues
 * 4. Filtering excluding already-converted students
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Student;
use Illuminate\Support\Facades\DB;

echo "================================================\n";
echo "  FIX STUDENT ID ISSUES\n";
echo "================================================\n\n";

try {
    DB::beginTransaction();

    // 1. Fix students with {YEAR} literal in their IDs
    echo "1. Fixing students with literal {YEAR} in their IDs...\n";

    $studentsWithLiteralYear = Student::where('student_id', 'like', '%{YEAR}%')->get();
    echo "   Found {$studentsWithLiteralYear->count()} students with literal {YEAR}\n";

    $fixedYear = 0;
    foreach ($studentsWithLiteralYear as $student) {
        $currentId = $student->student_id;

        // Parse the original ID to get the year
        $service = new App\Services\StudentIdReassignmentService;

        // Try to find the original MHIAFRGN ID from backup
        $backup = DB::table('student_id_changes')
            ->where('student_id', $student->id)
            ->where('old_student_id', 'like', 'MHIAFRGN%')
            ->orderBy('created_at', 'desc')
            ->first();

        if ($backup) {
            $parsed = $service->parseStudentId($backup->old_student_id);
            $year = $parsed['year'] ?? '23';
        } else {
            $year = '23'; // Default fallback
        }

        // Replace {YEAR} with actual year
        $newId = str_replace('{YEAR}', $year, $currentId);

        // Check uniqueness
        $exists = Student::where('student_id', $newId)->where('id', '!=', $student->id)->exists();

        if (! $exists) {
            $student->student_id = $newId;
            $student->save();

            echo "   ✓ Fixed: {$currentId} → {$newId}\n";
            $fixedYear++;
        } else {
            echo "   ✗ Skipped (duplicate): {$currentId} → {$newId}\n";
        }
    }

    echo "   Fixed {$fixedYear} students with literal {YEAR}\n\n";

    // 2. Clean up student_id_changes status
    echo "2. Cleaning up backup records...\n";

    // Mark old inconsistent records as superseded
    $updatedBackups = DB::table('student_id_changes')
        ->where('status', 'active')
        ->whereIn('student_id', function ($query) {
            $query->select('s1.student_id')
                ->from('student_id_changes as s1')
                ->join('student_id_changes as s2', 's1.student_id', '=', 's2.student_id')
                ->where('s1.status', 'active')
                ->where('s2.status', 'active')
                ->where('s1.id', '!=', 's2.id')
                ->groupBy('s1.student_id');
        })
        ->update(['status' => 'superseded']);

    echo "   Updated {$updatedBackups} duplicate backup records to 'superseded'\n";

    // Create new active backup records for current state
    $studentsNeedingBackup = Student::whereNotIn('id', function ($query) {
        $query->select('student_id')->from('student_id_changes')->where('status', 'active');
    })->get();

    echo "   Found {$studentsNeedingBackup->count()} students needing backup records\n";

    $createdBackups = 0;
    foreach ($studentsNeedingBackup as $student) {
        // Find the most recent change for this student
        $lastChange = DB::table('student_id_changes')
            ->where('student_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastChange) {
            // Create new active record based on current state
            DB::table('student_id_changes')->insert([
                'student_id' => $student->id,
                'old_student_id' => $lastChange->old_student_id, // Keep original as backup
                'new_student_id' => $student->student_id, // Current state
                'changed_by' => auth()->id() ?? 1,
                'status' => 'active',
                'notes' => 'Auto-created backup for reversion capability',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $createdBackups++;
        }
    }

    echo "   Created {$createdBackups} new backup records\n\n";

    // 3. Summary of current state
    echo "3. Current database state:\n";

    $stats = DB::select("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN student_id LIKE 'MHIAF%' AND student_id NOT LIKE '%/%' THEN 1 ELSE 0 END) as mhiafrgn_simple,
            SUM(CASE WHEN student_id LIKE 'MHIAF/%' THEN 1 ELSE 0 END) as mhiaf_structured,
            SUM(CASE WHEN student_id LIKE '%{YEAR}%' THEN 1 ELSE 0 END) as literal_year,
            SUM(CASE WHEN student_id LIKE 'STU/%' THEN 1 ELSE 0 END) as stu_format
        FROM students
    ")[0];

    echo "   Total students: {$stats->total}\n";
    echo "   MHIAFRGN simple format: {$stats->mhiafrgn_simple}\n";
    echo "   MHIAF structured format: {$stats->mhiaf_structured}\n";
    echo "   With literal {YEAR}: {$stats->literal_year}\n";
    echo "   STU format: {$stats->stu_format}\n\n";

    // 4. Check backup coverage
    $backupStats = DB::select("
        SELECT 
            COUNT(DISTINCT student_id) as students_with_backups,
            COUNT(*) as total_backup_records,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_backups
        FROM student_id_changes
    ")[0];

    echo "4. Backup coverage:\n";
    echo "   Students with backups: {$backupStats->students_with_backups}\n";
    echo "   Total backup records: {$backupStats->total_backup_records}\n";
    echo "   Active backup records: {$backupStats->active_backups}\n\n";

    DB::commit();

    echo "================================================\n";
    echo "✓ FIXES COMPLETED SUCCESSFULLY\n";
    echo "================================================\n\n";

    echo "Next steps:\n";
    echo "1. Test AI Sensei preview functionality\n";
    echo "2. Test AI Sensei reassignment with custom patterns\n";
    echo "3. Test AI Sensei reversion functionality\n\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "\n✗ ERROR: {$e->getMessage()}\n";
    echo "✗ No changes were made to the database.\n\n";
    exit(1);
}
