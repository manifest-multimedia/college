<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use Carbon\Carbon;

class BackupManagement extends Component
{
    use WithFileUploads;
    
    public $backupFiles = [];
    public $backupInProgress = false;
    public $restoreFile;
    public $lastBackupTime;
    
    protected $listeners = ['deleteConfirmed' => 'deleteBackup'];
    
    public function mount()
    {
        $this->loadBackups();
        $this->getLastBackupTime();
    }
    
    private function loadBackups()
    {
        try {
            $disk = Storage::disk('local');
            $files = $disk->files('backups');
            
            $this->backupFiles = collect($files)
                ->filter(function ($file) {
                    return str_ends_with($file, '.zip');
                })
                ->map(function ($file) use ($disk) {
                    return [
                        'name' => basename($file),
                        'path' => $file,
                        'size' => $this->formatBytes($disk->size($file)),
                        'date' => Carbon::createFromTimestamp($disk->lastModified($file))->format('M d, Y g:i A'),
                        'age' => Carbon::createFromTimestamp($disk->lastModified($file))->diffForHumans(),
                    ];
                })
                ->sortByDesc(function ($file) use ($disk) {
                    return $disk->lastModified($file['path']);
                })
                ->values()
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Error loading backups: ' . $e->getMessage());
            session()->flash('error', 'Failed to load backups.');
        }
    }
    
    private function getLastBackupTime()
    {
        if (count($this->backupFiles) > 0) {
            $this->lastBackupTime = $this->backupFiles[0]['date'];
        } else {
            $this->lastBackupTime = 'No backups found';
        }
    }
    
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
    
    public function createBackup($option = 'db')
    {
        try {
            $this->backupInProgress = true;
            
            if ($option === 'full') {
                Artisan::call('backup:run');
            } else {
                Artisan::call('backup:run --only-db');
            }
            
            $this->backupInProgress = false;
            $this->loadBackups();
            $this->getLastBackupTime();
            
            session()->flash('success', 'Backup created successfully.');
        } catch (\Exception $e) {
            $this->backupInProgress = false;
            Log::error('Error creating backup: ' . $e->getMessage());
            session()->flash('error', 'Failed to create backup. Please try again later.');
        }
    }
    
    public function downloadBackup($path)
    {
        try {
            $filePath = Storage::disk('local')->path($path);
            $fileName = basename($path);
            
            return response()->download($filePath, $fileName);
        } catch (\Exception $e) {
            Log::error('Error downloading backup: ' . $e->getMessage());
            session()->flash('error', 'Failed to download backup file.');
        }
    }
    
    public function confirmDelete($path)
    {
        $this->deleteBackupPath = $path;
        $this->dispatch('showDeleteConfirmation');
    }
    
    public function deleteBackup()
    {
        try {
            Storage::disk('local')->delete($this->deleteBackupPath);
            $this->loadBackups();
            $this->getLastBackupTime();
            
            session()->flash('success', 'Backup file deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting backup: ' . $e->getMessage());
            session()->flash('error', 'Failed to delete backup file.');
        }
    }
    
    // This would be connected to a file upload UI element
    public function uploadRestore()
    {
        $this->validate([
            'restoreFile' => 'required|file|mimes:zip|max:1024000', // 1GB max
        ]);
        
        try {
            $path = $this->restoreFile->store('restores');
            
            // Here you would implement the actual restore logic
            // For safety reasons, this should be done with caution
            
            session()->flash('success', 'Backup file uploaded. Restore process will begin shortly.');
            $this->restoreFile = null;
        } catch (\Exception $e) {
            Log::error('Error uploading restore file: ' . $e->getMessage());
            session()->flash('error', 'Failed to upload backup file for restoration.');
        }
    }
    
    public function render()
    {
        return view('livewire.settings.backup-management');
    }
}