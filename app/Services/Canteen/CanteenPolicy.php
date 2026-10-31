<?php

namespace App\Services\Canteen;

use App\Models\AcademicYear;
use App\Models\Semester;
use App\Services\StudentIdGenerationService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/** Institution-specific policy lives in the existing settings store, never in a mobile build. */
class CanteenPolicy
{
    public function period(): ?array
    {
        $yearKey = $this->key('academic_year');
        $semesterKey = $this->key('semester');
        $settings = DB::table('system_settings')->whereIn('key', [$yearKey, $semesterKey])->where('is_active', true)->pluck('value', 'key');
        if ($settings->isNotEmpty()) {
            $year = $settings->get($yearKey);
            $semester = $settings->get($semesterKey);

            return is_string($year) && trim($year) !== '' && ctype_digit((string) $semester) && (int) $semester > 0
                ? ['academic_year' => $year, 'semester' => (int) $semester] : null;
        }
        $years = AcademicYear::query()->where('is_current', true)->limit(2)->get();
        if ($years->count() !== 1) {
            return null;
        }
        $semesters = Semester::query()->where('academic_year_id', $years[0]->id)->where('is_current', true)->limit(2)->get();

        return $semesters->count() === 1 ? ['academic_year' => $years[0]->name, 'semester' => (int) $semesters[0]->sequence] : null;
    }

    public function setPeriod(string $year, int $semester): void
    {
        if (trim($year) === '' || strlen($year) > 32 || $semester < 1 || $semester > 99) {
            throw new InvalidArgumentException('An academic-year label and a semester between 1 and 99 are required.');
        }
        DB::transaction(function () use ($year, $semester) {
            $this->save('academic_year', trim($year), 'string');
            $this->save('semester', (string) $semester, 'number');
        });
    }

    public function paymentThreshold(): ?float
    {
        $value = DB::table('system_settings')->where('key', $this->key('payment_threshold'))->where('is_active', true)->value('value');
        if ($value === null) {
            $value = config('canteen.payment_threshold');
        }

        return is_numeric($value) && is_finite((float) $value) && (float) $value > 0 && (float) $value <= 100 ? (float) $value : null;
    }

    public function setPaymentThreshold(float $value): void
    {
        if (! is_finite($value) || $value <= 0 || $value > 100) {
            throw new InvalidArgumentException('The approved minimum payment must be greater than 0 and at most 100 percent.');
        }
        $this->save('payment_threshold', (string) $value, 'number');
    }

    private function save(string $key, string $value, string $type): void
    {
        if (app(StudentIdGenerationService::class)->configuredInstitutionPrefix() === null) {
            throw new InvalidArgumentException('Configure the institution prefix before saving canteen policy.');
        }
        DB::table('system_settings')->updateOrInsert(['key' => $this->key($key)], [
            'value' => $value, 'type' => $type, 'is_active' => true, 'group' => 'canteen',
            'description' => 'Institution-approved canteen clearance configuration.',
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function key(string $setting): string
    {
        $prefix = strtoupper(app(StudentIdGenerationService::class)->configuredInstitutionPrefix() ?? '');

        return 'canteen.'.hash('sha256', $prefix).'.'.$setting;
    }
}
