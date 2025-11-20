<?php

namespace App\Http\Controllers\Api\Communication;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Models\RecipientList;
use App\Services\Communication\Email\EmailServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class EmailController extends Controller
{
    /**
     * The Email Service implementation.
     */
    protected EmailServiceInterface $emailService;

    /**
     * Create a new controller instance.
     */
    public function __construct(EmailServiceInterface $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Send email to a single recipient.
     */
    public function sendSingle(Request $request): JsonResponse
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'recipient' => 'required|email',
            'subject' => 'required|string',
            'message' => 'required|string',
            'cc' => 'nullable|string',
            'bcc' => 'nullable|string',
            'template' => 'nullable|string',
            'attachments' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $options = [
                'user_id' => auth()->id(),
                'cc' => $request->input('cc'),
                'bcc' => $request->input('bcc'),
                'template' => $request->input('template'),
            ];

            if ($request->has('attachments')) {
                $options['attachments'] = $request->input('attachments');
            }

            $result = $this->emailService->sendSingle(
                $request->input('recipient'),
                $request->input('subject'),
                $request->input('message'),
                $options
            );

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Failed to send email', [
                'error' => $e->getMessage(),
                'recipient' => $request->input('recipient'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send email to multiple recipients.
     */
    public function sendBulk(Request $request): JsonResponse
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'recipients' => 'required|array',
            'recipients.*' => 'required|email',
            'subject' => 'required|string',
            'message' => 'required|string',
            'cc' => 'nullable|string',
            'bcc' => 'nullable|string',
            'template' => 'nullable|string',
            'attachments' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $options = [
                'user_id' => auth()->id(),
                'cc' => $request->input('cc'),
                'bcc' => $request->input('bcc'),
                'template' => $request->input('template'),
            ];

            if ($request->has('attachments')) {
                $options['attachments'] = $request->input('attachments');
            }

            $result = $this->emailService->sendBulk(
                $request->input('recipients'),
                $request->input('subject'),
                $request->input('message'),
                $options
            );

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Failed to send bulk email', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send bulk email: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send email to a predefined group.
     */
    public function sendToGroup(Request $request): JsonResponse
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'group_id' => 'required|integer|exists:recipient_lists,id',
            'subject' => 'required|string',
            'message' => 'required|string',
            'cc' => 'nullable|string',
            'bcc' => 'nullable|string',
            'template' => 'nullable|string',
            'attachments' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $options = [
                'user_id' => auth()->id(),
                'cc' => $request->input('cc'),
                'bcc' => $request->input('bcc'),
                'template' => $request->input('template'),
            ];

            if ($request->has('attachments')) {
                $options['attachments'] = $request->input('attachments');
            }

            $result = $this->emailService->sendToGroup(
                $request->input('group_id'),
                $request->input('subject'),
                $request->input('message'),
                $options
            );

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Failed to send group email', [
                'error' => $e->getMessage(),
                'group_id' => $request->input('group_id'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send group email: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get email logs.
     */
    public function getLogs(Request $request): JsonResponse
    {
        try {
            $query = EmailLog::query();

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

            if ($request->has('subject')) {
                $query->where('subject', 'like', '%'.$request->input('subject').'%');
            }

            // Pagination
            $perPage = $request->input('per_page', 15);
            $logs = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $logs,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get email logs', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get email logs: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get recipient lists available for email.
     */
    public function getRecipientLists(): JsonResponse
    {
        try {
            $lists = RecipientList::where('is_active', true)
                ->where(function ($query) {
                    $query->where('type', 'email')
                        ->orWhere('type', 'both');
                })
                ->with(['items' => function ($query) {
                    $query->where('is_active', true)->whereNotNull('email');
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
