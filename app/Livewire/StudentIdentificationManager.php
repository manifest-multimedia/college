<?php

namespace App\Livewire;

use App\Models\Student;
use App\Models\StudentIdentification;
use App\Services\StudentIdentificationService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;

class StudentIdentificationManager extends Component
{
    use WithFileUploads;

    public int $studentId;

    public ?int $editingIdentificationId = null;

    public string $identificationType = 'ghana_card';

    public string $identifierNumber = '';

    public string $issuingCountry = 'GH';

    public string $issuingAuthority = '';

    public ?string $expiresOn = null;

    public bool $markVerified = false;

    public $cardImage = null;

    public bool $showForm = false;

    public array $identificationTypes = [];

    protected function rules(): array
    {
        return [
            'identificationType' => 'required|in:'.implode(',', array_keys(StudentIdentification::TYPES)),
            'identifierNumber' => 'required|string|max:100',
            'issuingCountry' => 'nullable|string|size:2',
            'issuingAuthority' => 'nullable|string|max:150',
            'expiresOn' => 'nullable|date',
            'markVerified' => 'boolean',
            'cardImage' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ];
    }

    public function mount(int $studentId): void
    {
        abort_unless($this->canManageIdentifications(), 403);

        $this->studentId = $studentId;
        $this->identificationTypes = StudentIdentification::TYPES;
    }

    public function createIdentification(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function editIdentification(int $identificationId): void
    {
        $identification = $this->identificationForStudent($identificationId);

        $this->editingIdentificationId = $identification->id;
        $this->identificationType = $identification->identification_type;
        $this->identifierNumber = $identification->identifier;
        $this->issuingCountry = $identification->issuing_country ?? '';
        $this->issuingAuthority = $identification->issuing_authority ?? '';
        $this->expiresOn = $identification->expires_on?->format('Y-m-d');
        $this->markVerified = $identification->verification_status === 'verified';
        $this->cardImage = null;
        $this->showForm = true;
    }

    public function saveIdentification(StudentIdentificationService $service): void
    {
        $this->validate();

        try {
            $student = Student::findOrFail($this->studentId);
            $identification = $this->editingIdentificationId
                ? $this->identificationForStudent($this->editingIdentificationId)
                : null;

            $service->save($student, [
                'identification_type' => $this->identificationType,
                'identifier' => $this->identifierNumber,
                'issuing_country' => $this->issuingCountry,
                'issuing_authority' => $this->issuingAuthority,
                'expires_on' => $this->expiresOn,
                'mark_verified' => $this->markVerified,
            ], $this->cardImage, $identification);

            $this->resetForm();
            session()->flash('success', 'Identification saved securely.');
        } catch (\Illuminate\Validation\ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            Log::error('Unable to save student identification', [
                'student_id' => $this->studentId,
                'changed_by' => auth()->id(),
                'error' => $exception->getMessage(),
            ]);
            session()->flash('error', 'The identification could not be saved. Please try again.');
        }
    }

    public function deleteIdentification(int $identificationId, StudentIdentificationService $service): void
    {
        $service->delete($this->identificationForStudent($identificationId));
        session()->flash('success', 'Identification removed.');
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    public function render()
    {
        return view('livewire.student-identification-manager', [
            'identifications' => StudentIdentification::where('student_id', $this->studentId)
                ->with('verifier:id,name')
                ->latest()
                ->get(),
        ]);
    }

    private function identificationForStudent(int $identificationId): StudentIdentification
    {
        return StudentIdentification::where('student_id', $this->studentId)->findOrFail($identificationId);
    }

    private function resetForm(): void
    {
        $this->reset(['editingIdentificationId', 'identifierNumber', 'issuingAuthority', 'expiresOn', 'cardImage', 'showForm']);
        $this->identificationType = 'ghana_card';
        $this->issuingCountry = 'GH';
        $this->markVerified = false;
        $this->resetValidation();
    }

    private function canManageIdentifications(): bool
    {
        return auth()->check() && auth()->user()->hasRole(['System', 'Super Admin', 'Administrator', 'Academic Officer']);
    }
}
