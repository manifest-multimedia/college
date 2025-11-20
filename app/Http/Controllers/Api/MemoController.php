<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Memo;
use App\Models\MemoAttachment;
use App\Services\Memo\MemoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MemoController extends Controller
{
    protected $memoService;

    public function __construct(MemoService $memoService)
    {
        $this->memoService = $memoService;
    }

    /**
     * Get a list of memos.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Memo::with(['user', 'department', 'recipient', 'recipientDepartment']);

            // Apply filters
            if ($request->has('status')) {
                $query->withStatus($request->input('status'));
            }

            if ($request->has('priority')) {
                $query->withPriority($request->input('priority'));
            }

            if ($request->input('created_by_me') === 'true') {
                $query->createdBy(Auth::id());
            }

            if ($request->input('sent_to_me') === 'true') {
                $query->sentTo(Auth::id());
            }

            if ($request->input('sent_to_my_department') === 'true' && Auth::user()->department_id) {
                $query->sentToDepartment(Auth::user()->department_id);
            }

            // Pagination
            $perPage = $request->input('per_page', 15);
            $memos = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $memos,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get memos', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get memos: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created memo.
     */
    public function store(Request $request): JsonResponse
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'department_id' => 'nullable|exists:departments,id',
            'recipient_id' => 'nullable|exists:users,id',
            'recipient_department_id' => 'nullable|exists:departments,id',
            'priority' => 'nullable|in:low,medium,high',
            'requested_action' => 'nullable|string|max:255',
            'status' => 'nullable|in:draft,pending',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240', // 10MB max file size
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $data = $request->all();
            $attachments = $request->file('attachments') ?? [];

            $result = $this->memoService->createMemo($data, $attachments);

            return response()->json($result, $result['success'] ? 201 : 500);
        } catch (\Exception $e) {
            Log::error('Failed to create memo', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create memo: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified memo.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $memo = Memo::with([
                'user',
                'department',
                'recipient',
                'recipientDepartment',
                'attachments',
                'actions' => function ($query) {
                    $query->with(['user', 'forwardedToUser', 'forwardedToDepartment'])
                        ->orderBy('created_at', 'desc');
                },
            ])->findOrFail($id);

            // Record that the memo was viewed
            if (Auth::id() !== $memo->user_id) {
                $this->memoService->recordAction($id, 'viewed');
            }

            return response()->json([
                'success' => true,
                'data' => $memo,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get memo', [
                'error' => $e->getMessage(),
                'id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get memo: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified memo.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'recipient_id' => 'nullable|exists:users,id',
            'recipient_department_id' => 'nullable|exists:departments,id',
            'priority' => 'nullable|in:low,medium,high',
            'requested_action' => 'nullable|string|max:255',
            'status' => 'nullable|in:draft,pending',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240', // 10MB max file size
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $data = $request->all();
            $attachments = $request->file('attachments') ?? [];

            $result = $this->memoService->updateMemo($id, $data, $attachments);

            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            Log::error('Failed to update memo', [
                'error' => $e->getMessage(),
                'id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update memo: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Forward a memo to another user or department.
     */
    public function forward(Request $request, int $id): JsonResponse
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'forward_to_user_id' => 'required_without:forward_to_department_id|nullable|exists:users,id',
            'forward_to_department_id' => 'required_without:forward_to_user_id|nullable|exists:departments,id',
            'comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->memoService->forwardMemo($id, $request->all());

            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            Log::error('Failed to forward memo', [
                'error' => $e->getMessage(),
                'id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to forward memo: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Approve a memo.
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->memoService->approveMemo($id, $request->all());

            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            Log::error('Failed to approve memo', [
                'error' => $e->getMessage(),
                'id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve memo: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reject a memo.
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'comment' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->memoService->rejectMemo($id, $request->all());

            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            Log::error('Failed to reject memo', [
                'error' => $e->getMessage(),
                'id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reject memo: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Complete a memo.
     */
    public function complete(Request $request, int $id): JsonResponse
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->memoService->completeMemo($id, $request->all());

            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            Log::error('Failed to complete memo', [
                'error' => $e->getMessage(),
                'id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to complete memo: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark items as procured.
     */
    public function markAsProcured(Request $request, int $id): JsonResponse
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->memoService->markAsProcured($id, $request->all());

            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            Log::error('Failed to mark items as procured', [
                'error' => $e->getMessage(),
                'id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark items as procured: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark items as delivered to stores.
     */
    public function markAsDelivered(Request $request, int $id): JsonResponse
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->memoService->markAsDelivered($id, $request->all());

            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            Log::error('Failed to mark items as delivered', [
                'error' => $e->getMessage(),
                'id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark items as delivered: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark items as audited by stores.
     */
    public function markAsAudited(Request $request, int $id): JsonResponse
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->memoService->markAsAudited($id, $request->all());

            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            Log::error('Failed to mark items as audited', [
                'error' => $e->getMessage(),
                'id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark items as audited: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete an attachment.
     */
    public function deleteAttachment(int $id, int $attachmentId): JsonResponse
    {
        try {
            // Verify that the attachment belongs to the specified memo
            $attachment = MemoAttachment::where('id', $attachmentId)
                ->where('memo_id', $id)
                ->firstOrFail();

            $result = $this->memoService->deleteAttachment($attachmentId);

            return response()->json([
                'success' => $result,
                'message' => $result ? 'Attachment deleted successfully' : 'Failed to delete attachment',
            ], $result ? 200 : 500);
        } catch (\Exception $e) {
            Log::error('Failed to delete attachment', [
                'error' => $e->getMessage(),
                'memo_id' => $id,
                'attachment_id' => $attachmentId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete attachment: '.$e->getMessage(),
            ], 500);
        }
    }
}
