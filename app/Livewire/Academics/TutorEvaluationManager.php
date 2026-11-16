<?php

namespace App\Livewire\Academics;

use App\Models\Semester;
use App\Models\TutorEvaluationCampaign;
use App\Models\TutorEvaluationForm;
use App\Models\TutorEvaluationFormVersion;
use App\Services\TutorEvaluations\EvaluationSchema;
use App\Services\TutorEvaluations\EvaluationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

class TutorEvaluationManager extends Component
{
    public string $name = '';
    public string $description = '';
    public ?int $formId = null;
    public array $sections = [];
    public bool $targetAll = true;
    public array $selectedTargets = [];
    public string $campaignName = '';
    public ?int $semesterId = null;
    public ?int $versionId = null;
    public string $opensAt = '';
    public string $closesAt = '';
    public bool $campaignActive = false;
    public bool $campaignRequired = false;
    public int $reportMinimum = 5;
    public ?int $reportCampaignId = null;
    public ?int $reportLecturerId = null;
    public ?int $reportSubjectId = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['System', 'Super Admin', 'Administrator', 'Academic Officer']), 403);
        $this->sections = [['title' => 'Teaching and Learning', 'questions' => []]];
    }

    public function addSection(): void
    {
        if (count($this->sections) >= 20) return;
        $this->sections[] = ['title' => '', 'questions' => []];
    }

    public function removeSection(int $index): void
    {
        if (count($this->sections) <= 1) return;
        array_splice($this->sections, $index, 1);
    }

    public function addQuestion(int $section): void
    {
        if (! isset($this->sections[$section]) || count($this->sections[$section]['questions']) >= 50) return;
        $this->sections[$section]['questions'][] = [
            'id' => 'q_'.str_replace('-', '', (string) \Illuminate\Support\Str::uuid()),
            'label' => '', 'type' => 'rating', 'required' => true, 'max_rating' => 5, 'options' => [],
        ];
    }

    public function removeQuestion(int $section, int $question): void
    {
        if (isset($this->sections[$section]['questions'][$question])) {
            array_splice($this->sections[$section]['questions'], $question, 1);
        }
    }

    public function loadForm(int $id): void
    {
        $form = TutorEvaluationForm::with('versions')->findOrFail($id);
        $this->formId = $form->id;
        $this->name = $form->name;
        $this->description = $form->description ?? '';
        $this->sections = $form->versions->sortByDesc('version')->first()?->schema ?: [['title' => 'Teaching and Learning', 'questions' => []]];
        foreach ($this->sections as &$section) {
            foreach ($section['questions'] as &$question) {
                $question['optionsText'] = implode("\n", $question['options'] ?? []);
            }
            unset($question);
        }
        unset($section);
    }

    public function resetBuilder(): void
    {
        $this->formId = null;
        $this->name = '';
        $this->description = '';
        $this->sections = [['title' => 'Teaching and Learning', 'questions' => []]];
    }

    public function publish(EvaluationSchema $schema): void
    {
        $this->authorizeManager();
        $this->validate(['name' => 'required|string|max:255', 'description' => 'nullable|string|max:3000']);
        $sections = $this->sections;
        foreach ($sections as &$section) {
            foreach ($section['questions'] as &$question) {
                if (in_array($question['type'], ['single_choice', 'multiple_choice'], true)) {
                    $question['options'] = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', $question['optionsText'] ?? implode("\n", $question['options'] ?? []))), fn ($v) => $v !== ''));
                }
                unset($question['optionsText']);
            }
            unset($question);
        }
        unset($section);
        $sections = $schema->validate($sections);
        // New versions never mutate an already-administered form.
        DB::transaction(function () use ($sections) {
            $form = $this->formId ? TutorEvaluationForm::query()->lockForUpdate()->findOrFail($this->formId) : TutorEvaluationForm::create([
                'name' => $this->name, 'description' => $this->description, 'created_by' => auth()->id(),
            ]);
            if ($this->formId) $form->update(['name' => $this->name, 'description' => $this->description]);
            $next = ((int) $form->versions()->max('version')) + 1;
            TutorEvaluationFormVersion::create(['form_id' => $form->id, 'version' => $next, 'schema' => $sections, 'published_at' => now()]);
            $this->formId = $form->id;
        });
        session()->flash('message', 'Evaluation form version published. Previous versions remain unchanged.');
    }

    public function createCampaign(): void
    {
        $this->authorizeManager();
        $data = $this->validate([
            'campaignName' => 'required|string|max:255',
            'semesterId' => 'required|exists:semesters,id',
            'versionId' => 'required|exists:tutor_evaluation_form_versions,id',
            'opensAt' => 'nullable|date',
            'closesAt' => 'nullable|date|after:opensAt',
            'campaignActive' => 'boolean',
            'campaignRequired' => 'boolean',
            'reportMinimum' => 'required|integer|min:5|max:100',
        ]);
        $pairs = app(EvaluationService::class)->eligiblePairs((int) $data['semesterId']);
        $allowed = $pairs->map(fn ($pair) => $pair->subject_id.':'.$pair->lecturer_id)->all();
        $selected = array_values(array_unique($this->selectedTargets));
        if (! $this->targetAll && (! count($selected) || array_diff($selected, $allowed))) {
            throw \Illuminate\Validation\ValidationException::withMessages(['selectedTargets' => 'Select at least one eligible course/tutor combination.']);
        }
        DB::transaction(function () use ($data, $selected) {
        $campaign = TutorEvaluationCampaign::create([
            'name' => $data['campaignName'], 'semester_id' => $data['semesterId'], 'form_version_id' => $data['versionId'],
            'opens_at' => $data['opensAt'] ?: null, 'closes_at' => $data['closesAt'] ?: null,
            'is_active' => $data['campaignActive'], 'is_required' => $data['campaignRequired'],  'report_minimum' => $data['reportMinimum'], 'target_all' => $this->targetAll,
        ]);
        if (! $this->targetAll) {
            foreach ($selected as $pair) {
                [$subjectId, $lecturerId] = array_map('intval', explode(':', $pair));
                DB::table('tutor_evaluation_campaign_targets')->insert([
                    'campaign_id' => $campaign->id, 'subject_id' => $subjectId, 'lecturer_id' => $lecturerId,
                ]);
            }
        }
        });
        $this->reset('campaignName', 'semesterId', 'versionId', 'opensAt', 'closesAt', 'campaignActive', 'campaignRequired', 'targetAll', 'selectedTargets');
        session()->flash('message', 'Semester evaluation campaign created.');
    }

    public function toggleCampaign(int $id): void
    {
        $this->authorizeManager();
        $campaign = TutorEvaluationCampaign::findOrFail($id);
        $campaign->update(['is_active' => ! $campaign->is_active]);
    }

    private function authorizeManager(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['System', 'Super Admin', 'Administrator', 'Academic Officer']), 403);
    }

    public function render()
    {
        $this->authorizeManager();
        $campaigns = TutorEvaluationCampaign::with('semester.academicYear', 'formVersion.form')->latest()->get();
        $report = null;
        if ($this->reportCampaignId && $this->reportLecturerId && $this->reportSubjectId) {
            $campaign = $campaigns->firstWhere('id', (int) $this->reportCampaignId);
            if ($campaign) {
                $responses = DB::table('tutor_evaluation_responses')->where('campaign_id', $campaign->id)
                    ->where('lecturer_id', $this->reportLecturerId)->where('subject_id', $this->reportSubjectId)->get();
                $completionCount = DB::table('tutor_evaluation_completions')->where('campaign_id', $campaign->id)
                    ->where('lecturer_id', $this->reportLecturerId)->where('subject_id', $this->reportSubjectId)->count();
                $statistics = [];
                if ($responses->count() >= $campaign->report_minimum) {
                    foreach ($campaign->formVersion->schema as $section) {
                        foreach ($section['questions'] as $question) {
                            if ($question['type'] !== 'rating') continue;
                            $scores = $responses->map(fn ($r) => json_decode($r->answers, true)[$question['id']] ?? null)->filter(fn ($v) => is_numeric($v));
                            $statistics[] = ['label' => $question['label'], 'average' => $scores->isNotEmpty() ? round($scores->avg(), 2) : null, 'count' => $scores->count()];
                        }
                    }
                }
                $report = ['count' => $completionCount, 'visible' => $responses->count() >= $campaign->report_minimum, 'minimum' => $campaign->report_minimum, 'statistics' => $statistics];
            }
        }
        $eligiblePairs = $this->semesterId ? app(EvaluationService::class)->eligiblePairs((int) $this->semesterId) : collect();
        return view('livewire.academics.tutor-evaluation-manager', [
            'eligiblePairs' => $eligiblePairs,
            'forms' => TutorEvaluationForm::with('versions')->latest()->get(),
            'versions' => TutorEvaluationFormVersion::with('form')->latest()->get(),
            'semesters' => Semester::with('academicYear')->latest()->get(),
            'campaigns' => $campaigns,
            'report' => $report,
        ])->layout('components.dashboard.default');
    }
}
