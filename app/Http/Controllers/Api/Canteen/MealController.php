<?php

namespace App\Http\Controllers\Api\Canteen;

use App\Http\Controllers\Controller;
use App\Services\Canteen\CanteenMealLanes;
use App\Services\Canteen\CanteenMealPolicy;
use App\Services\Canteen\CanteenMealRedemptions;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class MealController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate(['device_id' => ['required', 'uuid'], 'device_key' => ['required', 'regex:/^[a-f0-9]{64}$/'], 'label' => ['required', 'string', 'max:80']]);
        DB::table('canteen_meal_devices')->insertOrIgnore(['device_id' => $data['device_id'], 'label' => $data['label'],
            'key_hash' => hash('sha256', $data['device_key']), 'registered_by' => $request->user()->id, 'created_at' => now(), 'updated_at' => now()]);
        $row = DB::table('canteen_meal_devices')->where('device_id', $data['device_id'])->first();
        abort_unless(hash_equals($row->key_hash, hash('sha256', $data['device_key'])), 403, 'This device could not be authenticated.');

        return response()->json(['device_id' => $row->device_id, 'label' => $row->label])->header('Cache-Control', 'no-store');
    }

    public function prepare(Request $request, CanteenMealLanes $lanes)
    {
        $data = $request->validate(['device_id' => ['required', 'uuid']]);
        $this->device($request, $data['device_id']);

        return response()->json($lanes->snapshot($data['device_id'], $request->user()->id,
            CarbonImmutable::parse($request->user()->currentAccessToken()->expires_at)))->header('Cache-Control', 'no-store');
    }

    public function redeem(Request $request, CanteenMealRedemptions $meals)
    {
        $data = $request->validate($this->rules());
        $this->device($request, $data['device_id']);
        $snapshot = $this->snapshot($data, app(CanteenMealPolicy::class)->prefix());
        abort_unless((string) $snapshot['attendant_user_id'] === (string) $request->user()->id, 403, 'Prepare this scanner for the current attendant before serving.');

        return response()->json($meals->redeem($request->user()->id, $data, $snapshot))->header('Cache-Control', 'no-store');
    }

    public function sync(Request $request, CanteenMealRedemptions $meals)
    {
        $data = $request->validate(['device_id' => ['required', 'uuid'], 'events' => ['required', 'array', 'min:1', 'max:100']]);
        $this->device($request, $data['device_id']);
        $results = [];
        foreach ($data['events'] as $event) {
            try {
                $event = Validator::make(is_array($event) ? $event : [], $this->rules() + ['offline_issued' => ['required', 'boolean']])->validate();
                abort_unless($event['device_id'] === $data['device_id'], 422, 'A queued event belongs to a different scanner.');
                $snapshot = $this->snapshot($event, app(CanteenMealPolicy::class)->prefix());
                $results[] = $meals->redeem((int) $snapshot['attendant_user_id'], $event, $snapshot, true, $request->user()->id, ! $event['offline_issued']);
            } catch (ValidationException|HttpExceptionInterface $error) {
                $id = is_array($event) && is_string($event['client_request_id'] ?? null) ? $event['client_request_id'] : '';
                if (Str::isUuid($id)) {
                    DB::table('canteen_meal_sync_rejections')->insertOrIgnore(['client_request_id' => $id, 'device_id' => $data['device_id'],
                        'institution_prefix' => app(CanteenMealPolicy::class)->prefix(), 'event' => json_encode($event),
                        'reason' => $error->getMessage(), 'synced_by' => $request->user()->id, 'created_at' => now()]);
                }
                $results[] = ['request_id' => $id, 'sync_rejected' => true, 'message' => 'This saved capture needs supervisor review. It was not accepted as a new redemption.'];
            }
        }

        return response()->json(['results' => $results, 'server_time' => now()->toIso8601String()])->header('Cache-Control', 'no-store');
    }

    private function rules(): array
    {
        return ['client_request_id' => ['required', 'uuid'], 'device_id' => ['required', 'uuid'], 'snapshot_id' => ['required', 'uuid'],
            'index_number' => ['required', 'string', 'max:64'], 'verification_method' => ['required', Rule::in(['ocr', 'manual'])],
            'meal' => ['required', Rule::in(CanteenMealPolicy::MEALS)], 'service_date' => ['required', 'date_format:Y-m-d'],
            'captured_at' => ['required', 'date', 'max:64']];
    }

    private function device(Request $request, string $device): void
    {
        $key = (string) $request->header('X-Canteen-Device-Key');
        $row = DB::table('canteen_meal_devices')->where('device_id', $device)->first();
        abort_unless(strlen($key) === 64 && $row && hash_equals($row->key_hash, hash('sha256', $key)), 403, 'This serving device is not registered.');
    }

    private function snapshot(array $data, string $prefix): array
    {
        $row = DB::table('canteen_meal_snapshots')->where('id', $data['snapshot_id'])->where('institution_prefix', $prefix)
            ->where('device_id', $data['device_id'])->first();
        abort_unless($row && $row->service_date === $data['service_date'], 422, 'The saved serving roster does not match this event.');
        $snapshot = json_decode($row->payload, true);
        $captured = CarbonImmutable::parse($data['captured_at']);
        abort_unless($captured->gte(CarbonImmutable::parse($snapshot['server_time'])->subSeconds(5))
            && $captured->lt(CarbonImmutable::parse($snapshot['expires_at'])) && $captured->lte(CarbonImmutable::now()->addMinute()), 422, 'The queued capture is outside its saved authorization period.');

        return $snapshot;
    }
}
