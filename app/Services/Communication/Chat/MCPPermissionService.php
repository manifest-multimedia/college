<?php

namespace App\Services\Communication\Chat;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MCPPermissionService
{
    /**
     * Check if the current user can perform MCP operations
     */
    public function canAccessMCP(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        // System and Super Admin have full access
        if ($user->hasAnyRole(['System', 'Super Admin'])) {
            return true;
        }

        // Users with exam management permissions can access MCP
        return $user->hasAnyPermission([
            'create exams',
            'edit exams',
            'view exams',
            'create offline exams',
            'manage curriculum',
        ]);
    }

    /**
     * Check if user can create question sets
     */
    public function canCreateQuestionSets(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        return $user->hasAnyRole(['System', 'Super Admin', 'Academic Officer', 'Administrator'])
            || $user->hasAnyPermission(['create exams', 'create offline exams', 'manage curriculum']);
    }

    /**
     * Check if user can add questions to sets
     */
    public function canAddQuestions(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        return $user->hasAnyRole(['System', 'Super Admin', 'Academic Officer', 'Administrator', 'Lecturer'])
            || $user->hasAnyPermission(['create exams', 'edit exams', 'create offline exams', 'manage curriculum']);
    }

    /**
     * Check if user can create exams
     */
    public function canCreateExams(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        return $user->hasAnyRole(['System', 'Super Admin', 'Academic Officer', 'Administrator'])
            || $user->hasPermission('create exams');
    }

    /**
     * Check if user can view exams
     */
    public function canViewExams(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        return $user->hasAnyRole(['System', 'Super Admin', 'Academic Officer', 'Administrator', 'Lecturer'])
            || $user->hasAnyPermission(['view exams', 'create exams', 'edit exams', 'grade exams']);
    }

    /**
     * Check if user can list courses (generally more permissive)
     */
    public function canListCourses(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        // Most authenticated users can view courses
        return $user->hasAnyRole(['System', 'Super Admin', 'Academic Officer', 'Administrator', 'Lecturer', 'Student'])
            || $user->hasAnyPermission(['view courses', 'view exams']);
    }

    /**
     * Check if user can list question sets
     */
    public function canListQuestionSets(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        return $user->hasAnyRole(['System', 'Super Admin', 'Academic Officer', 'Administrator', 'Lecturer'])
            || $user->hasAnyPermission(['view exams', 'view offline exams', 'create exams', 'manage curriculum']);
    }

    /**
     * Check if user can view question set details
     */
    public function canViewQuestionSetDetails(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        return $user->hasAnyRole(['System', 'Super Admin', 'Academic Officer', 'Administrator', 'Lecturer'])
            || $user->hasAnyPermission(['view exams', 'view offline exams', 'grade exams', 'manage curriculum']);
    }

    /**
     * Check if user can manage students (required for student ID reassignment)
     */
    public function canManageStudents(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        return $user->hasAnyRole(['System', 'Super Admin', 'Administrator', 'Registrar'])
            || $user->hasAnyPermission(['manage students', 'edit students', 'create students']);
    }

    /**
     * Get user context for AI assistant
     */
    public function getUserContext(): array
    {
        $user = Auth::user();
        if (! $user) {
            return [
                'authenticated' => false,
                'message' => 'You must be logged in to use AI Sensei exam management features.',
            ];
        }

        $roles = $user->roles->pluck('name')->toArray();
        $permissions = $user->getAllPermissions()->pluck('name')->toArray();

        // Determine user capabilities
        $capabilities = [];
        if ($this->canCreateQuestionSets()) {
            $capabilities[] = 'create question sets';
        }
        if ($this->canAddQuestions()) {
            $capabilities[] = 'add questions';
        }
        if ($this->canCreateExams()) {
            $capabilities[] = 'create exams';
        }
        if ($this->canViewExams()) {
            $capabilities[] = 'view exams';
        }
        if ($this->canListCourses()) {
            $capabilities[] = 'view courses';
        }
        if ($this->canListQuestionSets()) {
            $capabilities[] = 'view question sets';
        }
        if ($this->canViewQuestionSetDetails()) {
            $capabilities[] = 'view question details';
        }
        if ($this->canManageStudents()) {
            $capabilities[] = 'manage student IDs';
            $capabilities[] = 'reassign student IDs';
            $capabilities[] = 'revert student ID changes';
        }

        return [
            'authenticated' => true,
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $roles,
            'primary_role' => $roles[0] ?? 'User',
            'exam_permissions' => array_intersect($permissions, [
                'create exams', 'edit exams', 'view exams',
                'create offline exams', 'update offline exams', 'view offline exams',
                'grade exams', 'generate exam reports', 'manage curriculum',
            ]),
            'student_permissions' => array_intersect($permissions, [
                'manage students', 'edit students', 'create students', 'view students',
            ]),
            'capabilities' => $capabilities,
            'can_access_mcp' => $this->canAccessMCP(),
            'can_manage_students' => $this->canManageStudents(),
        ];
    }

    /**
     * Get permission denial message for specific action
     */
    public function getPermissionDenialMessage(string $action): string
    {
        $user = Auth::user();

        if (! $user) {
            return "You must be logged in to {$action}.";
        }

        $userRole = $user->roles->first()?->name ?? 'User';

        $messages = [
            'create_question_set' => "Sorry, your role ({$userRole}) does not have permission to create question sets. This action requires Academic Officer, Administrator, or System role, or specific exam creation permissions.",
            'add_question_to_set' => "Sorry, your role ({$userRole}) does not have permission to add questions. This action requires Academic Officer, Administrator, Lecturer, or System role, or exam management permissions.",
            'create_exam' => "Sorry, your role ({$userRole}) does not have permission to create exams. This action requires Academic Officer, Administrator, or System role, or the 'create exams' permission.",
            'list_exams' => "Sorry, your role ({$userRole}) does not have permission to view exams. This action requires Academic Officer, Administrator, Lecturer, or System role, or exam viewing permissions.",
            'get_exam_details' => "Sorry, your role ({$userRole}) does not have permission to view exam details. This action requires Academic Officer, Administrator, Lecturer, or System role, or exam viewing permissions.",
            'list_courses' => "Sorry, your role ({$userRole}) does not have permission to view courses.",
            'list_question_sets' => "Sorry, your role ({$userRole}) does not have permission to view question sets. This action requires Academic Officer, Administrator, Lecturer, or System role.",
            'get_question_set_details' => "Sorry, your role ({$userRole}) does not have permission to view question set details. This action requires Academic Officer, Administrator, Lecturer, or System role.",
        ];

        return $messages[$action] ?? "Sorry, you don't have permission to perform this action.";
    }

    /**
     * Log permission check for audit purposes
     */
    public function logPermissionCheck(string $action, bool $granted): void
    {
        $user = Auth::user();

        Log::info('MCP Permission Check', [
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'action' => $action,
            'granted' => $granted,
            'user_roles' => $user?->roles->pluck('name')->toArray(),
            'timestamp' => now()->toISOString(),
        ]);
    }
}
