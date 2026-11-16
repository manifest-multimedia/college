<?php

namespace App\Http\Controllers\Academics;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OnlineExamResultPolicyController extends Controller
{
    public function index()
    {
        return view('academics.online-exam-result-policy', [
            'policy' => DB::table('online_exam_result_policies')->first(),
            'exams' => Exam::query()->with(['course', 'examType'])->orderByDesc('id')->limit(200)->get(),
        ]);
    }
    public function save(Request $request)
    {
        $data = $request->validate([
            'enabled' => 'required|boolean',
            'release_mode' => 'required|in:immediate,after_end,manual',
            'detail_level' => 'required|in:score,summary',
        ]);
        DB::table('online_exam_result_policies')->updateOrInsert(['id' => 1], $data + ['updated_at' => now(), 'created_at' => now()]);
        return back()->with('message', 'Online examination results policy saved.');
    }
    public function override(Request $request, Exam $exam)
    {
        $data = $request->validate([
            'result_release_override' => 'nullable|in:disabled,immediate,after_end,manual',
            'result_detail_override' => 'nullable|in:score,summary',
        ]);
        $exam->update($data);
        return back()->with('message', 'Examination override saved.');
    }
    public function publish(Exam $exam)
    {
        $exam->update(['results_published_at' => now()]);
        return back()->with('message', 'Examination results released.');
    }
}
