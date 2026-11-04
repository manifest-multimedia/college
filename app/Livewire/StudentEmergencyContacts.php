<?php

namespace App\Livewire;

use App\Models\Student;
use App\Models\StudentEmergencyContact;
use App\Services\StudentEmergencyContactService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

class StudentEmergencyContacts extends Component
{
    #[Locked]
    public int $studentId;

    #[Locked]
    public ?int $editingContactId = null;

    public array $contact = [];

    public bool $showForm = false;

    public function mount(int $studentId): void
    {
        $this->studentId = $studentId;
        $this->authorizedStudent();
    }

    public function addContact(): void
    {
        $this->authorizedStudent();
        $this->cancel();
        $this->showForm = true;
    }

    public function editContact(int $contactId): void
    {
        $contact = $this->contactForStudent($contactId);
        $this->resetValidation();
        $this->editingContactId = $contact->id;
        $this->contact = $contact->only(array_keys(StudentEmergencyContactService::FIELDS));
        $this->showForm = true;
    }

    public function saveContact(StudentEmergencyContactService $service): void
    {
        $student = $this->authorizedStudent();
        $existing = $this->editingContactId ? $this->contactForStudent($this->editingContactId) : null;
        $this->contact = $service->clean($this->contact);
        $rules = [];
        foreach ($service::rules() as $field => $rule) {
            $rules['contact.'.$field] = $rule;
        }
        $this->validate($rules);
        $service->save($student, $this->contact, $existing);
        $this->cancel();
        session()->flash('emergency_contact_status', 'Emergency contact saved.');
    }

    public function deleteContact(int $contactId, StudentEmergencyContactService $service): void
    {
        $service->delete($this->contactForStudent($contactId));
        if ($this->editingContactId === $contactId) {
            $this->cancel();
        }
        session()->flash('emergency_contact_status', 'Emergency contact removed.');
    }

    public function cancel(): void
    {
        $this->reset(['editingContactId', 'contact', 'showForm']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.student-emergency-contacts', [
            'contacts' => $this->authorizedStudent()->emergencyContacts()->orderBy('id')->get(),
        ]);
    }

    private function authorizedStudent(): Student
    {
        $student = Student::findOrFail($this->studentId);
        Gate::authorize('manage-student-emergency-contacts', $student);

        return $student;
    }

    private function contactForStudent(int $contactId): StudentEmergencyContact
    {
        return $this->authorizedStudent()->emergencyContacts()->findOrFail($contactId);
    }
}
