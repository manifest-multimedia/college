<?php

namespace App\Http\Controllers\Communication;

use App\Http\Controllers\Controller;
use App\Models\ResultsSmsUploadBatch;
use App\Services\Communication\SMS\ResultsSmsUploadService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResultsSmsUploadReportController extends Controller
{
    public function download(string $batch, ResultsSmsUploadService $uploads): StreamedResponse
    {
        abort_unless(auth()->user()?->hasAnyRole(['System', 'Super Admin', 'Administrator', 'Academic Officer']), 403);
        $batch = ResultsSmsUploadBatch::where('public_id', $batch)->firstOrFail();

        return response()->streamDownload(function () use ($batch, $uploads): void {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Row', 'Student ID', 'Status', 'Reason', 'Recipient', 'Processed at']);

            $batch->rows()->orderBy('row_number')->cursor()->each(function ($row) use ($output, $uploads): void {
                fputcsv($output, [
                    $row->row_number,
                    $uploads->safeForSpreadsheet($row->student_id),
                    $row->status,
                    $uploads->safeForSpreadsheet($row->safe_reason),
                    $row->masked_recipient,
                    $row->processed_at?->toDateTimeString(),
                ]);
            });
            fclose($output);
        }, 'results-sms-'.$batch->public_id.'-report.csv', ['Content-Type' => 'text/csv']);
    }
}
