<?php
namespace App\Http\Controllers\Hostels;
use App\Http\Controllers\Controller;
use App\Models\Hostel\{Hostel,HostelApplication,HostelAllocationPeriod,HostelBed,HostelRoom,HostelSetting};
use App\Services\Hostels\{AllocationService,HostelApplicationService};
use Illuminate\Http\Request;
class HostelApplicationController extends Controller {
 public function studentIndex(Request $request) {
  $student=$request->user()->student;
  abort_unless($student,403);
  return view('hostels.student-applications',['settings'=>HostelSetting::first(),'periods'=>HostelAllocationPeriod::where('status','open')->whereDate('ends_on','>=',today())->get(),'hostels'=>Hostel::where('status','active')->get(),'applications'=>HostelApplication::with(['period','preferredHostel'])->where('student_id',$student->id)->latest()->get()]);
 }
 public function submit(Request $request,HostelApplicationService $service) {
  $student=$request->user()->student; abort_unless($student,403);
  $data=$request->validate(['hostel_allocation_period_id'=>'required|exists:hostel_allocation_periods,id','preferred_hostel_id'=>'nullable|exists:hostels,id','notes'=>'nullable|string|max:2000']);
  $service->submit($student,$data['hostel_allocation_period_id'],$data['preferred_hostel_id']??null,$data['notes']??null);
  return back()->with('message','Application submitted.');
 }
 public function index(Request $request) {
  $filters=$request->validate(['status'=>'nullable|in:pending,waitlisted']);
  $queue=['pending','waitlisted'];
  $beds=HostelBed::with('room.hostel:id,name')->where('status','active')->get()
   ->sortBy(fn($bed)=>sprintf('%s|%s|%s',$bed->room?->hostel?->name,$bed->room?->room_number,$bed->bed_code),SORT_NATURAL|SORT_FLAG_CASE)->values();
  return view('hostels.applications',[
   'applications'=>HostelApplication::with(['student','period','preferredHostel'])->whereIn('status',$queue)
    ->when($filters['status']??null,fn($q,$status)=>$q->where('status',$status))->orderBy('created_at')->paginate(20)->withQueryString(),
   'beds'=>$beds,
   'openPeriods'=>HostelAllocationPeriod::where('status','open')->orderByDesc('starts_on')->get(),
   'counts'=>HostelApplication::whereIn('status',$queue)->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total','status'),
   'statusFilter'=>$filters['status']??null,
  ]);
 }
 public function autoAllocate(Request $request,\App\Services\Hostels\HostelAutoAllocationService $service) {
  $data=$request->validate(['hostel_allocation_period_id'=>'required|exists:hostel_allocation_periods,id']);
  $result=$service->process($data['hostel_allocation_period_id'],$request->user()->id);
  return back()->with('message',"Auto allocation: {$result['allocated']} assigned, {$result['waitlisted']} waitlisted, {$result['payment_pending']} awaiting payment.");
 }
 public function decide(Request $request,HostelApplication $application,HostelApplicationService $service,AllocationService $allocations) {
  $data=$request->validate(['decision'=>'required|in:approved,rejected','hostel_bed_id'=>'nullable|exists:hostel_beds,id']);
  $service->decide($application,$data['decision'],$request->user()->id,$data['hostel_bed_id']??null,$allocations);
  return back()->with('message','Application '.$data['decision'].'.');
 }
}
