<?php

namespace App\Services\Canteen;

use App\Models\CanteenQualification;
use App\Models\Student;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CanteenMealLanes
{
    public function __construct(private CanteenMealPolicy $policy, private CanteenClearanceService $clearance) {}

    public function roster(): array
    {
        $prefix = $this->policy->prefix();
        $students = Student::with('collegeClass')->where('student_id', 'like', $prefix.'/%')->where('status', 'active')->orderBy('id')->get();
        $period = app(CanteenPolicy::class)->period();
        $latest = CanteenQualification::selectRaw('MAX(canteen_qualifications.id)')
            ->join('canteen_qualification_imports as batch', 'batch.id', '=', 'canteen_qualifications.import_id')
            ->where('batch.institution_prefix', $prefix)->where('batch.academic_year', $period['academic_year'] ?? '')
            ->where('batch.semester', $period['semester'] ?? 0)->groupBy('student_id');
        $qualifications = CanteenQualification::with('import')->whereIn('id', $latest)->get()->keyBy('student_id');
        $groups = [];
        $rows = [];
        foreach ($students as $student) {
            $label = $qualifications->get($student->id)?->import?->class_label;
            $key = $label ? 'list:'.hash('sha256', mb_strtoupper(trim($label))) : 'college:'.($student->college_class_id ?? 'other');
            $label = $label ?: ($student->collegeClass?->name ?? 'Other students');
            $groups[$key] ??= ['key' => $key, 'label' => $label, 'count' => 0];
            $groups[$key]['count']++;
            $rows[$student->student_id] = ['group_key' => $key, 'group_label' => $label, 'student_id' => $student->id];
        }

        return ['groups' => array_values($groups), 'students' => $rows];
    }

    public function assign(array $assignments, array $labels, int $actor): void
    {
        $roster = $this->roster();
        $known = array_column($roster['groups'], null, 'key');
        $devices = DB::table('canteen_meal_devices')->pluck('device_id')->all();
        foreach ($assignments as $group => $device) {
            if (! isset($known[$group]) || ! in_array($device, $devices, true)) {
                throw ValidationException::withMessages(['assignments' => 'Select a registered scanner for each listed class.']);
            }
        }
        if (count($assignments) !== count($known)) {
            throw ValidationException::withMessages(['assignments' => 'Every class needs one serving device before the day can be prepared.']);
        }
        DB::transaction(function () use ($assignments, $labels, $actor) {
            DB::table('canteen_meal_policies')->where('institution_prefix', $this->policy->prefix())->lockForUpdate()->first();
            foreach ($assignments as $group => $device) {
                DB::table('canteen_meal_assignments')->updateOrInsert(['institution_prefix' => $this->policy->prefix(), 'group_key' => $group],
                    ['device_id' => $device, 'created_at' => now(), 'updated_at' => now()]);
            }
            foreach ($labels as $device => $label) {
                DB::table('canteen_meal_devices')->where('device_id', $device)->update(['label' => $label, 'updated_at' => now()]);
            }
            DB::table('canteen_meal_assignment_changes')->insert(['institution_prefix' => $this->policy->prefix(),
                'assignments' => json_encode($assignments, JSON_THROW_ON_ERROR), 'changed_by' => $actor, 'created_at' => now()]);
        }, 3);
    }

    public function currentPlan(): ?array
    {
        $now = CarbonImmutable::now();
        $recent = DB::table('canteen_meal_plans')->where('institution_prefix', $this->policy->prefix())->orderByDesc('id')->limit(3)->get();
        foreach ($recent as $row) {
            $policy = json_decode($row->policy, true);
            $start = CarbonImmutable::parse($row->service_date.' 00:00:00', $policy['timezone']);
            if ($now->gte($start) && $now->lt($start->addDay())) {
                return ['service_date' => $row->service_date, 'policy' => $policy, 'students' => json_decode($row->students, true)];
            }
        }

        return null;
    }

    /** Once any scanner prepares a day, its routing and windows cannot split. */
    public function plan(): array
    {
        $policy = $this->policy->get();
        abort_unless($policy['enabled'], 422, 'Meal redemption is not enabled. Ask the College administrator to save the schedule.');
        $date = CarbonImmutable::now($policy['timezone'])->toDateString();

        return DB::transaction(function () use ($date) {
            $row = DB::table('canteen_meal_policies')->where('institution_prefix', $this->policy->prefix())->lockForUpdate()->first();
            if ($active = $this->currentPlan()) {
                return $active;
            }
            $fresh = json_decode($row->configuration, true);
            $date = CarbonImmutable::now($fresh['timezone'])->toDateString();
            $existing = DB::table('canteen_meal_plans')->where('institution_prefix', $this->policy->prefix())->where('service_date', $date)->first();
            if ($existing) {
                return ['service_date' => $date, 'policy' => json_decode($existing->policy, true), 'students' => json_decode($existing->students, true)];
            }
            $policy = json_decode($row->configuration, true) + ['version' => $row->version];
            abort_unless($policy['enabled'], 422, 'Meal redemption is disabled.');
            $roster = $this->roster();
            $assignments = DB::table('canteen_meal_assignments')->where('institution_prefix', $this->policy->prefix())->pluck('device_id', 'group_key');
            $devices = DB::table('canteen_meal_devices')->get()->keyBy('device_id');
            foreach ($roster['students'] as &$student) {
                $device = $devices->get($assignments->get($student['group_key'], ''));
                if (! $device) {
                    throw ValidationException::withMessages(['assignments' => 'Assign every class to a registered scanner before preparing the day.']);
                }
                $student['device_id'] = $device->device_id;
                $student['lane'] = $device->label;
            }
            unset($student);
            DB::table('canteen_meal_plans')->insert(['institution_prefix' => $this->policy->prefix(), 'service_date' => $date,
                'policy' => json_encode($policy), 'students' => json_encode($roster['students']), 'created_at' => now(), 'updated_at' => now()]);

            return ['service_date' => $date, 'policy' => $policy, 'students' => $roster['students']];
        }, 3);
    }

    public function existingOrCreate(?string $date, bool $offline): ?array
    {
        if ($date) {
            $row = DB::table('canteen_meal_plans')->where('institution_prefix', $this->policy->prefix())->where('service_date', $date)->first();
            if ($row) {
                return ['service_date' => $date, 'policy' => json_decode($row->policy, true), 'students' => json_decode($row->students, true)];
            }
        }
        abort_if($offline, 422, 'The saved serving day is unavailable.');

        return $this->plan();
    }

    public function snapshot(string $device, int $actor, CarbonImmutable $sessionExpiry): array
    {
        $plan = $this->plan();
        $assigned = array_filter($plan['students'], fn ($row) => $row['device_id'] === $device);
        abort_unless($assigned, 422, 'This scanner has no assigned classes for today. Ask the College administrator to assign a serving lane.');
        $students = Student::with('collegeClass')->whereIn('student_id', array_keys($assigned))->get();
        $decisions = $this->clearance->verifyMany($students);
        $rows = [];
        foreach ($students as $student) {
            $decision = $decisions[$student->id];
            $rows[$student->student_id] = ['index_number' => $student->student_id,
                'full_name' => trim(implode(' ', array_filter([$student->first_name, $student->other_name, $student->last_name]))),
                'programme' => ['name' => $student->collegeClass?->name, 'code' => $student->collegeClass?->short_name],
                'decision' => $decision['decision'], 'payment_percentage' => $decision['payment_percentage'],
                'decision_context' => $decision['decision_context'] + ['reason' => $decision['reason']]];
        }
        $windows = array_map(function ($window) use ($plan) {
            $base = $plan['service_date'].' ';

            return ['meal' => $window['meal'], 'opens_at' => CarbonImmutable::parse($base.$window['opens'], $plan['policy']['timezone'])->toIso8601String(),
                'closes_at' => CarbonImmutable::parse($base.$window['closes'], $plan['policy']['timezone'])->toIso8601String()];
        }, $plan['policy']['windows']);
        $end = max(array_column($windows, 'closes_at'));
        $expiry = CarbonImmutable::parse($end)->min($sessionExpiry);
        abort_unless($expiry->isFuture(), 422, 'Today’s meal windows have ended.');
        $id = (string) Str::uuid();
        $payload = ['snapshot_id' => $id, 'device_id' => $device, 'attendant_user_id' => (string) $actor,
            'institution_prefix' => $this->policy->prefix(), 'service_date' => $plan['service_date'],
            'timezone' => $plan['policy']['timezone'], 'server_time' => now()->toIso8601String(), 'expires_at' => $expiry->toIso8601String(),
            'policy_version' => $plan['policy']['version'], 'windows' => $windows, 'students' => $rows,
            'routes' => array_map(fn ($r) => ['device_id' => $r['device_id'], 'lane' => $r['lane'], 'group' => $r['group_label']], $plan['students']),
            'lane' => reset($assigned)['lane'],
            'redeemed' => DB::table('canteen_meal_redemptions')->where('institution_prefix', $this->policy->prefix())
                ->where('service_date', $plan['service_date'])->whereIn('index_number', array_keys($assigned))->get(['index_number', 'meal', 'redeemed_at', 'id'])->map(fn ($r) => ['index_number' => $r->index_number, 'meal' => $r->meal, 'id' => $r->id, 'redeemed_at' => CarbonImmutable::parse($r->redeemed_at)->toIso8601String()])->all()];
        DB::table('canteen_meal_snapshots')->insert(['id' => $id, 'institution_prefix' => $this->policy->prefix(), 'device_id' => $device,
            'attendant_user_id' => $actor, 'service_date' => $plan['service_date'], 'policy_version' => $plan['policy']['version'],
            'payload' => json_encode($payload), 'expires_at' => $expiry, 'created_at' => now(), 'updated_at' => now()]);

        return $payload;
    }
}
