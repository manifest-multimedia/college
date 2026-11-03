<?php

namespace App\Services\Canteen;

use App\Models\AcademicYear;
use App\Models\CanteenQualification;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentFeeBill;
use App\Services\StudentIdGenerationService;
use Illuminate\Support\Collection;

/** Financial evidence takes priority; status lists never imply payment amounts. */
class CanteenClearanceService
{
    public function normalizeIndexNumber(string $value): string
    {
        $value = str_replace(['\\', "\u{2044}", "\u{2215}"], '/', mb_strtoupper(trim($value)));

        return preg_replace('/\s+/', '', $value) ?? $value;
    }

    public function verify(string $indexNumber): array
    {
        $student = Student::query()->where('student_id', $this->normalizeIndexNumber($indexNumber))->first();
        if (! $student) {
            return $this->result('not_found', null, 'No student matches this index number.') + ['student' => null];
        }

        return $this->verifyMany(collect([$student]))[$student->id] + ['student' => $student];
    }

    public function verifyMany(Collection $students): array
    {
        if ($students->isEmpty()) {
            return [];
        }
        $policy = app(CanteenPolicy::class);
        $period = $policy->period();
        $threshold = $policy->paymentThreshold();
        $prefix = strtoupper(app(StudentIdGenerationService::class)->configuredInstitutionPrefix() ?? '');
        $bills = collect();
        $qualifications = collect();
        $billingPeriod = $period ? $this->billingPeriod($period) : null;
        if ($billingPeriod && ! isset($billingPeriod['ambiguous'])) {
            $bills = StudentFeeBill::query()->active()->where('academic_year_id', $billingPeriod['year']->id)
                ->where('semester_id', $billingPeriod['semester']->id)->whereIn('student_id', $students->pluck('id'))
                ->orderByDesc('created_at')->orderByDesc('id')->get()->unique('student_id')->keyBy('student_id');
        }
        if ($period && $prefix !== '') {
            // Include revoked imports when choosing the newest record: revocation
            // must never silently reactivate an older approval.
            $latest = CanteenQualification::query()->selectRaw('MAX(canteen_qualifications.id)')
                ->join('canteen_qualification_imports as batch', 'batch.id', '=', 'canteen_qualifications.import_id')
                ->where('batch.institution_prefix', $prefix)->where('batch.academic_year', $period['academic_year'])
                ->where('batch.semester', $period['semester'])->whereIn('student_id', $students->pluck('id'))->groupBy('student_id');
            $qualifications = CanteenQualification::query()->with('import')->whereIn('id', $latest)->get()->keyBy('student_id');
        }
        $results = [];
        foreach ($students as $student) {
            $context = ['academic_year' => $period['academic_year'] ?? null, 'semester' => $period['semester'] ?? null];
            if (! $student->isActive()) {
                $results[$student->id] = $this->result('review_required', null, 'This student record is not active.', $context);

                continue;
            }
            if (! $period || $prefix === '' || ! str_starts_with(strtoupper($student->student_id), $prefix.'/')) {
                $results[$student->id] = $this->result('review_required', null, 'Institution or clearance period is unconfigured or does not match.', $context);

                continue;
            }
            if (isset($billingPeriod['ambiguous'])) {
                $results[$student->id] = $this->result('review_required', null, 'The financial period has duplicate definitions and needs review.', $context);

                continue;
            }
            $bill = $bills->get($student->id);
            if ($bill) {
                $context += ['source' => 'financial_records', 'bill_id' => $bill->id, 'payment_threshold' => $threshold];
                $total = (float) $bill->total_amount;
                $paid = (float) $bill->amount_paid;
                if ($threshold === null || ! is_finite($total) || ! is_finite($paid) || $total <= 0 || $paid < 0) {
                    $results[$student->id] = $this->result('review_required', null, 'Financial evidence or the institution payment rule needs review.', $context);

                    continue;
                }
                $ratio = $paid / $total * 100;
                $percentage = min(100.0, round($ratio, 2));
                $results[$student->id] = $this->result($ratio >= $threshold ? 'approved' : 'denied', $percentage, null, $context);

                continue;
            }
            $qualification = $qualifications->get($student->id);
            if (! $qualification || $qualification->index_number !== $student->student_id || $qualification->import->revoked_at !== null) {
                $results[$student->id] = $this->result('review_required', null, 'No current valid qualification list or financial evidence is available.', $context);

                continue;
            }
            $context += ['source' => 'qualification_list', 'qualification_id' => $qualification->id, 'import_id' => $qualification->import_id,
                'file_name' => $qualification->import->file_name, 'file_sha256' => $qualification->import->file_sha256,
                'imported_at' => $qualification->import->imported_at->toIso8601String()];
            $results[$student->id] = $this->result($qualification->qualified ? 'approved' : 'denied', null, null, $context);
        }

        return $results;
    }

    private function billingPeriod(array $period): ?array
    {
        $years = AcademicYear::query()->where('name', $period['academic_year'])->limit(2)->get();
        if ($years->count() > 1) {
            return ['ambiguous' => true];
        }
        if ($years->isEmpty()) {
            return null;
        }
        $semesters = Semester::query()->where('academic_year_id', $years[0]->id)->where('sequence', $period['semester'])->limit(2)->get();
        if ($semesters->count() > 1) {
            return ['ambiguous' => true];
        }

        return $semesters->count() === 1 ? ['year' => $years[0], 'semester' => $semesters[0]] : null;
    }

    private function result(string $decision, ?float $percentage, ?string $reason, array $context = []): array
    {
        return ['decision' => $decision, 'payment_percentage' => $percentage, 'reason' => $reason,
            'decision_context' => $context + ['source' => null, 'evaluated_at' => now()->toIso8601String()]];
    }
}
