<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AuditAcademicPeriods extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finance:audit-academic-periods 
                            {--repair : Apply safe reconciliation fixes within a verified database transaction}
                            {--force : Force execution without interactive confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit and reconcile Academic Year and Semester linkages across finance and academic records while preserving financial integrity';

    /**
     * Tables with both academic_year_id and semester_id to inspect.
     */
    protected array $coordinatedTables = [
        'student_fee_bills' => 'Student Fee Bills',
        'fee_structures' => 'Fee Structures',
        'course_registrations' => 'Course Registrations',
        'assessment_scores' => 'Assessment Scores',
        'exam_clearances' => 'Exam Clearances',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('===========================================================');
        $this->info('  360College - Academic Period Alignment & Integrity Audit');
        $this->info('===========================================================');

        $isRepair = (bool) $this->option('repair');

        // Step 1: Collect Audit Metrics
        $auditResults = $this->performAudit();

        // Step 2: Render Summary
        $this->renderAuditReport($auditResults);

        $totalMismatches = $auditResults['total_mismatches'];
        $orphanSemesters = $auditResults['orphan_semesters'];

        if ($totalMismatches === 0 && empty($orphanSemesters)) {
            $this->newLine();
            $this->info('✓ All academic years and semesters are fully synchronized across all modules.');
            $this->info('✓ No orphan semesters or mismatched foreign references detected.');
            return 0;
        }

        // Step 3: Handle Dry-Run Mode vs Repair Mode
        if (! $isRepair) {
            $this->newLine();
            $this->warn('DRY RUN COMPLETE: Found ' . $totalMismatches . ' mismatched record(s) and ' . count($orphanSemesters) . ' orphan semester(s).');
            $this->line('To reconcile these records safely under an audited transaction, run:');
            $this->comment('  php artisan finance:audit-academic-periods --repair');
            return 1;
        }

        // Confirmation before repair
        if (! $this->option('force')) {
            $confirmed = $this->confirm('Are you sure you want to repair mismatched records and reconcile semesters? Financial checksums will be validated before committing.');
            if (! $confirmed) {
                $this->info('Operation cancelled by user.');
                return 0;
            }
        }

        // Step 4: Execute Safe Repair with Financial Parity Check
        return $this->executeRepair($auditResults);
    }

    /**
     * Perform read-only audit across all coordinated tables and semesters.
     */
    protected function performAudit(): array
    {
        $results = [
            'tables' => [],
            'orphan_semesters' => [],
            'total_mismatches' => 0,
            'financial_checksums' => $this->calculateFinancialChecksums(),
        ];

        // 1. Audit Orphan Semesters (academic_year_id IS NULL)
        $orphans = DB::table('semesters')->whereNull('academic_year_id')->get();
        foreach ($orphans as $orphan) {
            $references = [];
            foreach ($this->coordinatedTables as $table => $label) {
                if (Schema::hasTable($table) && Schema::hasColumn($table, 'semester_id')) {
                    $count = DB::table($table)->where('semester_id', $orphan->id)->count();
                    if ($count > 0) {
                        $references[$table] = $count;
                    }
                }
            }
            if (Schema::hasTable('subjects') && Schema::hasColumn('subjects', 'semester_id')) {
                $subCount = DB::table('subjects')->where('semester_id', $orphan->id)->count();
                if ($subCount > 0) {
                    $references['subjects'] = $subCount;
                }
            }

            $results['orphan_semesters'][] = [
                'id' => $orphan->id,
                'name' => $orphan->name,
                'sequence' => $orphan->sequence,
                'references' => $references,
            ];
        }

        // 2. Audit Coordinated Tables
        foreach ($this->coordinatedTables as $table => $label) {
            if (! Schema::hasTable($table) ||
                ! Schema::hasColumn($table, 'academic_year_id') ||
                ! Schema::hasColumn($table, 'semester_id')) {
                continue;
            }

            $totalRecords = DB::table($table)->whereNotNull('semester_id')->count();

            // Mismatch: record's academic_year_id differs from semester's academic_year_id, or semester has no year
            $mismatchedRows = DB::table("{$table} as t")
                ->leftJoin('semesters as s', 't.semester_id', '=', 's.id')
                ->whereNotNull('t.semester_id')
                ->where(function ($query) {
                    $query->whereColumn('t.academic_year_id', '!=', 's.academic_year_id')
                        ->orWhereNull('s.academic_year_id')
                        ->orWhereNull('s.id');
                })
                ->select('t.id', 't.academic_year_id as row_year', 't.semester_id', 's.academic_year_id as sem_year', 's.name as sem_name', 's.sequence as sem_seq')
                ->get();

            $results['tables'][$table] = [
                'label' => $label,
                'total' => $totalRecords,
                'mismatched_count' => $mismatchedRows->count(),
                'sample_mismatches' => $mismatchedRows,
            ];

            $results['total_mismatches'] += $mismatchedRows->count();
        }

        return $results;
    }

    /**
     * Render formatted audit report to console.
     */
    protected function renderAuditReport(array $auditResults): void
    {
        $this->newLine();
        $this->info('--- Coordinated Module Records ---');

        $tableData = [];
        foreach ($auditResults['tables'] as $table => $data) {
            $tableData[] = [
                $data['label'],
                $data['total'],
                $data['mismatched_count'] > 0 ? "<fg=red>{$data['mismatched_count']}</>" : '<fg=green>0</>',
                $data['mismatched_count'] === 0 ? '<fg=green>Aligned</>' : '<fg=yellow>Mismatched</>',
            ];
        }

        $this->table(['Module / Table', 'Total Records with Semester', 'Mismatched Count', 'Status'], $tableData);

        // Orphan Semesters Table
        $this->newLine();
        $this->info('--- Orphan Semesters (No Academic Year Assigned) ---');
        if (empty($auditResults['orphan_semesters'])) {
            $this->line('<fg=green>None found. All semesters are attached to an Academic Year.</>');
        } else {
            $orphanData = [];
            foreach ($auditResults['orphan_semesters'] as $orphan) {
                $refSummary = empty($orphan['references'])
                    ? '<fg=gray>Unreferenced</>'
                    : collect($orphan['references'])->map(fn ($cnt, $tbl) => "{$tbl}: {$cnt}")->implode(', ');

                $orphanData[] = [
                    $orphan['id'],
                    $orphan['name'],
                    $orphan['sequence'] ?? 'N/A',
                    $refSummary,
                ];
            }
            $this->table(['Semester ID', 'Name', 'Sequence', 'References in DB'], $orphanData);
        }

        // Financial Baseline Metrics
        $this->newLine();
        $this->info('--- Financial Baseline Checksum ---');
        $financials = $auditResults['financial_checksums'];
        $this->table(
            ['Metric', 'Current Value'],
            [
                ['Total Student Fee Bills', number_format($financials['bill_count'])],
                ['Sum Total Billed (total_amount)', number_format($financials['total_amount'], 2)],
                ['Sum Total Paid (amount_paid)', number_format($financials['total_paid'], 2)],
                ['Sum Total Outstanding (balance)', number_format($financials['total_balance'], 2)],
            ]
        );
    }

    /**
     * Calculate financial checksums to verify mathematical parity before and after repair.
     */
    protected function calculateFinancialChecksums(): array
    {
        if (! Schema::hasTable('student_fee_bills')) {
            return [
                'bill_count' => 0,
                'total_amount' => 0.0,
                'total_paid' => 0.0,
                'total_balance' => 0.0,
            ];
        }

        return [
            'bill_count' => DB::table('student_fee_bills')->count(),
            'total_amount' => (float) DB::table('student_fee_bills')->sum('total_amount'),
            'total_paid' => (float) DB::table('student_fee_bills')->sum('amount_paid'),
            'total_balance' => (float) DB::table('student_fee_bills')->sum('balance'),
        ];
    }

    /**
     * Execute repair within an audited database transaction.
     */
    protected function executeRepair(array $auditResults): int
    {
        $this->newLine();
        $this->info('Executing repair under database transaction with financial parity checks...');

        $preChecksum = $this->calculateFinancialChecksums();

        try {
            DB::beginTransaction();

            $repairedCount = 0;

            // 1. Resolve orphan semesters with references
            foreach ($auditResults['orphan_semesters'] as $orphan) {
                if (! empty($orphan['references'])) {
                    // Find what academic year the referencing records belong to
                    $detectedYearId = null;
                    foreach ($this->coordinatedTables as $table => $label) {
                        if (isset($orphan['references'][$table])) {
                            $detectedYearId = DB::table($table)
                                ->where('semester_id', $orphan['id'])
                                ->whereNotNull('academic_year_id')
                                ->value('academic_year_id');
                            if ($detectedYearId) {
                                break;
                            }
                        }
                    }

                    if ($detectedYearId) {
                        DB::table('semesters')->where('id', $orphan['id'])->update([
                            'academic_year_id' => $detectedYearId,
                            'updated_at' => now(),
                        ]);
                        $this->line("Assigned orphan semester #{$orphan['id']} ('{$orphan['name']}') to Academic Year #{$detectedYearId}.");
                    }
                }
            }

            // 2. Reconcile mismatched records across all coordinated tables
            foreach ($auditResults['tables'] as $table => $data) {
                if ($data['mismatched_count'] === 0) {
                    continue;
                }

                $this->info("Reconciling {$data['label']} ({$data['mismatched_count']} records)...");

                foreach ($data['sample_mismatches'] as $row) {
                    $targetYearId = $row->row_year;
                    if (! $targetYearId) {
                        continue; // Cannot reconcile if record has no academic_year_id
                    }

                    // Determine target semester ID belonging to $targetYearId
                    $targetSemesterId = $this->findOrCreateTargetSemester(
                        (int) $targetYearId,
                        $row->sem_name,
                        $row->sem_seq ? (int) $row->sem_seq : null,
                        $row->semester_id ? (int) $row->semester_id : null
                    );

                    if ($targetSemesterId && $targetSemesterId !== $row->semester_id) {
                        DB::table($table)->where('id', $row->id)->update([
                            'semester_id' => $targetSemesterId,
                        ]);
                        $repairedCount++;
                    }
                }
            }

            // 3. Post-Repair Financial Parity Check
            $postChecksum = $this->calculateFinancialChecksums();

            $this->validateFinancialParity($preChecksum, $postChecksum);

            DB::commit();

            $this->newLine();
            $this->info("✓ Repair completed successfully! Total records re-aligned: {$repairedCount}.");
            $this->info('✓ Financial parity check PASSED: All bill amounts, payments, and balances match 100%.');

            return 0;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->newLine();
            $this->error('REPAIR FAILED & TRANSACTION ROLLED BACK: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Find existing semester belonging to target academic year matching name or sequence,
     * or safely provision a matching semester.
     */
    protected function findOrCreateTargetSemester(int $targetYearId, ?string $semName, ?int $semSeq, ?int $fallbackOldSemId): int
    {
        // 1. Try matching by sequence in the target academic year
        if ($semSeq) {
            $matchBySeq = DB::table('semesters')
                ->where('academic_year_id', $targetYearId)
                ->where('sequence', $semSeq)
                ->value('id');
            if ($matchBySeq) {
                return (int) $matchBySeq;
            }
        }

        // 2. Try matching by name in the target academic year
        if ($semName) {
            $matchByName = DB::table('semesters')
                ->where('academic_year_id', $targetYearId)
                ->where('name', $semName)
                ->value('id');
            if ($matchByName) {
                return (int) $matchByName;
            }
        }

        // 3. If old semester details exist, get fallback information
        $oldSemester = null;
        if ($fallbackOldSemId) {
            $oldSemester = DB::table('semesters')->where('id', $fallbackOldSemId)->first();
        }

        $resolvedName = $semName ?: ($oldSemester->name ?? 'Semester 1');
        $resolvedSeq = $semSeq ?: ($oldSemester->sequence ?? 1);
        $academicYear = DB::table('academic_years')->where('id', $targetYearId)->first();

        // 4. Provision matching semester for target academic year
        return (int) DB::table('semesters')->insertGetId([
            'name' => $resolvedName,
            'slug' => Str::slug($resolvedName . '-' . ($academicYear ? $academicYear->name : $targetYearId) . '-' . Str::random(5)),
            'description' => $oldSemester->description ?? "Auto-aligned {$resolvedName}",
            'academic_year_id' => $targetYearId,
            'sequence' => $resolvedSeq,
            'start_date' => $academicYear ? $academicYear->start_date : now()->toDateString(),
            'end_date' => $academicYear ? $academicYear->end_date : now()->addMonths(4)->toDateString(),
            'is_current' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Validate financial parity to prevent mathematical discrepancy or accidental balance changes.
     */
    protected function validateFinancialParity(array $pre, array $post): void
    {
        if ($pre['bill_count'] !== $post['bill_count']) {
            throw new \RuntimeException(
                "Financial integrity check failed! Bill count changed from {$pre['bill_count']} to {$post['bill_count']}."
            );
        }

        $amountDiff = abs($pre['total_amount'] - $post['total_amount']);
        if ($amountDiff > 0.001) {
            throw new \RuntimeException(
                "Financial integrity check failed! Total billed amount changed by {$amountDiff} (pre: {$pre['total_amount']}, post: {$post['total_amount']})."
            );
        }

        $paidDiff = abs($pre['total_paid'] - $post['total_paid']);
        if ($paidDiff > 0.001) {
            throw new \RuntimeException(
                "Financial integrity check failed! Total paid amount changed by {$paidDiff} (pre: {$pre['total_paid']}, post: {$post['total_paid']})."
            );
        }

        $balanceDiff = abs($pre['total_balance'] - $post['total_balance']);
        if ($balanceDiff > 0.001) {
            throw new \RuntimeException(
                "Financial integrity check failed! Total balance changed by {$balanceDiff} (pre: {$pre['total_balance']}, post: {$post['total_balance']})."
            );
        }
    }
}
