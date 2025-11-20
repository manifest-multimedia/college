<?php

namespace App\Console\Commands;

use App\Models\CollegeClass;
use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NormalizeProgramNames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'program:normalize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Normalize program names to standard formats and update related records';

    /**
     * The two standard program names we want to keep.
     */
    protected $standardPrograms = [
        'Registered General Nursing',
        'Registered Midwifery',
    ];

    /**
     * Mapping of variants to standard names
     */
    protected $programMappings = [];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Define program mappings (variants to standard names)
        $this->programMappings = [
            // RGN variants
            'RGN' => 'Registered General Nursing',
            'registered general nursing' => 'Registered General Nursing',
            'Registered  General  Nursing' => 'Registered General Nursing',
            'Registered General nursing' => 'Registered General Nursing',
            'registered general Nursing' => 'Registered General Nursing',
            'General Nursing' => 'Registered General Nursing',
            'Nursing' => 'Registered General Nursing',

            // RM variants
            'RM' => 'Registered Midwifery',
            'registered midwifery' => 'Registered Midwifery',
            'Registered  Midwifery' => 'Registered Midwifery',
            'Registered midwifery' => 'Registered Midwifery',
            'registered Midwifery' => 'Registered Midwifery',
            'Midwifery' => 'Registered Midwifery',
        ];
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting program name normalization...');

        // Use a transaction for data integrity
        DB::beginTransaction();

        try {
            $standardProgramIds = $this->ensureStandardPrograms();

            // First normalize non-standard program names
            $stats = $this->normalizePrograms($standardProgramIds);

            // Then merge duplicate standard programs
            $mergeDuplicateStats = $this->mergeDuplicateStandardPrograms($standardProgramIds);

            // Combine the stats
            $stats['studentsUpdated'] += $mergeDuplicateStats['studentsUpdated'];
            $stats['programsMerged'] += $mergeDuplicateStats['programsMerged'];
            $stats['programsDeleted'] += $mergeDuplicateStats['programsDeleted'];

            DB::commit();

            // Display summary
            $this->info('Normalization completed successfully!');
            $this->info("Students updated: {$stats['studentsUpdated']}");
            $this->info("Programs merged: {$stats['programsMerged']}");
            $this->info("Obsolete programs deleted: {$stats['programsDeleted']}");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('An error occurred during normalization: '.$e->getMessage());
            $this->error($e->getTraceAsString());

            return 1;
        }

        return 0;
    }

    /**
     * Ensure standard programs exist in the database
     *
     * @return array Array of standard program IDs
     */
    protected function ensureStandardPrograms()
    {
        $this->info('Ensuring standard programs exist...');

        $standardProgramIds = [];

        foreach ($this->standardPrograms as $programName) {
            // Get the first occurrence of each standard program
            $program = CollegeClass::where('name', $programName)->orderBy('id')->first();

            // If it doesn't exist, create it
            if (! $program) {
                $program = CollegeClass::create([
                    'name' => $programName,
                    'description' => $programName,
                    'slug' => Str::slug($programName),
                ]);
            }

            $standardProgramIds[$programName] = $program->id;
            $this->line("- Standard program: {$programName} (ID: {$program->id})");
        }

        return $standardProgramIds;
    }

    /**
     * Merge duplicate standard program entries
     * (e.g., if we have two "Registered General Nursing" entries with different IDs)
     *
     * @param  array  $standardProgramIds  IDs of standard programs
     * @return array Statistics of the operation
     */
    protected function mergeDuplicateStandardPrograms($standardProgramIds)
    {
        $stats = [
            'studentsUpdated' => 0,
            'programsMerged' => 0,
            'programsDeleted' => 0,
        ];

        $this->info('Checking for duplicate standard program entries...');

        foreach ($this->standardPrograms as $programName) {
            $canonicalId = $standardProgramIds[$programName];

            // Find all duplicate entries with the same name but different IDs
            $duplicates = CollegeClass::where('name', $programName)
                ->where('id', '!=', $canonicalId)
                ->get();

            if ($duplicates->count() > 0) {
                $this->line("Found {$duplicates->count()} duplicate entries for '{$programName}'");

                foreach ($duplicates as $duplicate) {
                    // Count students that will be affected
                    $affectedStudents = $duplicate->students()->count();

                    // Update all students from this program to the standard one
                    if ($affectedStudents > 0) {
                        Student::where('college_class_id', $duplicate->id)
                            ->update(['college_class_id' => $canonicalId]);

                        $stats['studentsUpdated'] += $affectedStudents;
                        $this->line("  - Migrated {$affectedStudents} students from duplicate '{$programName}' (ID: {$duplicate->id}) to canonical ID: {$canonicalId}");
                    }

                    // Delete the duplicate program
                    $duplicate->delete();
                    $stats['programsDeleted']++;
                    $stats['programsMerged']++;
                }
            }
        }

        return $stats;
    }

    /**
     * Normalize all program names and update related records
     *
     * @param  array  $standardProgramIds  IDs of standard programs
     * @return array Statistics of the operation
     */
    protected function normalizePrograms($standardProgramIds)
    {
        $stats = [
            'studentsUpdated' => 0,
            'programsMerged' => 0,
            'programsDeleted' => 0,
        ];

        // Get all non-standard programs
        $nonStandardPrograms = CollegeClass::whereNotIn('name', $this->standardPrograms)->get();

        $this->info('Processing '.$nonStandardPrograms->count().' non-standard programs...');

        $bar = $this->output->createProgressBar($nonStandardPrograms->count());
        $bar->start();

        foreach ($nonStandardPrograms as $program) {
            $standardName = $this->determineStandardName($program->name);

            if ($standardName) {
                $standardId = $standardProgramIds[$standardName];

                // Count students that will be affected
                $affectedStudents = $program->students()->count();

                // Update all students from this program to the standard one
                if ($affectedStudents > 0) {
                    Student::where('college_class_id', $program->id)
                        ->update(['college_class_id' => $standardId]);

                    $stats['studentsUpdated'] += $affectedStudents;
                    $this->line('');
                    $this->line("  - Migrated {$affectedStudents} students from '{$program->name}' to '{$standardName}'");
                }

                // Delete the obsolete program
                $program->delete();
                $stats['programsDeleted']++;
                $stats['programsMerged']++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->line('');

        return $stats;
    }

    /**
     * Determine which standard program name a variant maps to
     *
     * @param  string  $programName  The program name to normalize
     * @return string|null The standard name or null if no match
     */
    protected function determineStandardName($programName)
    {
        // Direct match in our mapping
        if (isset($this->programMappings[$programName])) {
            return $this->programMappings[$programName];
        }

        // Try case-insensitive match
        $lowercaseName = strtolower($programName);
        foreach ($this->programMappings as $variant => $standard) {
            if (strtolower($variant) === $lowercaseName) {
                return $standard;
            }
        }

        // Try fuzzy matching based on keywords
        if (Str::contains(strtolower($programName), ['nursing', 'rgn'])) {
            return 'Registered General Nursing';
        }

        if (Str::contains(strtolower($programName), ['midwif', 'rm'])) {
            return 'Registered Midwifery';
        }

        // Ask the user for programs we couldn't automatically determine
        $this->line("\nUncertain program mapping: '{$programName}'");
        $choices = [
            1 => $this->standardPrograms[0],
            2 => $this->standardPrograms[1],
            3 => 'Skip this program',
        ];

        foreach ($choices as $key => $choice) {
            $this->line("  {$key}. {$choice}");
        }

        $choice = $this->ask('Which standard program should this map to?', 3);

        if ($choice == 1) {
            return $this->standardPrograms[0];
        }
        if ($choice == 2) {
            return $this->standardPrograms[1];
        }

        return null; // Skip this program
    }
}
