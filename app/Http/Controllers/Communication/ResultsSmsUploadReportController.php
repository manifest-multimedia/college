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

            // Prepend UTF-8 BOM so Microsoft Excel on Windows recognizes UTF-8 without mojibake
            fwrite($output, "\xEF\xBB\xBF");

            fputcsv($output, ['Row', 'Student ID', 'Student Name', 'Status', 'Reason', 'Recipient', 'Processed at']);

            $batch->rows()->with('student')->orderBy('row_number')->cursor()->each(function ($row) use ($output, $uploads): void {
                // Replace unicode bullet • with ASCII * so phone masking displays cleanly in all spreadsheet apps
                $recipient = str_replace(['•', "\xE2\x80\xA2"], '*', (string) ($row->masked_recipient ?? ''));

                fputcsv($output, [
                    $row->row_number,
                    $uploads->safeForSpreadsheet($row->student_id),
                    $uploads->safeForSpreadsheet($row->student?->full_name ?? ($row->student?->name ?? '')),
                    $row->status,
                    $uploads->safeForSpreadsheet($row->safe_reason),
                    $recipient,
                    $row->processed_at?->toDateTimeString(),
                ]);
            });
            fclose($output);
        }, 'results-sms-'.$batch->public_id.'-report.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}

