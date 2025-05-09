<?php

namespace App\Console\Commands;

use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanupEmptyStudentRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'students:cleanup-empty-records {--dry-run : Run in simulation mode without making changes} {--force : Skip confirmation prompts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove student records that have empty Student ID, First Name, Last Name, and Email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('DRY RUN MODE: No database changes will be made');
        }

        // Find students with missing required fields
        $query = Student::where(function ($query) {
            $query->whereNull('student_id')
                  ->orWhere('student_id', '')
                  ->orWhereNull('first_name')
                  ->orWhere('first_name', '')
                  ->orWhereNull('last_name')
                  ->orWhere('last_name', '')
                  ->orWhereNull('email')
                  ->orWhere('email', '');
        });

        $emptyRecordsCount = $query->count();
        
        if ($emptyRecordsCount === 0) {
            $this->info('No empty student records found. Nothing to clean up.');
            return 0;
        }
        
        $this->info("Found {$emptyRecordsCount} student records with missing required fields");
        
        // Sample of records to be deleted (show up to 5)
        $this->info('Sample records that will be removed:');
        $sampleRecords = $query->limit(5)->get();
        
        $headers = ['ID', 'Student ID', 'First Name', 'Last Name', 'Email'];
        $rows = [];
        
        foreach ($sampleRecords as $student) {
            $rows[] = [
                $student->id,
                $student->student_id ?? 'MISSING',
                $student->first_name ?? 'MISSING',
                $student->last_name ?? 'MISSING',
                $student->email ?? 'MISSING'
            ];
        }
        
        $this->table($headers, $rows);
        
        if ($sampleRecords->count() < $emptyRecordsCount) {
            $this->info("... and " . ($emptyRecordsCount - $sampleRecords->count()) . " more records");
        }
        
        // Request confirmation unless --force flag is used
        if (!$this->option('force') && !$isDryRun) {
            if (!$this->confirm("This will permanently remove {$emptyRecordsCount} student records. Continue?")) {
                $this->info('Operation cancelled by user.');
                return 0;
            }
        }
        
        // Initialize counter for deleted records
        $deletedCount = 0;
        
        // Start transaction for safer operations
        DB::beginTransaction();
        
        try {
            // Get all IDs to log them
            $idsToDelete = $query->pluck('id')->toArray();
            
            if (!$isDryRun) {
                // Perform the deletion
                $deletedCount = $query->delete();
                
                // Log the deletion
                Log::info('Empty student records deleted', [
                    'count' => $deletedCount,
                    'deleted_by' => 'CleanupEmptyStudentRecords command',
                    'record_ids' => $idsToDelete
                ]);
                
                // Commit transaction
                DB::commit();
                
                $this->info("Successfully deleted {$deletedCount} empty student records");
            } else {
                // In dry run mode, just report what would be done
                $this->info("Would delete {$emptyRecordsCount} empty student records");
                $this->info("IDs that would be deleted: " . implode(', ', $idsToDelete));
                
                // Rollback transaction in dry run mode
                DB::rollBack();
            }
        } catch (\Exception $e) {
            // Rollback transaction if any error occurs
            DB::rollBack();
            
            Log::error('Error deleting empty student records: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            
            $this->error('Error deleting empty student records: ' . $e->getMessage());
            return 1;
        }
        
        if ($isDryRun) {
            $this->info('Dry run completed. No changes were made to the database.');
        }
        
        return 0;
    }
}
