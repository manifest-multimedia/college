<?php
namespace App\Services\Hostels;

use App\Models\Hostel\{HostelAllocationPeriod,HostelBed};
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HostelBulkAllocationService
{
    public function plan(array $studentIds, int $periodId, int $hostelId): array
    {
        $period = HostelAllocationPeriod::findOrFail($periodId);
        if ($period->status !== 'open' || $period->ends_on->lt(today())) {
            throw ValidationException::withMessages(['period' => 'Select an open allocation period.']);
        }
        $students = Student::whereIn('id', $studentIds)->active()->orderBy('id')->get();
        if ($students->count() !== count(array_unique($studentIds))) {
            throw ValidationException::withMessages(['students' => 'One or more students are inactive or unavailable.']);
        }
        $beds = HostelBed::query()->join('hostel_rooms as r', 'r.id', '=', 'hostel_beds.hostel_room_id')
            ->join('hostels as h', 'h.id', '=', 'r.hostel_id')
            ->where('h.id', $hostelId)->where('h.status', 'active')
            ->where('r.status', 'active')->where('hostel_beds.status', 'active')
            ->orderBy('r.room_number')->orderBy('hostel_beds.bed_code')
            ->select('hostel_beds.id', 'hostel_beds.bed_code', 'r.room_number', 'h.gender_policy')->get();
        $periodIds = HostelAllocationPeriod::where('starts_on', '<=', $period->ends_on)
            ->where('ends_on', '>=', $period->starts_on)->pluck('id');
        $occupied = DB::table('hostel_allocations')->whereIn('hostel_allocation_period_id', $periodIds)
            ->whereIn('status', ['allocated', 'checked_in']);
        $takenBeds = (clone $occupied)->pluck('hostel_bed_id')->all();
        $allocatedStudents = (clone $occupied)->pluck('student_id')->all();
        $available = $beds->whereNotIn('id', $takenBeds)->values();
        $settings = \App\Models\Hostel\HostelSetting::first();
        $clearance = app(HostelPaymentClearanceService::class);
        $plan = [];
        foreach ($students as $student) {
            $reason = null;
            $bed = null;
            if (in_array($student->id, $allocatedStudents)) $reason = 'Already allocated in an overlapping period';
            elseif (! $settings?->enabled) $reason = 'Hostel module is disabled';
            elseif (! $clearance->isCleared($student->id, $periodId)) $reason = 'Payment clearance pending';
            else {
                $bed = $available->first(function ($candidate) use ($student, $settings) {
                    if (! $settings->enforce_gender || $candidate->gender_policy === 'mixed') return true;
                    return in_array(strtolower(trim((string) $student->gender)), $candidate->gender_policy === 'male' ? ['male','m'] : ['female','f'], true);
                });
                if (! $bed) $reason = 'No eligible vacant bed';
                else $available = $available->reject(fn ($candidate) => $candidate->id === $bed->id)->values();
            }
            $plan[] = ['student_id' => $student->id, 'student_name' => trim("{$student->first_name} {$student->last_name}"),
                'student_number' => $student->student_id, 'bed_id' => $bed?->id,
                'bed_label' => $bed ? "{$bed->room_number} / {$bed->bed_code}" : null, 'reason' => $reason];
        }
        return $plan;
    }

    public function execute(array $plan, int $periodId, int $actorId): array
    {
        $results = ['allocated' => 0, 'skipped' => []];
        foreach ($plan as $row) {
            if (! $row['bed_id']) {
                $results['skipped'][] = $row['student_number'].': '.$row['reason'];
                continue;
            }
            try {
                app(AllocationService::class)->allocate((int) $row['student_id'], (int) $row['bed_id'], $periodId, $actorId);
                $results['allocated']++;
            } catch (ValidationException $exception) {
                $results['skipped'][] = $row['student_number'].': '.implode(' ', $exception->errors()['allocation'] ?? $exception->errors()['payment'] ?? ['Allocation no longer available']);
            }
        }
        return $results;
    }
}
