<?php

namespace App\Livewire;

use App\Imports\QuestionImport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class QuestionUploadWidget extends Component
{
    use WithFileUploads;

    #[Validate('required|file|mimes:xlsx,csv,ods,tsv|max:10240')]
    public $file;

    public function render()
    {
        return view('livewire.question-upload-widget');
    }

    public function importQuestions()
    {

        try {
            // Store the file on the 'uploads' disk and get its path
            $filePath = $this->bulk_file->store('files', 'uploads');

            // Log the MIME type and stored file path for debugging
            Log::info('File MIME Type:', ['mime' => $this->bulk_file->getMimeType()]);
            Log::info('Stored File Path:', ['path' => $filePath]);

            // Use the correct storage disk path
            $fullPath = Storage::disk('uploads')->path($filePath);

            // Log the full path for debugging
            Log::info('Full File Path for Import:', ['full_path' => $fullPath]);

            // Import the file
            Excel::import(new QuestionImport($this->exam_id), $fullPath);

            // Flash a success message
            session()->flash('message', 'Questions imported successfully.');

            // Reload questions after import
            $this->loadQuestions();
        } catch (\Throwable $e) {
            // Log the error details and flash an error message
            Log::error('Error during import', ['error' => $e->getMessage()]);
            session()->flash('error', 'An error occurred during the import process. Please check the file and try again.');
        }
    }

    // ...

    /*************  ✨ Codeium Command ⭐  *************/
    /**
     * Handle the import of questions via an uploaded file (xlsx, csv, ods, tsv).
     *
     * Validates the uploaded file, imports the questions using the QuestionImport
     * class, and flashes a success message if the import is successful. If an
     * error occurs, it is logged and a flash error is displayed.
     *
     * @return void
     */
    /******  b14fd3b7-45ea-4f23-940b-2ab1b252602b  *******/
    // public function importQuestions()
    // {
    //     // Validate the uploaded file
    //     $this->validate([
    //         'bulk_file' => 'required|mimes:xlsx,csv,ods,tsv',
    //     ]);

    //     try {
    //         // Import the file using its temporary path
    //         Excel::import(new QuestionImport($this->exam_id), $this->bulk_file->path());

    //         // Flash success message
    //         session()->flash('message', 'Questions imported successfully.');
    //         $this->loadQuestions(); // Reload questions after import
    //     } catch (\Throwable $e) {
    //         // Log and handle errors
    //         Log::error('Error during import', ['error' => $e->getMessage()]);
    //         session()->flash('error', 'An error occurred during the import process. Please check the file and try again.');
    //     }
    // }

    /**
     * Determine the Reader Type based on the file extension.
     */
    private function getReaderType(string $extension): ?string
    {
        return match (strtolower($extension)) {
            'xlsx' => \Maatwebsite\Excel\Excel::XLSX,
            'csv' => \Maatwebsite\Excel\Excel::CSV,
            'ods' => \Maatwebsite\Excel\Excel::ODS,
            'tsv' => \Maatwebsite\Excel\Excel::TSV,
            default => null,
        };
    }
}
