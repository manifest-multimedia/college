<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentIdentification;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class StudentIdentificationService
{
    public function save(Student $student, array $attributes, ?UploadedFile $image = null, ?StudentIdentification $identification = null): StudentIdentification
    {
        $type = $attributes['identification_type'];
        $identifier = $this->canonicalIdentifier($attributes['identifier']);
        $this->validateIdentifier($type, $identifier);

        $hash = $this->identifierHash($type, $identifier);
        $duplicate = StudentIdentification::query()
            ->where('identification_type', $type)
            ->where('identifier_hash', $hash)
            ->when($identification, fn ($query) => $query->whereKeyNot($identification->id))
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'identifierNumber' => 'This identification number is already assigned to another student.',
            ]);
        }

        $identification ??= new StudentIdentification(['student_id' => $student->id]);
        $oldImagePath = $identification->image_path;

        $identification->fill([
            'identification_type' => $type,
            'identifier' => $identifier,
            'identifier_hash' => $hash,
            'issuing_country' => strtoupper((string) ($attributes['issuing_country'] ?? '')) ?: null,
            'issuing_authority' => $attributes['issuing_authority'] ?? null,
            'expires_on' => $attributes['expires_on'] ?? null,
            'verification_status' => ! empty($attributes['mark_verified']) ? 'verified' : 'unverified',
            'verified_at' => ! empty($attributes['mark_verified']) ? now() : null,
            'verified_by' => ! empty($attributes['mark_verified']) ? auth()->id() : null,
        ]);

        $newImagePath = null;
        try {
            if ($image) {
                $newImagePath = $image->store("student-identifications/{$student->id}", 'local');
                $identification->image_path = $newImagePath;
                $identification->image_mime_type = $image->getMimeType();
                $identification->image_size = $image->getSize();
            }

            $identification->save();
        } catch (\Throwable $exception) {
            if ($newImagePath) {
                Storage::disk('local')->delete($newImagePath);
            }

            throw $exception;
        }

        if ($image && $oldImagePath && $oldImagePath !== $identification->image_path) {
            Storage::disk('local')->delete($oldImagePath);
        }

        Log::info('Student identification saved', [
            'student_id' => $student->id,
            'identification_id' => $identification->id,
            'identification_type' => $type,
            'changed_by' => auth()->id(),
        ]);

        return $identification;
    }

    public function delete(StudentIdentification $identification): void
    {
        $studentId = $identification->student_id;
        $imagePath = $identification->image_path;
        $identification->delete();

        if ($imagePath) {
            Storage::disk('local')->delete($imagePath);
        }

        Log::warning('Student identification deleted', [
            'student_id' => $studentId,
            'identification_id' => $identification->id,
            'changed_by' => auth()->id(),
        ]);
    }

    public function canonicalIdentifier(string $identifier): string
    {
        return strtoupper(trim(preg_replace('/\s+/', '', $identifier)));
    }

    private function identifierHash(string $type, string $identifier): string
    {
        return hash_hmac('sha256', "{$type}|{$identifier}", (string) config('app.key'));
    }

    private function validateIdentifier(string $type, string $identifier): void
    {
        if ($type === 'ghana_card' && ! preg_match('/^GHA-\d{9}-\d$/', $identifier)) {
            throw ValidationException::withMessages([
                'identifierNumber' => 'Enter a Ghana Card number in the format GHA-123456789-0.',
            ]);
        }
    }
}
