<?php
namespace App\Http\Controllers\Hostels;

use App\Http\Controllers\Controller;
use App\Models\{AcademicYear, Cohort, CollegeClass, Student};
use App\Models\Hostel\{Hostel, HostelAllocationPeriod};
use App\Services\Hostels\HostelBulkAllocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HostelBulkAllocationController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate(['search' => 'nullable|string|max:150', 'program_id' => 'nullable|integer|exists:college_classes,id',
            'cohort_id' => 'nullable|integer|exists:cohorts,id', 'academic_year_id' => 'nullable|integer|exists:academic_years,id',
            'gender' => 'nullable|string|max:20', 'allocation_status' => 'nullable|in:all,allocated,unallocated']);
        $active = fn ($q) => $q->whereIn('status', ['allocated', 'checked_in']);
        $students = Student::query()->active()->with(['collegeClass:id,name', 'cohort:id,name'])
            ->withExists(['hostelAllocations as has_active_allocation' => $active])
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->search($v))
            ->when($filters['program_id'] ?? null, fn ($q, $v) => $q->where('college_class_id', $v))
            ->when($filters['cohort_id'] ?? null, fn ($q, $v) => $q->where('cohort_id', $v))
            ->when($filters['academic_year_id'] ?? null, fn ($q, $v) => $q->where('academic_year_id', $v))
            ->when($filters['gender'] ?? null, fn ($q, $v) => $q->where('gender', $v));
        if (($filters['allocation_status'] ?? '') === 'allocated') $students->whereHas('hostelAllocations', $active);
        if (($filters['allocation_status'] ?? '') === 'unallocated') $students->whereDoesntHave('hostelAllocations', $active);
        return view('hostels.bulk-index', ['students' => $students->orderBy('last_name')->orderBy('first_name')->paginate(30)->withQueryString(),
            'programs' => CollegeClass::orderBy('name')->get(['id','name']), 'cohorts' => Cohort::orderBy('name')->get(['id','name']),
            'years' => AcademicYear::orderByDesc('id')->get(['id','name']), 'hostels' => Hostel::where('status','active')->orderBy('name')->get(),
            'periods' => HostelAllocationPeriod::where('status','open')->whereDate('ends_on','>=',today())->get(),
            'selectedIds' => $request->session()->get('hostel_bulk_selection', [])]);
    }
    public function search(Request $request)
    {
        $data = $request->validate(['search' => 'required|string|min:2|max:150']);
        return response()->json(Student::active()->search($data['search'])->with(['collegeClass:id,name','cohort:id,name'])
            ->orderBy('last_name')->limit(20)->get()->map(fn ($s) => ['id' => $s->id,
                'label' => trim("{$s->first_name} {$s->last_name}")." ({$s->student_id}) · ".($s->collegeClass?->name ?? 'No program')." · ".($s->cohort?->name ?? 'No cohort')])->values());
    }
    public function selection(Request $request)
    {
        $data = $request->validate(['student_ids' => 'present|array|max:200',
            'student_ids.*' => 'integer|distinct|exists:students,id', 'action' => 'required|in:add,remove,clear']);
        $current = array_map('intval', $request->session()->get('hostel_bulk_selection', []));
        $ids = array_map('intval', $data['student_ids']);
        if ($data['action'] === 'clear') $current = [];
        elseif ($data['action'] === 'remove') $current = array_values(array_diff($current, $ids));
        else {
            $valid = Student::active()->whereIn('id', $ids)->pluck('id')->map(fn ($id) => (int) $id)->all();
            if (count($valid) !== count($ids)) throw \Illuminate\Validation\ValidationException::withMessages(['student_ids' => 'Some students are no longer active.']);
            $current = array_values(array_unique(array_merge($current, $valid)));
            if (count($current) > 200) throw \Illuminate\Validation\ValidationException::withMessages(['student_ids' => 'You can select up to 200 students per allocation batch.']);
        }
        $request->session()->put('hostel_bulk_selection', $current);
        return response()->json(['count' => count($current), 'selected_ids' => $current]);
    }

    public function preview(Request $request, HostelBulkAllocationService $service)
    {
        $data = $request->validate(['hostel_id' => 'required|integer|exists:hostels,id', 'period_id' => 'required|integer|exists:hostel_allocation_periods,id']);
        $selected = $request->session()->get('hostel_bulk_selection', []);
        if (!count($selected)) throw \Illuminate\Validation\ValidationException::withMessages(['students' => 'Select students before previewing.']);
        $plan = $service->plan(array_map('intval', $selected), (int)$data['period_id'], (int)$data['hostel_id']);
        $token = Str::random(40);
        $request->session()->put('hostel_bulk_preview', ['token' => $token, 'plan' => $plan, 'period_id' => (int)$data['period_id'], 'hostel_id' => (int)$data['hostel_id']]);
        return view('hostels.bulk-preview', compact('plan', 'token'));
    }
    public function confirm(Request $request, HostelBulkAllocationService $service)
    {
        $request->validate(['token' => 'required|string']);
        $preview = $request->session()->pull('hostel_bulk_preview');
        abort_unless($preview && hash_equals($preview['token'], $request->input('token')), 419);
        // Recalculate against current occupancy and payment status, rather than trusting the preview.
        $plan = $service->plan(array_column($preview['plan'], 'student_id'), $preview['period_id'], $preview['hostel_id']);
        $results = $service->execute($plan, $preview['period_id'], $request->user()->id);
        $request->session()->forget('hostel_bulk_selection');
        return redirect()->route('hostels.bulk.index')->with('bulk_results', $results);
    }
}
