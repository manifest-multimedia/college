<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MigrateYearData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'academics:migrate-year-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate data from years table to academic_years table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting migration of year data to academic_years table...');
        
        // Check if years table exists
        if (!Schema::hasTable('years')) {
            $this->error('Years table does not exist. No migration needed.');
            return 1;
        }
        
        // Get all data from years table
        $years = DB::table('years')->get();
        
        if ($years->isEmpty()) {
            $this->info('No data found in years table. Nothing to migrate.');
            return 0;
        }
        
        $this->info('Found ' . $years->count() . ' records to migrate.');
        
        // Begin transaction
        DB::beginTransaction();
        
        try {
            $bar = $this->output->createProgressBar($years->count());
            $bar->start();
            
            foreach ($years as $year) {
                // Extract year value if possible from name
                $yearValue = null;
                if (preg_match('/(\d{4})/', $year->name, $matches)) {
                    $yearValue = (int)$matches[1];
                }
                
                // Check if academic year already exists with same name
                $existingYear = DB::table('academic_years')
                    ->where('name', $year->name)
                    ->first();
                
                if ($existingYear) {
                    // Update foreign keys in related tables
                    $this->updateForeignKeys($year->id, $existingYear->id);
                } else {
                    // Create new academic year from year data
                    $academicYearId = DB::table('academic_years')->insertGetId([
                        'name' => $year->name,
                        'slug' => $year->slug ?? Str::slug($year->name),
                        'year' => $yearValue,
                        'start_date' => now()->startOfYear(),
                        'end_date' => now()->endOfYear(),
                        'is_current' => false,
                        'is_deleted' => $year->is_deleted ?? false,
                        'created_by' => $year->created_by ?? null,
                        'created_at' => $year->created_at ?? now(),
                        'updated_at' => $year->updated_at ?? now(),
                    ]);
                    
                    // Update foreign keys in related tables
                    $this->updateForeignKeys($year->id, $academicYearId);
                }
                
                $bar->advance();
            }
            
            $bar->finish();
            $this->newLine(2);
            
            DB::commit();
            $this->info('Migration completed successfully!');
            
            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error during migration: ' . $e->getMessage());
            Log::error('Error during year data migration: ' . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * Update foreign keys in related tables
     * 
     * @param int $oldYearId
     * @param int $newAcademicYearId
     */
    protected function updateForeignKeys($oldYearId, $newAcademicYearId)
    {
        // Update semesters table if it has year_id
        if (Schema::hasColumn('semesters', 'year_id')) {
            DB::table('semesters')
                ->where('year_id', $oldYearId)
                ->update(['academic_year_id' => $newAcademicYearId]);
        }
        
        // Update subjects table if it has year_id
        if (Schema::hasColumn('subjects', 'year_id')) {
            DB::table('subjects')
                ->where('year_id', $oldYearId)
                ->update(['academic_year_id' => $newAcademicYearId]);
        }
        
        // You can add more tables with year_id relationships here
    }
}