<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentIdentification;
use Illuminate\Support\Facades\Storage;

class StudentIdentificationImageController extends Controller
{
    public function show(Student $student, StudentIdentification $identification)
    {
        abort_unless($identification->student_id === $student->id, 404);
        abort_unless(auth()->user()?->hasRole(['System', 'Super Admin', 'Administrator', 'Academic Officer']), 403);
        abort_unless($identification->image_path && Storage::disk('local')->exists($identification->image_path), 404);

        \Log::info('Student identification image viewed', [
            'student_id' => $student->id,
            'identification_id' => $identification->id,
            'viewed_by' => auth()->id(),
        ]);

        return Storage::disk('local')->response(
            $identification->image_path,
            "{$identification->type_label}-{$student->student_id}",
            [
                'Content-Type' => $identification->image_mime_type ?: 'application/octet-stream',
                'Content-Disposition' => 'inline',
                'Cache-Control' => 'private, no-store',
            ],
        );
    }
}
