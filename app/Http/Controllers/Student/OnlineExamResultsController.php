<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ExamSession;
use App\Services\Exams\ExamResultVisibilityService;
use App\Services\ResultsService;
use Illuminate\Http\Request;

class OnlineExamResultsController extends Controller
{
    public function show(Request $request, ExamSession $session, ExamResultVisibilityService $visibility, ResultsService $results)
    {
        abort_unless($request->user() && (int) $session->student_id === (int) $request->user()->id, 403);
        abort_unless($session->completed_at && $session->completed_at->isPast() && ! $session->is_restored, 404);
        $session->load('exam.course');
        $visible = $visibility->canView($session);
        $result = $visible ? $results->calculateOnlineExamScore($session) : null;
        $policy = $visibility->policy($session->exam);
        return view('student.online-exam-result', compact('session', 'visible', 'result', 'policy'));
    }
}
