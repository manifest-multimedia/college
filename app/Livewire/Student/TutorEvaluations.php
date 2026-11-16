<?php

namespace App\Livewire\Student;

use App\Models\Student;
use App\Models\TutorEvaluationCampaign;
use App\Services\TutorEvaluations\EvaluationService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class TutorEvaluations extends Component
{
    public ?int $campaignId = null;
    public ?int $subjectId = null;
    public ?int $lecturerId = null;
    public array $answers = [];

    private function currentStudent(): Student
    {
        abort_unless(auth()->user()?->hasRole('Student'), 403);
        return Student::where('email', auth()->user()->email)->firstOrFail();
    }

    public function select(int $campaignId, int $subjectId, int $lecturerId, EvaluationService $service): void
    {
        $student = $this->currentStudent();
        $campaign = TutorEvaluationCampaign::open()->with('semester', 'formVersion')->findOrFail($campaignId);
        abort_unless($service->assignments($student, $campaign)->contains(fn ($a) => (int) $a->subject_id === $subjectId && (int) $a->lecturer_id === $lecturerId), 403);
        abort_if(DB::table('tutor_evaluation_completions')->where('campaign_id', $campaignId)->where('student_id', $student->id)->where('subject_id', $subjectId)->where('lecturer_id', $lecturerId)->exists(), 403);
        $this->campaignId = $campaignId;
        $this->subjectId = $subjectId;
        $this->lecturerId = $lecturerId;
        $this->answers = [];
    }

    public function submit(EvaluationService $service): void
    {
        $student = $this->currentStudent();
        $campaign = TutorEvaluationCampaign::with('semester', 'formVersion')->findOrFail($this->campaignId);
        $service->submit($student, $campaign, (int) $this->subjectId, (int) $this->lecturerId, $this->answers);
        $this->reset('campaignId', 'subjectId', 'lecturerId', 'answers');
        session()->flash('message', 'Evaluation submitted anonymously. Thank you for your feedback.');
    }

    public function render(EvaluationService $service)
    {
        $student = $this->currentStudent();
        $campaigns = TutorEvaluationCampaign::open()->with('semester.academicYear', 'formVersion')->get();
        $completed = DB::table('tutor_evaluation_completions')->where('student_id', $student->id)
            ->get(['campaign_id', 'subject_id', 'lecturer_id']);
        $assignments = $campaigns->map(fn ($campaign) => [
            'campaign' => $campaign,
            'assignments' => $service->assignments($student, $campaign)->map(fn ($a) => [
                'subject_id' => $a->subject_id, 'subject_name' => $a->subject_name,
                'lecturer_id' => $a->lecturer_id, 'lecturer_name' => $a->lecturer_name,
                'completed' => $completed->contains(fn ($c) => (int) $c->campaign_id === $campaign->id && (int) $c->subject_id === (int) $a->subject_id && (int) $c->lecturer_id === (int) $a->lecturer_id),
            ])->all(),
        ])->all();
        $selectedCampaign = $this->campaignId ? $campaigns->firstWhere('id', $this->campaignId) : null;
        $selectedAssignment = null;
        if ($selectedCampaign && $this->subjectId && $this->lecturerId) {
            $selectedAssignment = collect($assignments)->first(fn ($group) => $group['campaign']->id === $selectedCampaign->id);
            $selectedAssignment = collect($selectedAssignment['assignments'] ?? [])->first(fn ($a) => (int) $a['subject_id'] === $this->subjectId && (int) $a['lecturer_id'] === $this->lecturerId);
        }
        return view('livewire.student.tutor-evaluations', compact('assignments', 'selectedCampaign', 'selectedAssignment'))->layout('components.dashboard.default');
    }
}
