<?php

namespace App\Livewire\Concerns;

use App\Models\Student;
use App\Services\StudentEmergencyContactService;
use Illuminate\Support\Str;

trait HasNewStudentEmergencyContacts
{
    public array $emergencyContacts = [];

    public function addEmergencyContact(): void
    {
        $this->emergencyContacts[(string) Str::uuid()] = array_fill_keys(array_keys(StudentEmergencyContactService::FIELDS), '');
    }

    public function removeEmergencyContact(string $key): void
    {
        unset($this->emergencyContacts[$key]);
        $this->resetValidation('emergencyContacts.'.$key);
    }

    protected function validateEmergencyContacts(): void
    {
        $service = app(StudentEmergencyContactService::class);
        foreach ($this->emergencyContacts as $key => $attributes) {
            $this->emergencyContacts[$key] = $service->clean($attributes);
        }
        $rules = ['emergencyContacts' => 'array'];
        foreach ($service::rules() as $field => $rule) {
            $rules['emergencyContacts.*.'.$field] = $rule;
        }
        $this->validate($rules);
    }

    protected function saveEmergencyContacts(Student $student): void
    {
        $service = app(StudentEmergencyContactService::class);
        foreach ($this->emergencyContacts as $attributes) {
            $service->save($student, $attributes);
        }
    }
}
