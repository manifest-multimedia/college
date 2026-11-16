<?php
namespace App\Http\Controllers\Hostels;
use App\Http\Controllers\Controller;
use App\Models\Hostel\{HostelAllocation,HostelWarden};
use App\Services\Hostels\AllocationService;
use Illuminate\Http\Request;
class HostelWardenController extends Controller {
 public function index(Request $request) {
  $filters=$request->validate(['status'=>'nullable|in:allocated,checked_in,checked_out,released,transferred']);
  $hostelIds=HostelWarden::where('user_id',$request->user()->id)->where('active',true)->pluck('hostel_id');
  abort_if($hostelIds->isEmpty(),403);
  $scoped=fn()=>HostelAllocation::query()->join('hostel_beds as b','b.id','=','hostel_allocations.hostel_bed_id')->join('hostel_rooms as r','r.id','=','b.hostel_room_id')->whereIn('r.hostel_id',$hostelIds);
  $allocations=$scoped()->join('hostels as h','h.id','=','r.hostel_id')->join('students as s','s.id','=','hostel_allocations.student_id')
   ->when($filters['status']??null,fn($q,$status)=>$q->where('hostel_allocations.status',$status))
   ->select('hostel_allocations.*','r.room_number','b.bed_code','h.name as hostel_name','s.first_name','s.last_name','s.student_id as student_number')
   ->orderByDesc('hostel_allocations.id')->paginate(20)->withQueryString();
  $summary=$scoped()->selectRaw('hostel_allocations.status as status, COUNT(*) as total')->groupBy('hostel_allocations.status')->pluck('total','status');
  $hostelNames=\App\Models\Hostel\Hostel::whereIn('id',$hostelIds)->orderBy('name')->pluck('name');
  return view('hostels.warden',['allocations'=>$allocations,'summary'=>$summary,'hostelNames'=>$hostelNames,'statusFilter'=>$filters['status']??null]);
 }
 public function transition(Request $request,HostelAllocation $allocation,AllocationService $service) {
  $allowed=HostelWarden::where('user_id',$request->user()->id)->where('active',true)->whereHas('hostel',fn($q)=>$q->whereHas('rooms',fn($r)=>$r->whereHas('beds',fn($b)=>$b->where('id',$allocation->hostel_bed_id))))->exists();
  abort_unless($allowed,403);
  $data=$request->validate(['action'=>'required|in:check_in,check_out']);
  $service->transition($allocation,$data['action'],$request->user()->id);
  return back()->with('message','Occupancy updated.');
 }
}
