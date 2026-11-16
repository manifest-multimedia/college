<?php
namespace App\Services\Hostels;

use App\Models\Hostel\{HostelAllocation, HostelAllocationEvent, HostelAllocationPeriod, HostelBed, HostelSetting};
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AllocationService
{
    public function allocate(int $studentId, int $bedId, int $periodId, ?int $actorId): HostelAllocation
    {
        return DB::transaction(function () use ($studentId, $bedId, $periodId, $actorId) {
            $settings = HostelSetting::first();
            if (! $settings?->enabled) $this->reject('Hostel management is not enabled.');
            $period = HostelAllocationPeriod::lockForUpdate()->findOrFail($periodId);
            if ($period->status !== 'open') $this->reject('Allocation period is not open.');
            if ($period->ends_on < now()->toDateString()) $this->reject('Allocation period has ended.');
            $student = Student::lockForUpdate()->findOrFail($studentId);
            if (! $student->isActive()) $this->reject('Only active students can be allocated.');
            $bed = HostelBed::lockForUpdate()->findOrFail($bedId);
            if ($bed->status !== 'active') $this->reject('This bed is unavailable.');
            $room = DB::table('hostel_rooms')->where('id', $bed->hostel_room_id)->first();
            $hostel = DB::table('hostels')->where('id', $room->hostel_id)->first();
            if ($room->status !== 'active' || $hostel->status !== 'active') $this->reject('Room or hostel is unavailable.');
            if ($settings->enforce_gender && in_array($hostel->gender_policy, ['male','female'], true)) {
                $gender = strtolower(trim((string) $student->gender));
                if (! in_array($gender, $hostel->gender_policy === 'male' ? ['male','m'] : ['female','f'], true)) $this->reject('Student does not meet the hostel gender policy.');
            }
            // Lock the allocation period to serialize concurrent allocations, including different beds.
            $active = ['allocated', 'checked_in'];
            $overlappingPeriodIds = HostelAllocationPeriod::where('starts_on', '<=', $period->ends_on)
                ->where('ends_on', '>=', $period->starts_on)->pluck('id');
            if (HostelAllocation::whereIn('hostel_allocation_period_id', $overlappingPeriodIds)->whereIn('status', $active)->where('student_id', $studentId)->exists()) $this->reject('Student already has an overlapping allocation.');
            if (HostelAllocation::whereIn('hostel_allocation_period_id', $overlappingPeriodIds)->whereIn('status', $active)->where('hostel_bed_id', $bedId)->exists()) $this->reject('Bed is already allocated during this period.');
            app(HostelPaymentClearanceService::class)->assertCleared($studentId, $periodId);
            $allocation = HostelAllocation::create(['student_id'=>$studentId,'hostel_bed_id'=>$bedId,'hostel_allocation_period_id'=>$periodId,'allocated_by'=>$actorId,'status'=>'allocated']);
            $this->event($allocation, 'allocated', $actorId);
            return $allocation;
        });
    }
    public function transfer(HostelAllocation $allocation, int $newBedId, int $actorId, ?string $reason = null): HostelAllocation
    {
        return DB::transaction(function () use ($allocation, $newBedId, $actorId, $reason) {
            $current = HostelAllocation::lockForUpdate()->findOrFail($allocation->id);
            if (! in_array($current->status, ['allocated','checked_in'], true)) $this->reject('Only active allocations can be transferred.');
            if ($current->hostel_bed_id === $newBedId) $this->reject('Select a different bed.');
            $previousStatus = $current->status;
            $current->update(['status'=>'transferred','checked_out_at'=>now()]);
            $this->event($current, 'transferred_out', $actorId);
            $replacement = $this->allocate($current->student_id, $newBedId, $current->hostel_allocation_period_id, $actorId);
            $replacement->update(['transferred_from_id'=>$current->id, 'notes'=>$reason]);
            if ($previousStatus === 'checked_in') $this->transition($replacement, 'check_in', $actorId);
            $this->event($replacement, 'transferred_in', $actorId);
            return $replacement->fresh();
        });
    }

    public function transition(HostelAllocation $allocation, string $action, ?int $actorId): void
    {
        DB::transaction(function () use ($allocation, $action, $actorId) {
            $current = HostelAllocation::lockForUpdate()->findOrFail($allocation->id);
            $allowed = ['check_in'=>['allocated','checked_in'], 'check_out'=>['checked_in','checked_out'], 'release'=>['allocated','released']];
            if (! isset($allowed[$action]) || $current->status !== $allowed[$action][0]) $this->reject('Invalid allocation status transition.');
            $current->update(['status'=>$allowed[$action][1]] + ($action === 'check_in' ? ['checked_in_at'=>now()] : ($action === 'check_out' ? ['checked_out_at'=>now()] : [])));
            $this->event($current, $action, $actorId);
        });
    }
    private function event(HostelAllocation $allocation, string $event, ?int $actorId): void
    {
        HostelAllocationEvent::create(['hostel_allocation_id'=>$allocation->id,'actor_id'=>$actorId,'event'=>$event]);
    }
    private function reject(string $message): never
    {
        throw ValidationException::withMessages(['allocation'=>$message]);
    }
}
