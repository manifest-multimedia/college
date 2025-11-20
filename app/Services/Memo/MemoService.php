<?php

namespace App\Services\Memo;

use App\Models\Department;
use App\Models\Memo;
use App\Models\MemoAction;
use App\Models\MemoAttachment;
use App\Models\User;
use App\Services\Communication\Email\EmailServiceInterface;
use App\Services\Communication\SMS\SmsServiceInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MemoService
{
    protected $emailService;

    protected $smsService;

    public function __construct(EmailServiceInterface $emailService, SmsServiceInterface $smsService)
    {
        $this->emailService = $emailService;
        $this->smsService = $smsService;
    }

    /**
     * Create a new memo
     */
    public function createMemo(array $data, array $attachments = []): array
    {
        try {
            DB::beginTransaction();

            // Create the memo
            $memo = Memo::create([
                'title' => $data['title'],
                'description' => $data['description'],
                'user_id' => Auth::id(),
                'department_id' => $data['department_id'] ?? Auth::user()->department_id ?? null,
                'recipient_id' => $data['recipient_id'] ?? null,
                'recipient_department_id' => $data['recipient_department_id'] ?? null,
                'priority' => $data['priority'] ?? 'medium',
                'requested_action' => $data['requested_action'] ?? null,
                'status' => $data['status'] ?? 'pending',
            ]);

            // Record the creation action
            $this->recordAction($memo->id, 'created');

            // Process attachments if any
            if (! empty($attachments)) {
                foreach ($attachments as $attachment) {
                    $this->addAttachment($memo->id, $attachment);
                }
            }

            // Send notifications
            $this->sendMemoNotifications($memo, 'created');

            DB::commit();

            return [
                'success' => true,
                'memo' => $memo,
                'message' => 'Memo created successfully',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating memo', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create memo: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Update an existing memo
     */
    public function updateMemo(int $memoId, array $data, array $attachments = []): array
    {
        try {
            DB::beginTransaction();

            $memo = Memo::findOrFail($memoId);

            // Check permissions (only creator can update if still in draft)
            if ($memo->status !== 'draft' && $memo->user_id !== Auth::id()) {
                return [
                    'success' => false,
                    'message' => 'You do not have permission to update this memo',
                ];
            }

            // Update memo
            $memo->update([
                'title' => $data['title'] ?? $memo->title,
                'description' => $data['description'] ?? $memo->description,
                'recipient_id' => $data['recipient_id'] ?? $memo->recipient_id,
                'recipient_department_id' => $data['recipient_department_id'] ?? $memo->recipient_department_id,
                'priority' => $data['priority'] ?? $memo->priority,
                'requested_action' => $data['requested_action'] ?? $memo->requested_action,
                'status' => $data['status'] ?? $memo->status,
            ]);

            // Process new attachments if any
            if (! empty($attachments)) {
                foreach ($attachments as $attachment) {
                    $this->addAttachment($memoId, $attachment);
                }
            }

            DB::commit();

            return [
                'success' => true,
                'memo' => $memo,
                'message' => 'Memo updated successfully',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating memo', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'memo_id' => $memoId,
                'data' => $data,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to update memo: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Forward a memo to another user or department
     */
    public function forwardMemo(int $memoId, array $data): array
    {
        try {
            DB::beginTransaction();

            $memo = Memo::findOrFail($memoId);

            // Update memo status
            $memo->update([
                'status' => 'forwarded',
                'recipient_id' => $data['forward_to_user_id'] ?? null,
                'recipient_department_id' => $data['forward_to_department_id'] ?? null,
            ]);

            // Record the action
            $this->recordAction(
                $memoId,
                'forwarded',
                $data['comment'] ?? null,
                $data['forward_to_user_id'] ?? null,
                $data['forward_to_department_id'] ?? null
            );

            // Send notifications
            $this->sendMemoNotifications($memo, 'forwarded');

            DB::commit();

            return [
                'success' => true,
                'memo' => $memo,
                'message' => 'Memo forwarded successfully',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error forwarding memo', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'memo_id' => $memoId,
                'data' => $data,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to forward memo: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Approve a memo
     */
    public function approveMemo(int $memoId, array $data): array
    {
        try {
            DB::beginTransaction();

            $memo = Memo::findOrFail($memoId);

            // Update memo status
            $memo->update([
                'status' => 'approved',
            ]);

            // Record the action
            $this->recordAction($memoId, 'approved', $data['comment'] ?? null);

            // Send notifications
            $this->sendMemoNotifications($memo, 'approved');

            DB::commit();

            return [
                'success' => true,
                'memo' => $memo,
                'message' => 'Memo approved successfully',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving memo', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'memo_id' => $memoId,
                'data' => $data,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to approve memo: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Reject a memo
     */
    public function rejectMemo(int $memoId, array $data): array
    {
        try {
            DB::beginTransaction();

            $memo = Memo::findOrFail($memoId);

            // Update memo status
            $memo->update([
                'status' => 'rejected',
            ]);

            // Record the action
            $this->recordAction($memoId, 'rejected', $data['comment'] ?? null);

            // Send notifications
            $this->sendMemoNotifications($memo, 'rejected');

            DB::commit();

            return [
                'success' => true,
                'memo' => $memo,
                'message' => 'Memo rejected successfully',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting memo', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'memo_id' => $memoId,
                'data' => $data,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to reject memo: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Mark a memo as completed
     */
    public function completeMemo(int $memoId, array $data): array
    {
        try {
            DB::beginTransaction();

            $memo = Memo::findOrFail($memoId);

            // Update memo status
            $memo->update([
                'status' => 'completed',
            ]);

            // Record the action
            $this->recordAction($memoId, 'completed', $data['comment'] ?? null);

            // Send notifications
            $this->sendMemoNotifications($memo, 'completed');

            DB::commit();

            return [
                'success' => true,
                'memo' => $memo,
                'message' => 'Memo marked as completed successfully',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error completing memo', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'memo_id' => $memoId,
                'data' => $data,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to complete memo: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Mark items as procured
     */
    public function markAsProcured(int $memoId, array $data): array
    {
        try {
            DB::beginTransaction();

            $memo = Memo::findOrFail($memoId);

            // Record the action
            $this->recordAction($memoId, 'procured', $data['comment'] ?? null);

            // Send notifications
            $this->sendMemoNotifications($memo, 'procured');

            DB::commit();

            return [
                'success' => true,
                'message' => 'Items marked as procured successfully',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error marking as procured', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'memo_id' => $memoId,
                'data' => $data,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to mark as procured: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Mark items as delivered to stores
     */
    public function markAsDelivered(int $memoId, array $data): array
    {
        try {
            DB::beginTransaction();

            $memo = Memo::findOrFail($memoId);

            // Record the action
            $this->recordAction($memoId, 'delivered', $data['comment'] ?? null);

            // Send notifications to stores manager
            $this->sendMemoNotifications($memo, 'delivered');

            DB::commit();

            return [
                'success' => true,
                'message' => 'Items marked as delivered to stores successfully',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error marking as delivered', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'memo_id' => $memoId,
                'data' => $data,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to mark as delivered: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Mark items as audited by stores
     */
    public function markAsAudited(int $memoId, array $data): array
    {
        try {
            DB::beginTransaction();

            $memo = Memo::findOrFail($memoId);

            // Record the action
            $this->recordAction($memoId, 'audited', $data['comment'] ?? null);

            // Send notifications to memo creator
            $this->sendMemoNotifications($memo, 'audited');

            DB::commit();

            return [
                'success' => true,
                'message' => 'Items marked as audited successfully',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error marking as audited', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'memo_id' => $memoId,
                'data' => $data,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to mark as audited: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Add an attachment to a memo
     */
    public function addAttachment(int $memoId, UploadedFile $file): ?MemoAttachment
    {
        try {
            // Store the file
            $filename = uniqid().'_'.$file->getClientOriginalName();
            $path = $file->storeAs('memo-attachments', $filename, 'public');

            // Create attachment record
            return MemoAttachment::create([
                'memo_id' => $memoId,
                'user_id' => Auth::id(),
                'filename' => $filename,
                'original_filename' => $file->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error adding memo attachment', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'memo_id' => $memoId,
                'filename' => $file->getClientOriginalName(),
            ]);

            return null;
        }
    }

    /**
     * Delete an attachment
     */
    public function deleteAttachment(int $attachmentId): bool
    {
        try {
            $attachment = MemoAttachment::findOrFail($attachmentId);

            // Only the creator of the attachment or the memo creator can delete
            if (Auth::id() !== $attachment->user_id && Auth::id() !== $attachment->memo->user_id) {
                return false;
            }

            // Delete the file
            Storage::disk('public')->delete($attachment->file_path);

            // Delete the record
            $attachment->delete();

            return true;
        } catch (\Exception $e) {
            Log::error('Error deleting memo attachment', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'attachment_id' => $attachmentId,
            ]);

            return false;
        }
    }

    /**
     * Record an action on a memo
     */
    protected function recordAction(
        int $memoId,
        string $actionType,
        ?string $comment = null,
        ?int $forwardedToUserId = null,
        ?int $forwardedToDepartmentId = null
    ): MemoAction {
        return MemoAction::create([
            'memo_id' => $memoId,
            'user_id' => Auth::id(),
            'action_type' => $actionType,
            'comment' => $comment,
            'forwarded_to_user_id' => $forwardedToUserId,
            'forwarded_to_department_id' => $forwardedToDepartmentId,
        ]);
    }

    /**
     * Send notifications related to memo actions
     */
    protected function sendMemoNotifications(Memo $memo, string $action): void
    {
        try {
            // Determine who should be notified
            $recipients = $this->getNotificationRecipients($memo, $action);

            if (empty($recipients)) {
                return;
            }

            // Prepare notification content
            $subject = $this->getNotificationSubject($memo, $action);
            $message = $this->getNotificationMessage($memo, $action);

            // Send email notifications
            foreach ($recipients as $recipient) {
                // Only send if user has an email
                if (! empty($recipient->email)) {
                    $this->emailService->sendSingle(
                        $recipient->email,
                        $subject,
                        $message,
                        [
                            'user_id' => Auth::id(),
                        ]
                    );
                }

                // Send SMS if user has a phone number and action is important enough
                if (! empty($recipient->phone) && in_array($action, ['approved', 'rejected', 'completed', 'audited'])) {
                    $smsMessage = $this->getSmsNotificationMessage($memo, $action);
                    $this->smsService->sendSingle(
                        $recipient->phone,
                        $smsMessage,
                        [
                            'user_id' => Auth::id(),
                        ]
                    );
                }
            }
        } catch (\Exception $e) {
            Log::error('Error sending memo notifications', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'memo_id' => $memo->id,
                'action' => $action,
            ]);
        }
    }

    /**
     * Get recipients for notifications based on memo action
     */
    protected function getNotificationRecipients(Memo $memo, string $action): array
    {
        $recipients = [];

        switch ($action) {
            case 'created':
                // Notify the recipient user or department members
                if ($memo->recipient_id) {
                    $recipients[] = User::find($memo->recipient_id);
                } elseif ($memo->recipient_department_id) {
                    $departmentUsers = User::where('department_id', $memo->recipient_department_id)->get();
                    $recipients = array_merge($recipients, $departmentUsers->all());
                }
                break;

            case 'forwarded':
                // Latest forwarded action to find out who it was forwarded to
                $forwardAction = $memo->actions()->where('action_type', 'forwarded')->latest()->first();

                if ($forwardAction && $forwardAction->forwarded_to_user_id) {
                    $recipients[] = User::find($forwardAction->forwarded_to_user_id);
                } elseif ($forwardAction && $forwardAction->forwarded_to_department_id) {
                    $departmentUsers = User::where('department_id', $forwardAction->forwarded_to_department_id)->get();
                    $recipients = array_merge($recipients, $departmentUsers->all());
                }
                break;

            case 'approved':
            case 'rejected':
                // Notify the original creator
                $recipients[] = $memo->user;
                break;

            case 'procured':
                // Notify the stores manager - assuming a role-based system
                $storesManagers = User::whereHas('roles', function ($query) {
                    $query->where('name', 'Stores Manager');
                })->get();

                $recipients = array_merge($recipients, $storesManagers->all());
                break;

            case 'delivered':
                // Notify the stores manager again
                $storesManagers = User::whereHas('roles', function ($query) {
                    $query->where('name', 'Stores Manager');
                })->get();

                $recipients = array_merge($recipients, $storesManagers->all());
                break;

            case 'audited':
                // Notify the original creator when items are ready for collection
                $recipients[] = $memo->user;
                break;

            case 'completed':
                // Notify all involved parties
                $recipients[] = $memo->user; // Creator

                if ($memo->recipient_id) {
                    $recipients[] = User::find($memo->recipient_id);
                }

                // Include any approvers
                $approverIds = $memo->actions()
                    ->where('action_type', 'approved')
                    ->pluck('user_id')
                    ->unique()
                    ->toArray();

                $approvers = User::whereIn('id', $approverIds)->get();
                $recipients = array_merge($recipients, $approvers->all());
                break;
        }

        // Filter out duplicates and null values
        return array_filter(array_unique($recipients, SORT_REGULAR));
    }

    /**
     * Get notification subject based on memo action
     */
    protected function getNotificationSubject(Memo $memo, string $action): string
    {
        $refNum = $memo->reference_number;

        switch ($action) {
            case 'created':
                return "New Memo: {$refNum} - {$memo->title}";
            case 'forwarded':
                return "Memo Forwarded: {$refNum} - {$memo->title}";
            case 'approved':
                return "Memo Approved: {$refNum} - {$memo->title}";
            case 'rejected':
                return "Memo Rejected: {$refNum} - {$memo->title}";
            case 'procured':
                return "Items Procured: {$refNum} - {$memo->title}";
            case 'delivered':
                return "Items Delivered to Stores: {$refNum} - {$memo->title}";
            case 'audited':
                return "Items Ready for Collection: {$refNum} - {$memo->title}";
            case 'completed':
                return "Memo Completed: {$refNum} - {$memo->title}";
            default:
                return "Memo Update: {$refNum} - {$memo->title}";
        }
    }

    /**
     * Get notification message based on memo action
     */
    protected function getNotificationMessage(Memo $memo, string $action): string
    {
        $user = Auth::user();
        $userName = $user ? $user->name : 'A user';
        $memoUrl = route('memo.show', $memo->id);
        $refNum = $memo->reference_number;

        switch ($action) {
            case 'created':
                return "A new memo ({$refNum}) titled \"{$memo->title}\" has been created by {$userName} and requires your attention. Please review it at {$memoUrl}";

            case 'forwarded':
                $latestAction = $memo->getLatestAction('forwarded');
                $comment = $latestAction && $latestAction->comment ? "Comment: {$latestAction->comment}" : '';

                return "A memo ({$refNum}) titled \"{$memo->title}\" has been forwarded to you by {$userName}. {$comment}. Please review it at {$memoUrl}";

            case 'approved':
                $latestAction = $memo->getLatestAction('approved');
                $comment = $latestAction && $latestAction->comment ? "Comment: {$latestAction->comment}" : '';

                return "Your memo ({$refNum}) titled \"{$memo->title}\" has been approved by {$userName}. {$comment}. You can view the details at {$memoUrl}";

            case 'rejected':
                $latestAction = $memo->getLatestAction('rejected');
                $comment = $latestAction && $latestAction->comment ? "Reason: {$latestAction->comment}" : '';

                return "Your memo ({$refNum}) titled \"{$memo->title}\" has been rejected by {$userName}. {$comment}. You can view the details at {$memoUrl}";

            case 'procured':
                return "Items requested in memo ({$refNum}) titled \"{$memo->title}\" have been procured by {$userName}. The items will be delivered to stores for audit.";

            case 'delivered':
                return "Items requested in memo ({$refNum}) titled \"{$memo->title}\" have been delivered to stores by {$userName} and are pending audit.";

            case 'audited':
                return "Items requested in your memo ({$refNum}) titled \"{$memo->title}\" have been audited by stores and are ready for collection.";

            case 'completed':
                return "Memo ({$refNum}) titled \"{$memo->title}\" has been marked as completed by {$userName}.";

            default:
                return "There has been an update to memo ({$refNum}) titled \"{$memo->title}\". Please check the details at {$memoUrl}";
        }
    }

    /**
     * Get SMS notification message (shorter version)
     */
    protected function getSmsNotificationMessage(Memo $memo, string $action): string
    {
        $refNum = $memo->reference_number;

        switch ($action) {
            case 'approved':
                return "MEMO {$refNum} has been APPROVED. Check your email for details.";

            case 'rejected':
                return "MEMO {$refNum} has been REJECTED. Check your email for details.";

            case 'audited':
                return "Items for MEMO {$refNum} are ready for collection.";

            case 'completed':
                return "MEMO {$refNum} has been marked as COMPLETED.";

            default:
                return "Update on MEMO {$refNum}. Check your email for details.";
        }
    }
}
