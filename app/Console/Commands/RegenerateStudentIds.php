<?php

namespace App\Console\Commands;

use App\Models\CollegeClass;
use App\Models\Student;
use App\Services\StudentIdGenerationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RegenerateStudentIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'students:regenerate-ids 
                            {--program=* : Regenerate IDs for specific program(s) only}
                            {--academic-year= : Regenerate IDs for specific academic year}
                            {--duplicates-only : Only regenerate IDs for students with duplicate or malformed IDs}
                            {--dry-run : Show what would be changed without making actual changes}
                            {--force : Skip confirmation prompts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate student IDs following proper sequence and format configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $duplicatesOnly = $this->option('duplicates-only');
        $programs = $this->option('program');
        $academicYearId = $this->option('academic-year');

        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE: No database changes will be made');
        }

        $this->info('🔧 Starting student ID regeneration process...');

        // Find students that need ID regeneration
        $studentsToFix = $this->findStudentsToRegenerate($duplicatesOnly, $programs, $academicYearId);

        if ($studentsToFix->isEmpty()) {
            $this->info('✅ No students found that need ID regeneration');

            return 0;
        }

        $this->info("Found {$studentsToFix->count()} students that need ID regeneration");

        // Show preview of changes
        $this->displayPreview($studentsToFix);

        // Confirm before proceeding (unless dry-run or force)
        if (! $isDryRun && ! $this->option('force')) {
            if (! $this->confirm('Do you want to proceed with regenerating these student IDs?')) {
                $this->info('❌ Operation cancelled by user');

                return 0;
            }
        }

        // Perform the regeneration
        $results = $this->regenerateStudentIds($studentsToFix, $isDryRun);

        // Display results
        $this->displayResults($results, $isDryRun);

        return 0;
    }

    /**
     * Find students that need ID regeneration
     */
    private function findStudentsToRegenerate(bool $duplicatesOnly, array $programs, ?string $academicYearId)
    {
        $query = Student::query();

        if ($duplicatesOnly) {
            // Find students with duplicate IDs or malformed IDs
            $duplicateIds = DB::table('students')
                ->select('student_id')
                ->whereNotNull('student_id')
                ->where('student_id', '!=', '')
                ->groupBy('student_id')
                ->havingRaw('COUNT(*) > 1')
                ->pluck('student_id')
                ->toArray();

            $malformedIds = Student::whereNotNull('student_id')
                ->where('student_id', '!=', '')
                ->get()
                ->filter(function ($student) {
                    // Check if ID follows proper format
                    $service = new StudentIdGenerationService;

                    return ! $service->isValidStudentIdFormat($student->student_id);
                })
                ->pluck('student_id')
                ->toArray();

            $idsToFix = array_merge($duplicateIds, $malformedIds);

            if (empty($idsToFix)) {
                return collect();
            }

            $query->whereIn('student_id', $idsToFix);
        }

        // Filter by programs if specified
        if (! empty($programs)) {
            $programIds = CollegeClass::whereIn('name', $programs)->pluck('id');
            $query->whereIn('college_class_id', $programIds);
        }

        // Filter by academic year if specified
        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        return $query->with(['collegeClass', 'academicYear'])
            ->orderBy('college_class_id')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }

    /**
     * Display preview of changes
     */
    private function displayPreview($students)
    {
        $this->info("\n📋 Preview of changes:");

        $headers = ['Current ID', 'Student Name', 'Program', 'Action'];
        $rows = [];

        foreach ($students->take(10) as $student) {
            $rows[] = [
                $student->student_id ?: 'NULL',
                "{$student->first_name} {$student->last_name}",
                $student->collegeClass->name ?? 'N/A',
                'Will regenerate',
            ];
        }

        if ($students->count() > 10) {
            $rows[] = ['...', 'and '.($students->count() - 10).' more students', '...', '...'];
        }

        $this->table($headers, $rows);
    }

    /**
     * Regenerate student IDs
     */
    private function regenerateStudentIds($students, bool $isDryRun)
    {
        $results = [
            'success' => 0,
            'errors' => 0,
            'changes' => [],
        ];

        $service = new StudentIdGenerationService;

        // Group students by program for proper sequential assignment
        $studentsByProgram = $students->groupBy('college_class_id');

        foreach ($studentsByProgram as $programId => $programStudents) {
            $this->info("\n🔄 Processing program: ".($programStudents->first()->collegeClass->name ?? 'Unknown'));

            // Clear existing IDs temporarily to ensure proper sequence generation
            $originalIds = [];

            if (! $isDryRun) {
                DB::beginTransaction();

                try {
                    // Store original IDs and clear them
                    foreach ($programStudents as $student) {
                        $originalIds[$student->id] = $student->student_id;
                        $student->update(['student_id' => null]);
                    }

                    // Regenerate IDs in alphabetical order
                    foreach ($programStudents->sortBy('last_name') as $student) {
                        $newId = $service->generateStudentId(
                            $student->first_name,
                            $student->last_name,
                            $student->college_class_id,
                            $student->academic_year_id
                        );

                        $student->update(['student_id' => $newId]);

                        $results['changes'][] = [
                            'student_id' => $student->id,
                            'name' => "{$student->first_name} {$student->last_name}",
                            'old_id' => $originalIds[$student->id],
                            'new_id' => $newId,
                            'program' => $student->collegeClass->name ?? 'N/A',
                        ];

                        $results['success']++;

                        $this->line("  ✅ {$student->first_name} {$student->last_name}: {$originalIds[$student->id]} → {$newId}");
                    }

                    DB::commit();

                } catch (\Exception $e) {
                    DB::rollBack();
                    $results['errors']++;
                    $this->error("  ❌ Error processing program {$programId}: ".$e->getMessage());

                    // Restore original IDs on error
                    foreach ($originalIds as $studentId => $originalId) {
                        Student::find($studentId)->update(['student_id' => $originalId]);
                    }
                }
            } else {
                // Dry run - just show what would happen
                foreach ($programStudents->sortBy('last_name') as $student) {
                    // Temporarily clear the ID for proper sequence generation
                    $originalId = $student->student_id;
                    $student->student_id = null;

                    $newId = $service->generateStudentId(
                        $student->first_name,
                        $student->last_name,
                        $student->college_class_id,
                        $student->academic_year_id
                    );

                    // Restore original ID
                    $student->student_id = $originalId;

                    $results['changes'][] = [
                        'student_id' => $student->id,
                        'name' => "{$student->first_name} {$student->last_name}",
                        'old_id' => $originalId,
                        'new_id' => $newId,
                        'program' => $student->collegeClass->name ?? 'N/A',
                    ];

                    $this->line("  🔍 {$student->first_name} {$student->last_name}: {$originalId} → {$newId}");
                }
            }
        }

        return $results;
    }

    /**
     * Display results summary
     */
    private function displayResults($results, bool $isDryRun)
    {
        $this->info("\n📊 Summary:");

        if ($isDryRun) {
            $this->info("  🔍 Would update: {$results['success']} students");
        } else {
            $this->info("  ✅ Successfully updated: {$results['success']} students");
        }

        if ($results['errors'] > 0) {
            $this->error("  ❌ Errors: {$results['errors']}");
        }

        // Log the changes for audit trail
        if (! $isDryRun && ! empty($results['changes'])) {
            Log::info('Student IDs regenerated via command', [
                'total_updated' => $results['success'],
                'errors' => $results['errors'],
                'executed_by' => 'RegenerateStudentIds command',
                'changes' => $results['changes'],
            ]);
        }
    }
}
