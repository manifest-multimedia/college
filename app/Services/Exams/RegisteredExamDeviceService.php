<?php

namespace App\Services\Exams;

use App\Models\Exam;
use App\Models\ExamDevice;
use Illuminate\Http\Request;

class RegisteredExamDeviceService
{
    public const COOKIE = 'college_exam_device';

    public function deviceFor(Request $request): ?ExamDevice
    {
        [$publicId, $secret] = array_pad(explode('.', (string) $request->cookie(self::COOKIE, ''), 2), 2, null);
        if (blank($publicId) || blank($secret)) return null;

        $device = ExamDevice::where('public_id', $publicId)->first();
        if (! $device || ! $device->isActive() || ! hash_equals($device->token_hash, hash('sha256', $secret))) return null;

        $device->forceFill(['last_seen_at' => now()])->saveQuietly();
        return $device;
    }

    public function allows(Exam $exam, Request $request): bool
    {
        if ($exam->device_access_mode !== 'registered_devices_only') return true;
        $device = $this->deviceFor($request);
        if (! $device) return false;
        $allowedTypes = $exam->allowed_device_types ?: [];
        return empty($allowedTypes) || in_array($device->device_type, $allowedTypes, true);
    }
}
