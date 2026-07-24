<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Carbon\Carbon;

class ExportDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:export';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export the MySQL database to a public directory for local download';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting database export...');

        // Database credentials from config
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $database = config('database.connections.mysql.database');

        // File path setup
        $date = Carbon::now()->format('Y-m-d_H-i-s');
        $filename = "database_export_{$date}.sql";
        $storagePath = storage_path("app/public/{$filename}");

        // Ensure the directory exists
        if (!file_exists(storage_path('app/public'))) {
            mkdir(storage_path('app/public'), 0755, true);
        }

        // Build mysqldump command
        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s %s > %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($storagePath)
        );

        $this->info("Running mysqldump...");

        // Using exec instead of Process for simpler redirect handling
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            $this->error('Failed to export the database.');
            $this->error('Make sure "mysqldump" is installed and accessible in your system path.');
            return Command::FAILURE;
        }

        $this->info('Database successfully exported!');
        $this->newLine();
        
        $downloadUrl = asset("storage/{$filename}");
        
        $this->info("You can download the database file here:");
        $this->comment($downloadUrl);
        $this->newLine();
        $this->warn("IMPORTANT: Please delete this file from your storage folder after downloading it for security reasons!");

        return Command::SUCCESS;
    }
}
