<?php

namespace App\Http\Controllers\Api\Canteen;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureCanteenAccess;
use App\Models\CanteenDevice;
use App\Models\CanteenVerification;
use App\Models\Student;
use App\Services\Canteen\CanteenClearanceService;
use App\Services\Canteen\CanteenCredentialAuthenticator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Canteen app API. The app never infers financial clearance: every decision
 * comes from this endpoint (or from the local cache of an earlier response).
 * Offline captures upload through the sync endpoint and are reconciled
 * against the current decision at upload time.
 *
 * Authentication is enforced by the auth:sanctum route group; login is public.
 */
class CanteenController extends Controller
{
    public function __construct(private readonly CanteenClearanceService $clearance) {}

    /**
     * Issue an API token for a staff account.
     */
    public function login(Request $request): JsonResponse
    {
        $data = Validator::make($request->all(), [
            'username' => ['required', 'string', 'max:191'],
            'password' => ['required', 'string'],
            'device_id' => ['nullable', 'string', 'max:64'],
            'device_name' => ['nullable', 'string', 'max:120'],
            'platform' => ['nullable', 'string', 'max:32'],
        ])->validate();

        $user = app(CanteenCredentialAuthenticator::class)->authenticate($data['username'], $data['password']);

        if (! $user) {
            return response()->json(['message' => 'The username or password is incorrect.'], 401);
        }

        // Student portal accounts must not obtain canteen API tokens.
        if (! EnsureCanteenAccess::eligible($user)) {
            return response()->json(['message' => 'Canteen access is limited to staff accounts.'], 403);
        }

        if (filled($data['device_id'] ?? null)) {
            $this->touchDevice($data['device_id'], $data['device_name'] ?? null, $data['platform'] ?? null, $user->id);
        }

        $expiresAt = now()->addHours(12);
        $token = $user->createToken('canteen-app', ['canteen:access'], $expiresAt)->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'access' => 'canteen_attendant',
            'expires_at' => $expiresAt->toIso8601String(),
            'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
            'server_time' => now()->toIso8601String(),
        ]);
    }

    public function session(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'access' => 'canteen_attendant',
            'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
            'expires_at' => $user->currentAccessToken()->expires_at?->toIso8601String(),
            'server_time' => now()->toIso8601String(),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Signed out.']);
    }

    /**
     * Decide clearance for one scanned or typed index number.
     */
    public function verify(Request $request): JsonResponse
    {
        if (app(\App\Services\Canteen\CanteenMealPolicy::class)->isEnabled()) {
            return response()->json(['message' => 'Update Smart Canteen to the meal-redemption version before serving.'], 426);
        }
        $data = Validator::make($request->all(), [
            'index_number' => ['required', 'string', 'max:64'],
            'verification_method' => ['required', Rule::in([CanteenVerification::METHOD_OCR, CanteenVerification::METHOD_MANUAL])],
            'device_id' => ['required', 'string', 'max:64'],
            'client_request_id' => ['nullable', 'uuid'],
            'device_name' => ['nullable', 'string', 'max:120'],
        ])->validate();

        if (filled($data['client_request_id'] ?? null)) {
            $replay = CanteenVerification::query()
                ->where('client_request_id', $data['client_request_id'])
                ->first();

            if ($replay) {
                abort_unless($replay->attendant_user_id === $request->user()->id
                    && $replay->device_id === $data['device_id']
                    && $replay->index_number === $this->clearance->normalizeIndexNumber($data['index_number'])
                    && $replay->verification_method === $data['verification_method'], 409, 'Request identifier conflict.');

                return response()->json($this->verificationPayload($replay) + ['replay' => true]);
            }
        }

        $outcome = $this->clearance->verify($data['index_number']);

        $verification = CanteenVerification::create([
            'client_request_id' => $data['client_request_id'] ?? null,
            'student_id' => $outcome['student']?->id,
            'attendant_user_id' => $request->user()->id,
            'device_id' => $data['device_id'],
            'index_number' => $this->clearance->normalizeIndexNumber($data['index_number']),
            'verification_method' => $data['verification_method'],
            'decision' => $outcome['decision'],
            'is_offline' => false,
            'student_snapshot' => $outcome['student'] ? $this->snapshot($outcome['student']) : null,
            'payment_percentage' => $outcome['payment_percentage'],
            'decision_context' => ($outcome['decision_context'] ?? []) + ['reason' => $outcome['reason']],
            'verified_at' => now(),
            'synced_at' => now(),
        ]);

        $this->touchDevice($data['device_id'], $data['device_name'] ?? null, null, $request->user()->id);

        return response()->json($this->verificationPayload($verification) + ['replay' => false]);
    }

    /**
     * Cursor-paged roster snapshot with clearance, so the app can cache the
     * student body for offline use and request deltas with ?since=.
     */
    public function roster(Request $request): JsonResponse
    {
        $data = Validator::make($request->all(), [
            'since' => ['nullable', 'date'],
            'cursor' => ['nullable', 'integer', 'min:0'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:'.config('canteen.roster_page_size', 500)],
        ])->validate();

        $limit = (int) ($data['limit'] ?? config('canteen.roster_page_size', 500));

        $query = Student::query()->orderBy('id');

        // Eligibility also changes through imports, revocations, policy and
        // payments. A Student.updated_at filter would miss those changes.
        // Return complete paged snapshots until a clearance change-feed exists.

        if (filled($data['cursor'] ?? null)) {
            $query->where('id', '>', (int) $data['cursor']);
        }

        $students = $query->limit($limit + 1)->get();
        $hasMore = $students->count() > $limit;
        $students = $students->take($limit)->values();

        $clearances = $this->clearance->verifyMany($students);

        $rows = $students->map(function (Student $student) use ($clearances) {
            $clearance = $clearances[$student->id] ?? ['decision' => CanteenVerification::DECISION_REVIEW_REQUIRED, 'payment_percentage' => null];

            return [
                'index_number' => $student->student_id,
                'first_name' => $student->first_name,
                'middle_name' => $student->other_name,
                'last_name' => $student->last_name,
                'full_name' => $this->fullName($student),
                'programme' => [
                    'name' => $student->collegeClass?->name,
                    'code' => $student->collegeClass?->short_name,
                ],
                'status' => $student->status,
                'clearance' => [
                    'decision' => $clearance['decision'],
                    'cleared' => $clearance['decision'] === CanteenVerification::DECISION_APPROVED,
                    'payment_percentage' => $clearance['payment_percentage'],
                    'decision_context' => $clearance['decision_context'] ?? null,
                ],
                'updated_at' => $student->updated_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'students' => $rows,
            'next_cursor' => $hasMore ? (int) $students->last()?->id : null,
            'has_more' => $hasMore,
            'since' => $data['since'] ?? null,
            'sync_mode' => 'full_snapshot',
            'server_time' => now()->toIso8601String(),
        ]);
    }

    /**
     * Upload verifications captured while offline. Idempotent on
     * client_request_id; each record is reconciled against the current
     * decision so stale offline approvals are visible to finance.
     */
    public function sync(Request $request): JsonResponse
    {
        if (app(\App\Services\Canteen\CanteenMealPolicy::class)->isEnabled()) {
            return response()->json(['message' => 'Use meal-redemption sync. Clearance-only records cannot redeem meals.'], 426);
        }
        $data = Validator::make($request->all(), [
            'verifications' => ['required', 'array', 'min:1', 'max:'.config('canteen.verification_batch_size', 500)],
            'verifications.*.client_request_id' => ['required', 'uuid'],
            'verifications.*.index_number' => ['required', 'string', 'max:64'],
            'verifications.*.verification_method' => ['required', Rule::in([CanteenVerification::METHOD_OCR, CanteenVerification::METHOD_MANUAL])],
            'verifications.*.device_id' => ['required', 'string', 'max:64'],
            'verifications.*.verified_at' => ['required', 'date'],
            'verifications.*.decision' => ['required', Rule::in([
                CanteenVerification::DECISION_APPROVED,
                CanteenVerification::DECISION_DENIED,
                CanteenVerification::DECISION_NOT_FOUND,
                CanteenVerification::DECISION_REVIEW_REQUIRED,
            ])],
        ])->validate();

        $accepted = 0;
        $duplicates = 0;
        $mismatches = [];

        foreach ($data['verifications'] as $entry) {
            $exists = CanteenVerification::query()->where('client_request_id', $entry['client_request_id'])->exists();

            if ($exists) {
                $duplicates++;

                continue;
            }

            $outcome = $this->clearance->verify($entry['index_number']);

            $verification = CanteenVerification::create([
                'client_request_id' => $entry['client_request_id'],
                'student_id' => $outcome['student']?->id,
                'attendant_user_id' => $request->user()->id,
                'device_id' => $entry['device_id'],
                'index_number' => $this->clearance->normalizeIndexNumber($entry['index_number']),
                'verification_method' => $entry['verification_method'],
                'decision' => $entry['decision'],
                'is_offline' => true,
                'reconciled_decision' => $outcome['decision'],
                'student_snapshot' => $outcome['student'] ? $this->snapshot($outcome['student']) : null,
                'payment_percentage' => $outcome['payment_percentage'],
                'decision_context' => ($outcome['decision_context'] ?? []) + ['reason' => $outcome['reason']],
                'verified_at' => $entry['verified_at'],
                'synced_at' => now(),
            ]);

            $accepted++;

            if ($outcome['decision'] !== $verification->decision) {
                $mismatches[] = [
                    'client_request_id' => $entry['client_request_id'],
                    'index_number' => $verification->index_number,
                    'offline_decision' => $verification->decision,
                    'current_decision' => $outcome['decision'],
                ];
            }
        }

        return response()->json([
            'accepted' => $accepted,
            'duplicates' => $duplicates,
            'mismatches' => $mismatches,
            'server_time' => now()->toIso8601String(),
        ]);
    }

    private function verificationPayload(CanteenVerification $verification): array
    {
        $snapshot = $verification->student_snapshot;

        return [
            'request_id' => $verification->client_request_id,
            'decision' => $verification->decision,
            'index_number' => $verification->index_number,
            'verification_method' => $verification->verification_method,
            'student' => $snapshot,
            'payment_percentage' => $verification->payment_percentage !== null ? (float) $verification->payment_percentage : null,
            'decision_context' => $verification->decision_context,
            'verified_at' => $verification->verified_at?->toIso8601String(),
        ];
    }

    /**
     * @return array{index_number: string, first_name: string|null, middle_name: string|null, last_name: string|null, full_name: string, programme: array{name: string|null, code: string|null}, status: string|null}
     */
    private function snapshot(Student $student): array
    {
        return [
            'index_number' => $student->student_id,
            'first_name' => $student->first_name,
            'middle_name' => $student->other_name,
            'last_name' => $student->last_name,
            'full_name' => $this->fullName($student),
            'programme' => [
                'name' => $student->collegeClass?->name,
                'code' => $student->collegeClass?->short_name,
            ],
            'status' => $student->status,
        ];
    }

    private function fullName(Student $student): string
    {
        return trim(implode(' ', array_filter([$student->first_name, $student->other_name, $student->last_name])));
    }

    private function touchDevice(string $deviceId, ?string $name, ?string $platform, int $userId): void
    {
        CanteenDevice::updateOrCreate(
            ['device_id' => $deviceId],
            array_filter([
                'name' => $name,
                'platform' => $platform,
                'registered_by' => $userId,
                'last_seen_at' => now(),
            ], fn ($value) => $value !== null)
        );
    }
}
