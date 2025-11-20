<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RestoreQuestionBankData extends Command
{
    protected $signature = 'questionbank:restore {--path= : Backup path to restore from}';

    protected $description = 'Restore question bank data from backup';

    public function handle()
    {
        $backupPath = $this->option('path');

        if (! $backupPath) {
            $this->error('❌ Please specify the backup path using --path option');

            return 1;
        }

        if (! Storage::exists($backupPath)) {
            $this->error("❌ Backup path not found: {$backupPath}");

            return 1;
        }

        $this->warn('⚠️  WARNING: This will overwrite current data!');

        if (! $this->confirm('Are you sure you want to continue?')) {
            $this->info('❌ Restoration cancelled');

            return 0;
        }

        $this->info('🔄 Starting Question Bank Data Restoration...');

        // Get backup summary
        if (Storage::exists("{$backupPath}/backup_summary.json")) {
            $summary = json_decode(Storage::get("{$backupPath}/backup_summary.json"), true);
            $this->info("📅 Backup Date: {$summary['backup_date']}");
        }

        // Tables to restore (in dependency order)
        $tables = ['users', 'subjects', 'students', 'exams', 'questions', 'options', 'exam_sessions', 'responses'];

        foreach ($tables as $table) {
            $backupFile = "{$backupPath}/{$table}_backup.json";

            if (! Storage::exists($backupFile)) {
                $this->warn("⚠️  Backup file not found for table: {$table}");

                continue;
            }

            $this->info("📋 Restoring table: {$table}");

            try {
                // Get backup data
                $data = json_decode(Storage::get($backupFile), true);

                if (empty($data)) {
                    $this->info("  📭 No data to restore for {$table}");

                    continue;
                }

                // Disable foreign key checks temporarily
                DB::statement('SET FOREIGN_KEY_CHECKS=0');

                // Clear existing data
                DB::table($table)->truncate();

                // Restore data in chunks
                $chunks = array_chunk($data, 1000);
                foreach ($chunks as $chunk) {
                    DB::table($table)->insert($chunk);
                }

                // Re-enable foreign key checks
                DB::statement('SET FOREIGN_KEY_CHECKS=1');

                $count = count($data);
                $this->info("  ✅ {$table}: {$count} records restored");

            } catch (\Exception $e) {
                $this->error("  ❌ Error restoring {$table}: ".$e->getMessage());
            }
        }

        $this->info('✅ Restoration completed!');

        return 0;
    }
}
