<?php

namespace App\Services\Communication\Chat\MCP;

use App\Models\Student;
use App\Services\StudentIdReassignmentService;
use Illuminate\Support\Facades\Log;

class StudentManagementMCPService
{
    protected StudentIdReassignmentService $reassignmentService;

    public function __construct()
    {
        $this->reassignmentService = new StudentIdReassignmentService;
    }

    /**
     * Preview student ID reassignment changes
     */
    public function previewStudentIdReassignment(array $arguments): array
    {
        try {
            $filters = $this->buildFilters($arguments);
            $targetFormat = $arguments['target_format'] ?? null;
            $customPattern = $arguments['custom_pattern'] ?? null;

            Log::info('Preview student ID reassignment requested', [
                'filters' => $filters,
                'target_format' => $targetFormat,
                'custom_pattern' => $customPattern,
                'user_id' => auth()->id(),
            ]);

            $preview = $this->reassignmentService->previewReassignment($filters, $targetFormat, $customPattern);

            return [
                'success' => true,
                'summary' => [
                    'total_students' => $preview['total'],
                    'would_be_updated' => $preview['successful'],
                    'filters_applied' => $filters,
                    'target_format' => $targetFormat,
                    'custom_pattern' => $customPattern,
                    'sample_changes' => array_slice($preview['updates'], 0, 5), // Show only 5 examples
                ],
                'message' => "Preview: {$preview['successful']} student IDs would be updated out of {$preview['total']} total students.",
            ];

        } catch (\Exception $e) {
            Log::error('Error previewing student ID reassignment', [
                'error' => $e->getMessage(),
                'arguments' => $arguments,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Execute student ID reassignment
     */
    public function reassignStudentIds(array $arguments): array
    {
        try {
            $filters = $this->buildFilters($arguments);
            $targetFormat = $arguments['target_format'] ?? null;
            $customPattern = $arguments['custom_pattern'] ?? null;

            Log::info('Student ID reassignment execution requested', [
                'filters' => $filters,
                'target_format' => $targetFormat,
                'custom_pattern' => $customPattern,
                'user_id' => auth()->id(),
            ]);

            // Validate before executing
            $validation = $this->reassignmentService->validateReassignment($filters);

            if (! $validation['is_safe'] && ! empty($validation['errors'])) {
                return [
                    'success' => false,
                    'error' => 'Reassignment validation failed',
                    'validation' => $validation,
                ];
            }

            // Execute reassignment
            $results = $this->reassignmentService->batchReassignStudentIds([
                'filters' => $filters,
                'target_format' => $targetFormat,
                'custom_pattern' => $customPattern,
                'dry_run' => false,
            ]);

            Log::info('Student ID reassignment completed', [
                'results' => $results,
                'user_id' => auth()->id(),
            ]);

            return [
                'success' => true,
                'summary' => [
                    'total_processed' => $results['processed'],
                    'successful' => $results['successful'],
                    'failed' => $results['failed'],
                    'sample_updates' => array_slice($results['updates'], 0, 5), // Show only 5 examples
                ],
                'message' => "Successfully updated {$results['successful']} student IDs out of {$results['processed']} total. ".
                            ($results['failed'] > 0 ? "{$results['failed']} failed." : 'All updates completed successfully.'),
            ];

        } catch (\Exception $e) {
            Log::error('Error executing student ID reassignment', [
                'error' => $e->getMessage(),
                'arguments' => $arguments,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Revert student ID changes
     */
    public function revertStudentIds(array $arguments): array
    {
        try {
            $studentIds = $arguments['student_ids'] ?? [];

            if (empty($studentIds)) {
                return [
                    'success' => false,
                    'error' => 'No student IDs provided for reversion',
                ];
            }

            Log::info('Student ID reversion requested', [
                'student_ids' => $studentIds,
                'user_id' => auth()->id(),
            ]);

            $results = $this->reassignmentService->batchRevertStudentIds($studentIds);

            Log::info('Student ID reversion completed', [
                'results' => $results,
                'user_id' => auth()->id(),
            ]);

            return [
                'success' => true,
                'results' => $results,
                'summary' => [
                    'total' => $results['total'],
                    'successful' => $results['successful'],
                    'failed' => $results['failed'],
                ],
            ];

        } catch (\Exception $e) {
            Log::error('Error reverting student IDs', [
                'error' => $e->getMessage(),
                'arguments' => $arguments,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get student ID format statistics
     */
    public function getStudentIdStatistics(array $arguments = []): array
    {
        try {
            Log::info('Student ID statistics requested', [
                'user_id' => auth()->id(),
            ]);

            $stats = $this->reassignmentService->getIdFormatStatistics();

            return [
                'success' => true,
                'statistics' => $stats,
                'summary' => [
                    'total_students' => $stats['total_students'],
                    'simple_format_count' => $stats['by_format']['simple'],
                    'structured_format_count' => $stats['by_format']['structured'],
                    'unknown_format_count' => $stats['by_format']['unknown'],
                ],
            ];

        } catch (\Exception $e) {
            Log::error('Error getting student ID statistics', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Parse and analyze a specific student ID
     */
    public function parseStudentId(array $arguments): array
    {
        try {
            $studentId = $arguments['student_id'] ?? null;

            if (! $studentId) {
                return [
                    'success' => false,
                    'error' => 'Student ID is required',
                ];
            }

            Log::info('Student ID parsing requested', [
                'student_id' => $studentId,
                'user_id' => auth()->id(),
            ]);

            $parsed = $this->reassignmentService->parseStudentId($studentId);

            return [
                'success' => true,
                'parsed_data' => $parsed,
                'format' => $parsed['format'],
                'components' => array_diff_key($parsed, ['original' => '', 'format' => '']),
            ];

        } catch (\Exception $e) {
            Log::error('Error parsing student ID', [
                'error' => $e->getMessage(),
                'student_id' => $studentId ?? null,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get current student ID configuration
     */
    public function getStudentIdConfiguration(array $arguments = []): array
    {
        try {
            $config = [
                'format' => config('branding.student_id.format', 'structured'),
                'custom_pattern' => config('branding.student_id.custom_pattern'),
                'institution_prefix' => config('branding.student_id.institution_prefix'),
                'institution_simple' => config('branding.student_id.institution_simple'),
                'alphabetical_ordering' => config('branding.student_id.enable_alphabetical_ordering', true),
                'use_academic_year' => config('branding.student_id.use_academic_year', true),
                'sequence_start' => config('branding.student_id.sequence_start', 1),
                'sequence_reset_yearly' => config('branding.student_id.sequence_reset_yearly', true),
            ];

            return [
                'success' => true,
                'configuration' => $config,
                'format_description' => $this->getFormatDescription($config['format']),
            ];

        } catch (\Exception $e) {
            Log::error('Error getting student ID configuration', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Build filters from arguments
     */
    private function buildFilters(array $arguments): array
    {
        $filters = [];

        if (isset($arguments['program_id'])) {
            $filters['program_id'] = $arguments['program_id'];
        }

        if (isset($arguments['cohort_id'])) {
            $filters['cohort_id'] = $arguments['cohort_id'];
        }

        if (isset($arguments['year'])) {
            $filters['year'] = $arguments['year'];
        }

        if (isset($arguments['current_format'])) {
            $filters['format'] = $arguments['current_format'];
        }

        return $filters;
    }

    /**
     * Get format description
     */
    private function getFormatDescription(string $format): string
    {
        return match ($format) {
            'simple' => 'Simple format without separators (e.g., MHIAFRGN220003)',
            'structured' => 'Structured format with separators (e.g., MHIAF/RGN/22/23/003)',
            'custom' => 'Custom format based on configured pattern',
            default => 'Unknown format',
        };
    }
}
