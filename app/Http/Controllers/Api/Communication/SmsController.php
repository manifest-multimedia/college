<?php

namespace App\Http\Controllers\Api\Communication;

use App\Http\Controllers\Controller;
use App\Models\RecipientList;
use App\Models\SmsLog;
use App\Services\Communication\SMS\SmsServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SmsController extends Controller
{
    /**
     * The SMS Service implementation.
     */
    protected SmsServiceInterface $smsService;

    /**
     * Create a new controller instance.
     */
    public function __construct(SmsServiceInterface $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Send SMS to a single recipient.
     */
    public function sendSingle(Request $request): JsonResponse
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'recipient' => 'required|string',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->smsService->sendSingle(
                $request->input('recipient'),
                $request->input('message'),
                [
                    'user_id' => auth()->id(),
                ]
            );

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Failed to send SMS', [
                'error' => $e->getMessage(),
                'recipient' => $request->input('recipient'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send SMS: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send SMS to multiple recipients.
     */
    public function sendBulk(Request $request): JsonResponse
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'recipients' => 'required|array',
            'recipients.*' => 'required|string',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->smsService->sendBulk(
                $request->input('recipients'),
                $request->input('message'),
                [
                    'user_id' => auth()->id(),
                ]
            );

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Failed to send bulk SMS', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send bulk SMS: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send SMS to a predefined group.
     */
    public function sendToGroup(Request $request): JsonResponse
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'group_id' => 'required|integer|exists:recipient_lists,id',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->smsService->sendToGroup(
                $request->input('group_id'),
                $request->input('message'),
                [
                    'user_id' => auth()->id(),
                ]
            );

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Failed to send group SMS', [
                'error' => $e->getMessage(),
                'group_id' => $request->input('group_id'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send group SMS: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get SMS logs.
     */
    public function getLogs(Request $request): JsonResponse
    {
        try {
            $query = SmsLog::query();

            // Filtering
            if ($request->has('type')) {
                $query->where('type', $request->input('type'));
            }

            if ($request->has('status')) {
                $query->where('status', $request->input('status'));
            }

            if ($request->has('recipient')) {
                $query->where('recipient', 'like', '%'.$request->input('recipient').'%');
            }

            // Pagination
            $perPage = $request->input('per_page', 15);
            $logs = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $logs,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get SMS logs', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get SMS logs: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get recipient lists available for SMS.
     */
    public function getRecipientLists(): JsonResponse
    {
        try {
            $lists = RecipientList::where('is_active', true)
                ->where(function ($query) {
                    $query->where('type', 'sms')
                        ->orWhere('type', 'both');
                })
                ->with(['items' => function ($query) {
                    $query->where('is_active', true)->whereNotNull('phone');
                }])
                ->get();

            return response()->json([
                'success' => true,
                'data' => $lists,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get recipient lists', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get recipient lists: '.$e->getMessage(),
            ], 500);
        }
    }
}
