<?php
namespace App\Services\Hostels;
use App\Models\Hostel\{HostelApplication,HostelAllocationPeriod,HostelSetting};
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class HostelApplicationService {
 public function submit(Student $student,int $periodId,?int $hostelId,?string $notes): HostelApplication {
  return DB::transaction(function() use($student,$periodId,$hostelId,$notes) {
   $settings=HostelSetting::first();
   if(!$settings?->enabled || !$settings->allow_applications || !in_array($settings->allocation_method,['application','hybrid'],true)) throw ValidationException::withMessages(['application'=>'Applications are not enabled.']);
   if($hostelId && !\App\Models\Hostel\Hostel::whereKey($hostelId)->where('status','active')->exists()) throw ValidationException::withMessages(['preferred_hostel_id'=>'Selected hostel is unavailable.']);
   if(!$student->isActive()) throw ValidationException::withMessages(['application'=>'Student is not active.']);
   $period=HostelAllocationPeriod::findOrFail($periodId);
   if($period->status!=='open' || $period->ends_on->lt(today())) throw ValidationException::withMessages(['application'=>'This allocation period is closed.']);
   return HostelApplication::create(['student_id'=>$student->id,'hostel_allocation_period_id'=>$periodId,'preferred_hostel_id'=>$hostelId,'notes'=>$notes,'status'=>'pending']);
  });
 }
 public function decide(HostelApplication $application,string $decision,int $actorId,?int $bedId,AllocationService $allocations): void {
  DB::transaction(function() use($application,$decision,$actorId,$bedId,$allocations) {
   $application=HostelApplication::lockForUpdate()->findOrFail($application->id);
   if(!in_array($application->status,['pending','waitlisted'],true)) throw ValidationException::withMessages(['application'=>'Application has already been processed.']);
   if($decision==='approved') {
    if(!$bedId) throw ValidationException::withMessages(['hostel_bed_id'=>'A bed is required for approval.']);
    $allocations->allocate($application->student_id,$bedId,$application->hostel_allocation_period_id,$actorId);
   }
   $application->update(['status'=>$decision,'reviewed_by'=>$actorId,'reviewed_at'=>now()]);
  });
 }
}
