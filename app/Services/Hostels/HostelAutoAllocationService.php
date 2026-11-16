<?php
namespace App\Services\Hostels;
use App\Models\Hostel\{HostelApplication,HostelAllocationPeriod,HostelBed,HostelSetting};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class HostelAutoAllocationService {
 public function process(int $periodId,int $actorId): array {
  return DB::transaction(function() use($periodId,$actorId) {
   $settings=HostelSetting::first();
   if(!$settings?->enabled || !in_array($settings->allocation_method,['automatic','hybrid','application'],true)) throw ValidationException::withMessages(['allocation'=>'Automatic allocation is not enabled for this workflow.']);
   $period=HostelAllocationPeriod::lockForUpdate()->findOrFail($periodId);
   if($period->status!=='open' || $period->ends_on->lt(today())) throw ValidationException::withMessages(['period'=>'Period is not open.']);
   $result=['allocated'=>0,'waitlisted'=>0,'payment_pending'=>0];
   $applications=HostelApplication::where('hostel_allocation_period_id',$periodId)->whereIn('status',['pending','waitlisted'])->orderBy('created_at')->orderBy('id')->lockForUpdate()->get();
   $allocations=app(AllocationService::class); $clearance=app(HostelPaymentClearanceService::class);
   foreach($applications as $application) {
    if(!\App\Models\Student::whereKey($application->student_id)->active()->exists()) { continue; }
    if(!$clearance->isCleared($application->student_id,$periodId)) { $result['payment_pending']++; continue; }
    $beds=HostelBed::query()->join('hostel_rooms as r','r.id','=','hostel_beds.hostel_room_id')->join('hostels as h','h.id','=','r.hostel_id')
     ->where('hostel_beds.status','active')->where('r.status','active')->where('h.status','active')
     ->when($application->preferred_hostel_id,fn($q,$id)=>$q->where('h.id',$id))
     ->orderBy('h.id')->orderBy('r.id')->orderBy('hostel_beds.id')->pluck('hostel_beds.id');
    $assigned=false;
    foreach($beds as $bedId) {
     try { $allocations->allocate($application->student_id,(int)$bedId,$periodId,$actorId); $assigned=true; break; }
     catch(ValidationException $e) { continue; }
    }
    if($assigned) { $application->update(['status'=>'approved','reviewed_by'=>$actorId,'reviewed_at'=>now()]); $result['allocated']++; }
    elseif($settings->auto_waitlist) { $application->update(['status'=>'waitlisted','waitlisted_at'=>$application->waitlisted_at ?? now()]); $result['waitlisted']++; }
   }
   return $result;
  });
 }
}
