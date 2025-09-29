<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BackupQuestionBankData extends Command
{
    protected $signature = 'questionbank:backup {--path= : Custom backup path}';
    protected $description = 'Backup question bank data before migration';

    public function handle()
    {
        $this->info('🔄 Starting Question Bank Migration Backup...');
        
        // Create backup directory
        $timestamp = Carbon::now()->format('Ymd_His');
        $backupPath = $this->option('path') ?: "backups/questionbank_migration_{$timestamp}";
        
        if (!Storage::exists($backupPath)) {
            Storage::makeDirectory($backupPath);
        }
        
        $this->info("📁 Backup Directory: storage/app/{$backupPath}");
        
        // Tables to backup
        $tables = ['questions', 'options', 'exams', 'exam_sessions', 'responses', 'subjects', 'users', 'students'];
        
        $summary = [
            'backup_date' => Carbon::now()->toDateTimeString(),
            'backup_path' => $backupPath,
            'tables' => []
        ];
        
        foreach ($tables as $table) {
            $this->info("📋 Backing up table: {$table}");
            
            try {
                // Get table data
                $data = DB::table($table)->get();
                $count = $data->count();
                
                // Save as JSON
                Storage::put("{$backupPath}/{$table}_backup.json", $data->toJson(JSON_PRETTY_PRINT));
                
                $summary['tables'][$table] = [
                    'record_count' => $count,
                    'backup_file' => "{$table}_backup.json",
                    'status' => 'success'
                ];
                
                $this->info("  ✅ {$table}: {$count} records backed up");
                
            } catch (\Exception $e) {
                $this->error("  ❌ Error backing up {$table}: " . $e->getMessage());
                $summary['tables'][$table] = [
                    'record_count' => 0,
                    'backup_file' => null,
                    'status' => 'error',
                    'error' => $e->getMessage()
                ];
            }
        }
        
        // Save backup summary
        Storage::put("{$backupPath}/backup_summary.json", json_encode($summary, JSON_PRETTY_PRINT));
        
        // Create restoration command
        $restoreCommand = $this->createRestoreCommand($backupPath);
        Storage::put("{$backupPath}/restore_command.txt", $restoreCommand);
        
        $this->info('✅ Backup completed successfully!');
        $this->info("📁 Backup location: storage/app/{$backupPath}");
        $this->info("🔧 To restore: php artisan questionbank:restore --path={$backupPath}");
        
        return 0;
    }
    
    private function createRestoreCommand($backupPath)
    {
        return "To restore this backup, run:\nphp artisan questionbank:restore --path={$backupPath}\n\nThis will restore all question bank data to the state it was in when this backup was created.";
    }
}