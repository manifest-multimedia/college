<?php

namespace App\Services\Canteen;

use App\Models\Student;
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class CanteenMealRedemptions
{
    public function __construct(private CanteenMealPolicy $policy, private CanteenClearanceService $clearance, private CanteenMealLanes $lanes) {}

    public function redeem(int $actor, array $data, ?array $snapshot = null, bool $offline = false, ?int $uploader = null, bool $recordOnly = false): array
    {
        $data['index_number'] = $this->clearance->normalizeIndexNumber($data['index_number']);
        $fingerprint = hash('sha256', json_encode([$this->policy->prefix(), $actor, $data['device_id'], $data['index_number'], $data['verification_method'],
            $data['snapshot_id'] ?? null, $data['meal'] ?? null, $data['service_date'] ?? null, $data['captured_at'] ?? null], JSON_THROW_ON_ERROR));
        for ($race = 0; $race < 2; $race++) {
            try {
                return DB::transaction(function () use ($actor, $data, $snapshot, $offline, $uploader, $recordOnly, $fingerprint) {
                    Student::where('student_id', $data['index_number'])->lockForUpdate()->first();
                    $amend = null;
                    if ($replay = $this->replay($data['client_request_id'], $fingerprint)) {
                        // A transport failure can conceal an online denial after a
                        // valid roster was cached. Preserve that first response and
                        // explicitly reconcile the meal actually issued offline.
                        if ($offline && ! $recordOnly && ($snapshot['students'][$data['index_number']]['decision'] ?? null) === 'approved'
                            && ! in_array($replay['redemption']['status'], ['redeemed', 'already_redeemed'], true)) {
                            $amend = DB::table('canteen_meal_attempts')->where('client_request_id', $data['client_request_id'])->first();
                        } else {
                            return $replay;
                        }
                    }
                    $current = $this->policy->get();
                    $time = $offline ? CarbonImmutable::parse($data['captured_at']) : CarbonImmutable::now();
                    $plan = $current['enabled'] || $offline ? $this->lanes->existingOrCreate($snapshot['service_date'] ?? null, $offline) : null;
                    $policy = $plan['policy'] ?? $current;
                    $window = $this->policy->window($policy, $time);
                    $index = $data['index_number'];
                    $live = $this->clearance->verify($index);
                    $saved = $snapshot['students'][$index] ?? null;
                    $decision = $offline && $saved ? $saved['decision'] : $live['decision'];
                    $student = $offline && $saved ? array_intersect_key($saved, array_flip(['index_number', 'full_name', 'programme'])) : $this->studentSnapshot($live['student']);
                    $percentage = $offline && $saved ? $saved['payment_percentage'] : $live['payment_percentage'];
                    $context = $offline && $saved ? $saved['decision_context'] : $live['decision_context'] + ['reason' => $live['reason']];
                    $route = $plan['students'][$index] ?? null;
                    $status = ! $current['enabled'] && ! $offline ? 'not_configured' : (! $window ? 'window_closed' : 'review_required');
                    if ($window && ($current['enabled'] || $offline)) {
                        if (($data['meal'] ?? $window['meal']) !== $window['meal'] || ($data['service_date'] ?? $window['service_date']) !== $window['service_date']) {
                            $status = 'window_closed';
                        } elseif ($decision === 'not_found') {
                            $status = 'not_found';
                        } elseif (! $route || $route['device_id'] !== $data['device_id']) {
                            $status = 'wrong_lane';
                        } else {
                            $status = match ($decision) {
                                'approved' => 'redeemed', 'denied' => 'not_qualified', 'not_found' => 'not_found', default => 'review_required'
                            };
                        }
                    }
                    if ($recordOnly && $status === 'redeemed') {
                        $status = 'not_recorded';
                    }
                    $receipt = null;
                    if ($window) {
                        $key = ['institution_prefix' => $this->policy->prefix(), 'index_number' => $index,
                            'service_date' => $window['service_date'], 'meal' => $window['meal']];
                        $receipt = DB::table('canteen_meal_redemptions')->where($key)->first();
                        if ($receipt && $status !== 'window_closed' && $status !== 'not_configured') {
                            $status = 'already_redeemed';
                        } elseif ($status === 'redeemed') {
                            $id = DB::table('canteen_meal_redemptions')->insertGetId($key + [
                                'student_id' => $live['student']?->id, 'client_request_id' => $data['client_request_id'],
                                'device_id' => $data['device_id'], 'attendant_user_id' => $actor, 'is_offline' => $offline,
                                'snapshot_id' => $data['snapshot_id'] ?? null, 'redeemed_at' => $time, 'created_at' => now(), 'updated_at' => now(),
                            ]);
                            $receipt = DB::table('canteen_meal_redemptions')->where('id', $id)->first();
                        }
                    }
                    $payload = ['request_id' => $data['client_request_id'], 'index_number' => $index,
                        'verification_method' => $data['verification_method'], 'decision' => $decision,
                        'student' => $student, 'payment_percentage' => $percentage, 'decision_context' => $context,
                        'verified_at' => $time->toIso8601String(),
                        'redemption' => ['status' => $status, 'meal' => $window['meal'] ?? ($data['meal'] ?? null),
                            'service_date' => $window['service_date'] ?? $time->setTimezone($policy['timezone'])->toDateString(),
                            'receipt_id' => $receipt?->id, 'redeemed_at' => $receipt ? CarbonImmutable::parse($receipt->redeemed_at)->toIso8601String() : null,
                            'lane' => $route['lane'] ?? null,
                            'roster_saved_at' => $offline ? ($snapshot['server_time'] ?? null) : null, 'is_offline' => $offline, 'sync_status' => 'synced',
                            'reconciled_decision' => $offline ? $live['decision'] : null], 'replay' => false];
                    $attempt = ['client_request_id' => $data['client_request_id'], 'institution_prefix' => $this->policy->prefix(),
                        'index_number' => $index, 'device_id' => $data['device_id'], 'attendant_user_id' => $actor,
                        'request_fingerprint' => $fingerprint, 'status' => $status, 'is_offline' => $offline, 'synced_by' => $uploader ?? $actor,
                        'response' => json_encode($payload), 'created_at' => now(), 'updated_at' => now()];
                    if ($amend) {
                        $attempt['original_response'] = $amend->original_response ?? $amend->response;
                        $attempt['created_at'] = $amend->created_at;
                        DB::table('canteen_meal_attempts')->where('client_request_id', $data['client_request_id'])->update($attempt);
                    } else {
                        DB::table('canteen_meal_attempts')->insert($attempt);
                    }

                    return $payload;
                }, 3);
            } catch (UniqueConstraintViolationException $error) {
                if ($replay = $this->replay($data['client_request_id'], $fingerprint)) {
                    return $replay;
                }
                if ($race === 1) {
                    throw $error;
                }
            }
        }
        throw new \LogicException('Redemption transaction did not complete.');
    }

    private function replay(string $id, string $fingerprint): ?array
    {
        $row = DB::table('canteen_meal_attempts')->where('client_request_id', $id)->first();
        if (! $row) {
            return null;
        }
        abort_unless(hash_equals($row->request_fingerprint, $fingerprint), 409, 'Request identifier conflict.');
        $response = json_decode($row->response, true, 512, JSON_THROW_ON_ERROR);
        $response['replay'] = true;

        return $response;
    }

    private function studentSnapshot(?Student $student): ?array
    {
        return $student ? ['index_number' => $student->student_id,
            'full_name' => trim(implode(' ', array_filter([$student->first_name, $student->other_name, $student->last_name]))),
            'programme' => ['name' => $student->collegeClass?->name, 'code' => $student->collegeClass?->short_name]] : null;
    }
}
