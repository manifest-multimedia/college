<?php

namespace App\Services\Canteen;

use App\Services\StudentIdGenerationService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CanteenMealPolicy
{
    public const MEALS = ['breakfast', 'lunch', 'supper'];

    public function prefix(): string
    {
        $prefix = app(StudentIdGenerationService::class)->configuredInstitutionPrefix();
        if (! $prefix) {
            throw ValidationException::withMessages(['institution' => 'Configure the institution prefix first.']);
        }

        return strtoupper($prefix);
    }

    public function isEnabled(): bool
    {
        return app(StudentIdGenerationService::class)->configuredInstitutionPrefix() !== null && $this->get()['enabled'];
    }

    public function get(): array
    {
        $row = DB::table('canteen_meal_policies')->where('institution_prefix', $this->prefix())->first();

        return $row ? json_decode($row->configuration, true, 512, JSON_THROW_ON_ERROR) + ['version' => $row->version]
            : ['enabled' => false, 'timezone' => 'Africa/Accra', 'windows' => [], 'offline_policy' => 'assigned_device', 'version' => null];
    }

    public function save(array $input, int $actor, ?string $expectedVersion = null): array
    {
        $data = Validator::make($input, [
            'enabled' => ['required', 'boolean'], 'timezone' => ['required', 'timezone:all'],
            'offline_policy' => ['required', Rule::in(['assigned_device'])],
            'windows' => ['required', 'array', 'size:3'],
            'windows.*.meal' => ['required', Rule::in(self::MEALS), 'distinct'],
            'windows.*.opens' => ['required', 'date_format:H:i'],
            'windows.*.closes' => ['required', 'date_format:H:i'],
        ])->validate();
        $data['windows'] = array_map(fn ($window) => array_intersect_key($window, array_flip(['meal', 'opens', 'closes'])), $data['windows']);
        $ordered = $data['windows'];
        usort($ordered, fn ($a, $b) => strcmp($a['opens'], $b['opens']));
        $end = null;
        foreach ($ordered as $window) {
            if ($window['opens'] >= $window['closes'] || ($end !== null && $window['opens'] < $end)) {
                throw ValidationException::withMessages(['windows' => 'Meal windows must not overlap or cross midnight.']);
            }
            $end = $window['closes'];
        }
        $data['enabled'] = (bool) $data['enabled'];
        $data['windows'] = array_map(fn ($meal) => array_values(array_filter($data['windows'], fn ($w) => $w['meal'] === $meal))[0], self::MEALS);
        $version = (string) Str::uuid();
        DB::transaction(function () use ($data, $version, $actor, $expectedVersion) {
            $prefix = $this->prefix();
            $previous = DB::table('canteen_meal_policies')->where('institution_prefix', $prefix)->lockForUpdate()->first();
            if ($expectedVersion !== null && $expectedVersion !== ($previous?->version ?? '')) {
                throw ValidationException::withMessages(['version' => 'These settings changed in another session. Reload the page and review the current times.']);
            }
            DB::table('canteen_meal_policies')->updateOrInsert(['institution_prefix' => $prefix], [
                'configuration' => json_encode($data, JSON_THROW_ON_ERROR), 'version' => $version, 'updated_by' => $actor,
                'created_at' => $previous?->created_at ?? now(), 'updated_at' => now(),
            ]);
            DB::table('canteen_meal_policy_changes')->insert([
                'institution_prefix' => $prefix, 'version' => $version, 'previous_version' => $previous?->version,
                'previous_configuration' => $previous?->configuration,
                'configuration' => json_encode($data, JSON_THROW_ON_ERROR), 'changed_by' => $actor, 'created_at' => now(),
            ]);
        }, 3);

        return $data + ['version' => $version];
    }

    public function window(array $policy, CarbonImmutable $time, ?string $meal = null): ?array
    {
        if (! $policy['enabled']) {
            return null;
        }
        $local = $time->setTimezone($policy['timezone']);
        foreach ($policy['windows'] as $window) {
            $opens = CarbonImmutable::parse($local->toDateString().' '.$window['opens'], $policy['timezone']);
            $closes = CarbonImmutable::parse($local->toDateString().' '.$window['closes'], $policy['timezone']);
            if (($meal === null || $window['meal'] === $meal) && $time->gte($opens) && $time->lt($closes)) {
                return ['meal' => $window['meal'], 'service_date' => $local->toDateString(),
                    'opens_at' => $opens->toIso8601String(), 'closes_at' => $closes->toIso8601String()];
            }
        }

        return null;
    }
}
