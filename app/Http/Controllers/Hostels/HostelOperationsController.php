<?php
namespace App\Http\Controllers\Hostels;
use App\Http\Controllers\Controller;
use App\Models\Hostel\{Hostel,HostelAllocation,HostelBed,HostelRoom,HostelWarden};
use App\Services\Hostels\AllocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class HostelOperationsController extends Controller {
 const WARDEN_ELIGIBLE_ROLES=['Hostel Warden','Hostel Officer','Administrator','Super Admin','System','Academic Officer'];
 public function index(Request $request) {
  $hostels=Hostel::orderBy('name')->get();
  $totals=['hostels'=>$hostels->count(),'rooms'=>HostelRoom::count(),'beds'=>HostelBed::count(),'active_allocations'=>HostelAllocation::whereIn('status',['allocated','checked_in'])->count(),'checked_in'=>HostelAllocation::where('status','checked_in')->count()];
  $occupancy=DB::table('hostels as h')->leftJoin('hostel_rooms as r','r.hostel_id','=','h.id')->leftJoin('hostel_beds as b','b.hostel_room_id','=','r.id')->leftJoin('hostel_allocations as a',function($join){$join->on('a.hostel_bed_id','=','b.id')->whereIn('a.status',['allocated','checked_in']);})->select('h.id','h.name')->selectRaw('COUNT(DISTINCT b.id) as beds, COUNT(DISTINCT a.hostel_bed_id) as allocated')->groupBy('h.id','h.name')->get();
  $wardens=HostelWarden::with(['hostel:id,name','user:id,name,email'])->where('active',true)->latest()->get();
  $staff=\App\Models\User::whereHas('roles',fn($q)=>$q->whereIn('name',self::WARDEN_ELIGIBLE_ROLES))->orderBy('name')->get(['id','name','email']);
  $periods=\App\Models\Hostel\HostelAllocationPeriod::orderByDesc('starts_on')->get(['id','name']);
  return view('hostels.operations',compact('hostels','totals','occupancy','wardens','staff','periods'));
 }
 public function assignWarden(Request $request) {
  $data=$request->validate(['hostel_id'=>'required|exists:hostels,id','user_id'=>'required|exists:users,id']);
  abort_unless(\App\Models\User::findOrFail($data['user_id'])->hasAnyRole(self::WARDEN_ELIGIBLE_ROLES), 422, 'User must have an authorized staff role.');
  HostelWarden::updateOrCreate($data,['active'=>true]); return back()->with('message','Warden assigned.');
 }
 public function revokeWarden(HostelWarden $warden) { $warden->update(['active'=>false]); return back()->with('message','Warden access revoked.'); }
 public function transfer(Request $request,HostelAllocation $allocation,AllocationService $service) {
  $data=$request->validate(['hostel_bed_id'=>'required|exists:hostel_beds,id','reason'=>'nullable|string|max:2000']);
  $service->transfer($allocation,$data['hostel_bed_id'],$request->user()->id,$data['reason']??null);
  return back()->with('message','Student transferred successfully.');
 }
 public function export(Request $request) {
  $data=$request->validate(['period_id'=>'nullable|exists:hostel_allocation_periods,id','status'=>'nullable|in:allocated,checked_in,checked_out,released,transferred']);
  $rows=HostelAllocation::query()->when($data['period_id']??null,fn($q,$id)=>$q->where('hostel_allocation_period_id',$id))->when($data['status']??null,fn($q,$status)=>$q->where('status',$status))->orderBy('id')->cursor();
  return response()->streamDownload(function() use($rows) { $out=fopen('php://output','w'); fputcsv($out,['Allocation ID','Student ID','Bed ID','Period ID','Status','Check-in','Check-out']); foreach($rows as $a) fputcsv($out,[$a->id,$a->student_id,$a->hostel_bed_id,$a->hostel_allocation_period_id,$a->status,$a->checked_in_at,$a->checked_out_at]); fclose($out); },'hostel-allocations.csv',['Content-Type'=>'text/csv']);
 }
}
