<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\SystemNotification;
use App\Notifications\ExamNotification;
use App\Notifications\FinanceNotification;
use App\Notifications\CourseRegistrationNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class NotificationService
{
    /**
     * Send a notification to a specific user.
     *
     * @param User $user The user to notify
     * @param string $title Notification title
     * @param string $message Notification message
     * @param string|null $actionUrl Optional action URL
     * @param string $type Notification type (info, success, warning, danger)
     * @return void
     */
    public function notifyUser(User $user, string $title, string $message, ?string $actionUrl = null, string $type = 'info')
    {
        try {
            $notification = new SystemNotification($title, $message, $actionUrl, $type);
            $user->notify($notification);
            
            Log::info('Notification sent to user', [
                'user_id' => $user->id,
                'title' => $title,
                'type' => $type
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send notification to user', [
                'user_id' => $user->id,
                'title' => $title,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    /**
     * Send a notification to users with specific role(s).
     *
     * @param string|array $roles Role name or array of role names
     * @param string $title Notification title
     * @param string $message Notification message
     * @param string|null $actionUrl Optional action URL
     * @param string $type Notification type (info, success, warning, danger)
     * @return void
     */
    public function notifyRole($roles, string $title, string $message, ?string $actionUrl = null, string $type = 'info')
    {
        try {
            $roles = is_array($roles) ? $roles : [$roles];
            
            foreach ($roles as $role) {
                $users = User::role($role)->get();
                $notification = new SystemNotification($title, $message, $actionUrl, $type);
                
                Notification::send($users, $notification);
                
                Log::info('Notification sent to users with role', [
                    'role' => $role,
                    'user_count' => $users->count(),
                    'title' => $title,
                    'type' => $type
                ]);
            }
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send notification to role', [
                'roles' => $roles,
                'title' => $title,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    /**
     * Broadcast a system notification to all users.
     *
     * @param string $title Notification title
     * @param string $message Notification message
     * @param string|null $actionUrl Optional action URL
     * @param string $type Notification type (info, success, warning, danger)
     * @return void
     */
    public function notifyAll(string $title, string $message, ?string $actionUrl = null, string $type = 'info')
    {
        try {
            $users = User::all();
            $notification = new SystemNotification($title, $message, $actionUrl, $type);
            
            Notification::send($users, $notification);
            
            Log::info('System notification broadcasted to all users', [
                'user_count' => $users->count(),
                'title' => $title,
                'type' => $type
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to broadcast system notification', [
                'title' => $title,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    /**
     * Send an exam-related notification to a user.
     *
     * @param User $user The user to notify
     * @param string $title Notification title
     * @param string $message Notification message
     * @param string|null $examId Optional exam ID
     * @param string $type Notification type (info, success, warning, danger)
     * @return void
     */
    public function notifyExam(User $user, string $title, string $message, ?string $examId = null, string $type = 'info')
    {
        try {
            $notification = new ExamNotification($title, $message, $examId, $type);
            $user->notify($notification);
            
            Log::info('Exam notification sent to user', [
                'user_id' => $user->id,
                'title' => $title,
                'exam_id' => $examId,
                'type' => $type
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send exam notification', [
                'user_id' => $user->id,
                'title' => $title,
                'exam_id' => $examId,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    /**
     * Send a finance-related notification to a user.
     *
     * @param User $user The user to notify
     * @param string $title Notification title
     * @param string $message Notification message
     * @param string|null $transactionId Optional transaction ID
     * @param string $type Notification type (info, success, warning, danger)
     * @return void
     */
    public function notifyFinance(User $user, string $title, string $message, ?string $transactionId = null, string $type = 'info')
    {
        try {
            $notification = new FinanceNotification($title, $message, $transactionId, $type);
            $user->notify($notification);
            
            Log::info('Finance notification sent to user', [
                'user_id' => $user->id,
                'title' => $title,
                'transaction_id' => $transactionId,
                'type' => $type
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send finance notification', [
                'user_id' => $user->id,
                'title' => $title,
                'transaction_id' => $transactionId,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    /**
     * Send a course registration notification to a user.
     *
     * @param User $user The user to notify
     * @param string $title Notification title
     * @param string $message Notification message
     * @param string|null $courseId Optional course ID
     * @param string $type Notification type (info, success, warning, danger)
     * @return void
     */
    public function notifyCourseRegistration(User $user, string $title, string $message, ?string $courseId = null, string $type = 'info')
    {
        try {
            $notification = new CourseRegistrationNotification($title, $message, $courseId, $type);
            $user->notify($notification);
            
            Log::info('Course registration notification sent to user', [
                'user_id' => $user->id,
                'title' => $title,
                'course_id' => $courseId,
                'type' => $type
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send course registration notification', [
                'user_id' => $user->id,
                'title' => $title,
                'course_id' => $courseId,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
}