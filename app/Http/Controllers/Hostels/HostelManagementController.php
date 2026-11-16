<?php
namespace App\Http\Controllers\Hostels;

use App\Http\Controllers\Controller;
use App\Models\Hostel\{Hostel, HostelRoom, HostelBed, HostelAllocation, HostelAllocationPeriod, HostelSetting};
use App\Models\Student;
use App\Services\Hostels\AllocationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HostelManagementController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate(['status' => 'nullable|in:allocated,checked_in,checked_out,released,transferred']);
        $activeStatuses = ['allocated', 'checked_in'];

        $hostels = Hostel::orderBy('name')->get();
        $rooms = HostelRoom::with('hostel:id,name')->orderBy('room_number')->get();
        $beds = HostelBed::with('room.hostel:id,name')->orderBy('bed_code')->get()
            ->sortBy(fn ($bed) => sprintf('%s|%s|%s', $bed->room?->hostel?->name, $bed->room?->room_number, $bed->bed_code), SORT_NATURAL | SORT_FLAG_CASE)->values();
        $periods = HostelAllocationPeriod::orderByDesc('starts_on')->get();

        $occupiedBedIds = HostelAllocation::whereIn('status', $activeStatuses)->pluck('hostel_bed_id');
        $bedsByHostel = $beds->groupBy(fn ($bed) => $bed->room?->hostel_id);
        $occupiedByHostel = $beds->whereIn('id', $occupiedBedIds)->groupBy(fn ($bed) => $bed->room?->hostel_id);
        $roomsByHostel = $rooms->groupBy('hostel_id');
        $hostelStats = $hostels->mapWithKeys(fn ($hostel) => [$hostel->id => [
            'rooms' => $roomsByHostel->get($hostel->id)?->count() ?? 0,
            'beds' => $bedsByHostel->get($hostel->id)?->count() ?? 0,
            'occupied' => $occupiedByHostel->get($hostel->id)?->count() ?? 0,
        ]]);

        return view('hostels.manage', [
            'settings' => HostelSetting::first(), 'feeTypes' => \App\Models\FeeType::orderBy('name')->get(),
            'hostels' => $hostels, 'rooms' => $rooms, 'beds' => $beds, 'periods' => $periods,
            'hostelStats' => $hostelStats,
            'totals' => ['hostels' => $hostels->count(), 'rooms' => $rooms->count(), 'beds' => $beds->count(),
                'occupied' => $occupiedBedIds->unique()->count(), 'openPeriods' => $periods->where('status', 'open')->count()],
            'statusFilter' => $filters['status'] ?? null,
            'allocations' => HostelAllocation::with(['student', 'bed.room.hostel', 'period'])
                ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
                ->orderByDesc('id')->paginate(20)->withQueryString(),
        ]);
    }
    public function settings(Request $request)
    {
        $data = $request->validate([
            'enabled'=>'required|boolean','allocation_method'=>'required|in:staff,application,hybrid,automatic',
            'allocation_unit'=>'required|in:bed,room','period_type'=>'required|in:semester,academic_year,custom',
            'enforce_gender'=>'required|boolean','require_check_in'=>'required|boolean',
            'allow_applications'=>'required|boolean','require_payment'=>'required|boolean',
            'fee_type_id'=>'nullable|exists:fee_types,id','minimum_payment_percent'=>'required|integer|between:1,100','auto_waitlist'=>'required|boolean',
        ]);
        if ($data['require_payment'] && !$data['fee_type_id']) throw \Illuminate\Validation\ValidationException::withMessages(['fee_type_id'=>'Choose an accommodation fee type before enabling payment clearance.']);
        HostelSetting::updateOrCreate(['id'=>1], $data);
        return back()->with('message','Hostel settings saved.');
    }
    public function hostel(Request $request)
    {
        $data=$request->validate(['name'=>'required|string|max:255','code'=>'required|string|max:50|unique:hostels,code','location'=>'nullable|string|max:255','gender_policy'=>'required|in:male,female,mixed']);
        Hostel::create($data); return back()->with('message','Hostel created.');
    }
    public function room(Request $request)
    {
        $data=$request->validate(['hostel_id'=>'required|exists:hostels,id','room_number'=>['required','string','max:50',Rule::unique('hostel_rooms')->where('hostel_id',$request->hostel_id)],'floor'=>'nullable|string|max:50']);
        HostelRoom::create($data); return back()->with('message','Room created.');
    }
    public function bed(Request $request)
    {
        $data=$request->validate(['hostel_room_id'=>'required|exists:hostel_rooms,id','bed_code'=>['required','string','max:50',Rule::unique('hostel_beds')->where('hostel_room_id',$request->hostel_room_id)]]);
        HostelBed::create($data); return back()->with('message','Bed created.');
    }
    public function period(Request $request)
    {
        $data=$request->validate(['name'=>'required|string|max:255','academic_year_id'=>'nullable|exists:academic_years,id','semester_id'=>'nullable|exists:semesters,id','starts_on'=>'required|date','ends_on'=>'required|date|after_or_equal:starts_on','status'=>'required|in:draft,open,closed']);
        HostelAllocationPeriod::create($data); return back()->with('message','Allocation period created.');
    }
    public function allocate(Request $request, AllocationService $service)
    {
        $data=$request->validate(['student_id'=>'required|exists:students,id','hostel_bed_id'=>'required|exists:hostel_beds,id','hostel_allocation_period_id'=>'required|exists:hostel_allocation_periods,id']);
        $service->allocate($data['student_id'],$data['hostel_bed_id'],$data['hostel_allocation_period_id'],$request->user()->id);
        return back()->with('message','Student allocated successfully.');
    }
    public function transition(Request $request, HostelAllocation $allocation, AllocationService $service)
    {
        $data=$request->validate(['action'=>'required|in:check_in,check_out,release']);
        $service->transition($allocation,$data['action'],$request->user()->id);
        return back()->with('message','Allocation updated.');
    }
}
