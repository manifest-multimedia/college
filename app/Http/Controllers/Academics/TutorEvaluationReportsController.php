<?php

namespace App\Http\Controllers\Academics;

use App\Http\Controllers\Controller;
use App\Models\TutorEvaluationCampaign;
use App\Services\TutorEvaluations\ReportingService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TutorEvaluationReportsController extends Controller
{
    private function data(Request $request, ReportingService $service): array
    {
        abort_unless($request->user()?->hasAnyRole(['System', 'Super Admin', 'Administrator', 'Academic Officer']), 403);
        $validated = $request->validate([
            'campaign' => 'nullable|integer|exists:tutor_evaluation_campaigns,id',
            'lecturer' => 'nullable|integer|exists:users,id',
            'course' => 'nullable|integer|exists:subjects,id',
        ]);
        $campaigns = TutorEvaluationCampaign::with('semester.academicYear')->latest()->get();
        $campaign = isset($validated['campaign']) ? $campaigns->firstWhere('id', $validated['campaign']) : $campaigns->first();
        $lecturerId = $validated['lecturer'] ?? null;
        $subjectId = $validated['course'] ?? null;
        $report = $campaign ? $service->report($campaign, $lecturerId, $subjectId) : null;
        $lecturers = \Illuminate\Support\Facades\DB::table('users as u')->join('course_lecturer as cl', 'cl.user_id', '=', 'u.id')->select('u.id', 'u.name')->distinct()->orderBy('u.name')->get();
        $courses = \Illuminate\Support\Facades\DB::table('subjects')->select('id', 'name')->orderBy('name')->get();
        $overview = $service->overview($campaigns);
        return compact('campaigns', 'campaign', 'report', 'lecturerId', 'subjectId', 'lecturers', 'courses', 'overview');
    }

    public function index(Request $request, ReportingService $service)
    {
        return view('academics.tutor-evaluation-reports', $this->data($request, $service));
    }

    public function pdf(Request $request, ReportingService $service)
    {
        $data = $this->data($request, $service);
        abort_unless($data['campaign'], 404);
        return Pdf::loadView('academics.tutor-evaluation-reports-pdf', $data)
            ->download('tutor-assessment-report.pdf');
    }

    public function xlsx(Request $request, ReportingService $service)
    {
        $data = $this->data($request, $service);
        abort_unless($data['campaign'], 404);
        $sheet = new Spreadsheet();
        $tab = $sheet->getActiveSheet();
        $tab->setTitle('Tutor Assessments');
        $tab->fromArray(['Course', 'Lecturer', 'Responses', 'Eligible', 'Question', 'Type', 'Average', 'Distribution'], null, 'A1');
        $row = 2;
        foreach ($data['report']['pairs'] as $pair) {
            $questions = $data['report']['details'][$pair['lecturer_id'].':'.$pair['subject_id']] ?? [];
            if (! $questions) {
                $tab->fromArray([$pair['course'], $pair['lecturer'], $pair['responses'], $pair['eligible'], 'Withheld (privacy threshold)'], null, 'A'.$row++);
            }
            foreach ($questions as $question) {
                $tab->fromArray([$pair['course'], $pair['lecturer'], $pair['responses'], $pair['eligible'], $question['label'], $question['type'], $question['average'] ?? '', json_encode($question['distribution'] ?? [])], null, 'A'.$row++);
            }
        }
        $writer = new Xlsx($sheet);
        $path = tempnam(sys_get_temp_dir(), 'tutor-report-');
        try {
            $writer->save($path);
            return response()->download($path, 'tutor-assessment-report.xlsx')->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            @unlink($path);
            throw $e;
        }
    }

    public function csv(Request $request, ReportingService $service)
    {
        $data = $this->data($request, $service);
        abort_unless($data['campaign'], 404);
        return response()->streamDownload(function () use ($data) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Course', 'Lecturer', 'Responses', 'Eligible', 'Question', 'Type', 'Average', 'Distribution']);
            foreach ($data['report']['pairs'] as $pair) {
                $questions = $data['report']['details'][$pair['lecturer_id'].':'.$pair['subject_id']] ?? [];
                if (! $questions) {
                    fputcsv($out, [$pair['course'], $pair['lecturer'], $pair['responses'], $pair['eligible'], 'Withheld (privacy threshold)', '', '', '']);
                }
                foreach ($questions as $question) {
                    fputcsv($out, [$pair['course'], $pair['lecturer'], $pair['responses'], $pair['eligible'],
                        $question['label'], $question['type'], $question['average'] ?? '',
                        json_encode($question['distribution'] ?? [])]);
                }
            }
            fclose($out);
        }, 'tutor-assessment-report.csv', ['Content-Type' => 'text/csv']);
    }
}
