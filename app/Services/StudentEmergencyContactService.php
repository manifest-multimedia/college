<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentEmergencyContact;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class StudentEmergencyContactService
{
    public const FIELDS = [
        'full_name' => 'Full Name',
        'relationship' => 'Relationship',
        'mobile_number' => 'Mobile Number',
        'alternate_phone' => 'Alternate Phone',
        'email' => 'Email',
        'gps_address' => 'GPS Address',
        'residential_address' => 'Residential Address',
    ];

    public static function rules(): array
    {
        return [
            'full_name' => 'required|string|max:255',
            'relationship' => 'required|string|max:100',
            'mobile_number' => ['required', 'string', 'max:30', 'regex:/^\+?[0-9][0-9\s().-]{5,29}$/'],
            'alternate_phone' => ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9][0-9\s().-]{5,29}$/'],
            'email' => 'nullable|email|max:255',
            'gps_address' => 'nullable|string|max:255',
            'residential_address' => 'nullable|string|max:2000',
        ];
    }

    public function clean(array $attributes): array
    {
        $clean = [];
        foreach (self::FIELDS as $field => $label) {
            $value = $attributes[$field] ?? null;
            if (is_string($value)) {
                $value = trim($value);
                if ($field === 'email') {
                    $value = preg_replace('/\s+/', '', $value);
                }
            }
            $clean[$field] = $value === '' ? null : $value;
        }

        return $clean;
    }

    public function save(Student $student, array $attributes, ?StudentEmergencyContact $contact = null): StudentEmergencyContact
    {
        abort_if($contact && (int) $contact->student_id !== (int) $student->id, 404);
        $data = Validator::make($this->clean($attributes), self::rules())->validate();
        $contact ??= $student->emergencyContacts()->make();
        $contact->fill($data)->save();
        Log::info('Student emergency contact saved', [
            'student_id' => $student->id, 'contact_id' => $contact->id, 'changed_by' => auth()->id(),
        ]);

        return $contact;
    }

    public function delete(StudentEmergencyContact $contact): void
    {
        $contact->delete();
        Log::info('Student emergency contact removed', [
            'student_id' => $contact->student_id, 'contact_id' => $contact->id, 'changed_by' => auth()->id(),
        ]);
    }

    public static function importFields(): array
    {
        $fields = [];
        foreach (range(1, 5) as $slot) {
            foreach (self::FIELDS as $field => $label) {
                $fields[self::importKey($slot, $field)] = 'Emergency Contact '.($slot === 1 ? '' : $slot.' ').$label;
            }
        }

        return $fields;
    }

    public static function importKey(int $slot, string $field): string
    {
        return 'emergency_'.($slot === 1 ? '' : $slot.'_').$field;
    }

    public function extractImportedContacts(array &$data): array
    {
        $contacts = [];
        foreach (range(1, 5) as $slot) {
            $attributes = [];
            foreach (self::FIELDS as $field => $label) {
                $key = self::importKey($slot, $field);
                $attributes[$field] = $data[$key] ?? null;
                unset($data[$key]);
            }
            $attributes = $this->clean($attributes);
            if (array_filter($attributes, fn ($value) => $value !== null) !== []) {
                $contacts[$slot] = $attributes;
            }
        }

        return $contacts;
    }

    public function import(Student $student, array $contacts): void
    {
        if ($contacts === []) {
            return;
        }
        DB::transaction(function () use ($student, $contacts) {
            // Serialize contact imports for this student, including simultaneous retries.
            Student::whereKey($student->id)->lockForUpdate()->firstOrFail();
            foreach ($contacts as $slot => $attributes) {
                $contact = $student->emergencyContacts()->where('import_slot', $slot)->first();
                $contact ??= $student->emergencyContacts()->whereNull('import_slot')
                    ->where('full_name', $attributes['full_name'])->where('mobile_number', $attributes['mobile_number'])->first();
                $attributes = array_filter($attributes, fn ($value) => $value !== null);
                $contact = $this->save($student, $attributes + ($contact?->only(array_keys(self::FIELDS)) ?? []), $contact);
                $contact->import_slot = $slot;
                $contact->save();
            }
        });
    }
}
