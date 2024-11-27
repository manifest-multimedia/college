<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\QuestionImport;

class FileUploadController extends Controller
{
    /**
     * Handle the file upload.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:2048', // Adjust size and constraints as needed
        ]);

        $file = $request->file('file');

        // Generate a unique, shorter name for the file
        $filename = uniqid('upload_', true) . '.' . $file->getClientOriginalExtension();

        // Store the file on the 'uploads' disk with the new name
        $path = $file->storeAs('', $filename, 'uploads');

        // Get the full path of the stored file
        $fullPath = Storage::disk('uploads')->path($path);

        // Example: Pass the full path to Excel import
        $exam_id = $request->input('exam_id'); // Example input field for exam ID
        Excel::import(new QuestionImport($exam_id), $fullPath);

        return back()->with('success', 'File uploaded and processed successfully!')
            ->with('file_path', $fullPath);
    }
}
