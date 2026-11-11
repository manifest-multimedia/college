<?php

namespace App\Http\Controllers;

use App\Models\ExamSession;
use App\Models\QuestionAttachment;
use App\Services\Exams\RegisteredExamDeviceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class QuestionMediaController extends Controller
{
    public function show(Request $request, QuestionAttachment $attachment)
    {
        $staff = $request->user()?->hasAnyRole([
            'Lecturer', 'Academic Officer', 'Administrator', 'Super Admin', 'System', 'Finance Manager',
        ]);

        if (! $staff) {
            $token = $request->session()->get('exam_session_token');
            abort_unless(is_string($token) && $token !== '', 403);
            $session = ExamSession::with('exam')->where('session_token', $token)
                ->whereHas('sessionQuestions', fn ($query) => $query->where('question_id', $attachment->question_id))
                ->first();
            abort_unless($session && hash_equals($session->session_token, $token) && $session->started_at
                && (! $session->completed_at || $session->completed_at->isFuture()) && now()->lt($session->adjustedCompletionTime), 403);
            $exam = $session->exam;
            abort_if($exam->status === 'completed'
                || ($exam->start_date && now()->lt($exam->start_date))
                || ($exam->end_date && now()->gt($exam->end_date)), 403);
            abort_unless(app(RegisteredExamDeviceService::class)->allows($exam, $request), 403);
        }

        return $this->file($attachment);
    }

    // Protected by the offline API's existing Bearer-token middleware.
    public function offline(QuestionAttachment $attachment)
    {
        return $this->file($attachment);
    }

    private function file(QuestionAttachment $attachment)
    {
        abort_unless(in_array($attachment->attachment_type, ['image', 'audio', 'video']) && $attachment->file_path, 404);
        $disk = Storage::disk($attachment->storageDisk());
        abort_unless($disk->exists($attachment->file_path), 404);

        // BinaryFileResponse supports HTTP byte ranges for seeking in audio/video.
        return response()->file($disk->path($attachment->file_path), [
            'Content-Type' => $attachment->mime_type ?: 'application/octet-stream',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
