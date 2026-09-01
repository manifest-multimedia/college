<?php

namespace App\Services\Communication\SMS;

use App\Models\ResultsSmsUploadBatch;
use App\Models\Student;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;

class ResultsSmsUploadService
{
    public const MAX_ROWS = 10000;

    public function assertSafeUpload(UploadedFile $file): void
    {
        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, ['xlsx', 'csv'], true)) {
            throw new RuntimeException('Only .xlsx and .csv files are allowed.');
        }

        if ($extension === 'xlsx' && class_exists(\ZipArchive::class)) {
            $zip = new \ZipArchive();
            if ($zip->open($file->getRealPath()) !== true) {
                throw new RuntimeException('The Excel file is invalid or corrupted.');
            }
            $hasMacros = $zip->locateName('xl/vbaProject.bin') !== false;
            $zip->close();
            if ($hasMacros) {
                throw new RuntimeException('Macro-enabled workbooks are not permitted for results SMS uploads.');
            }
        }

        $scanner = config('results_sms.clamav_binary');
        if (filled($scanner)) {
            $scan = Process::timeout(60)->run([$scanner, '--no-summary', $file->getRealPath()]);
            if (! $scan->successful()) {
                throw new RuntimeException('The file did not pass the malware scan.');
            }
        } elseif (config('results_sms.require_malware_scan')) {
            throw new RuntimeException('Malware scanning is required but is not configured for this institution.');
        }
    }

    public function storeEncryptedUpload(UploadedFile $file, ResultsSmsUploadBatch $batch): void
    {
        $contents = file_get_contents($file->getRealPath());
        $path = 'private/results-sms/'.$batch->public_id.'.'.$file->getClientOriginalExtension().'.enc';
        $envelope = json_encode([
            'version' => 1,
            'sha256' => hash('sha256', $contents),
            // Keep the value passed to the encrypter text-safe for binary XLSX files.
            'contents' => base64_encode($contents),
        ], JSON_THROW_ON_ERROR);

        if (! Storage::disk('local')->put($path, Crypt::encryptString($envelope))) {
            throw new RuntimeException('The protected upload could not be stored.');
        }

        $batch->update([
            'original_filename' => $file->getClientOriginalName(),
            'stored_path' => $path,
            'file_hash' => hash('sha256', $contents),
            'file_extension' => strtolower($file->getClientOriginalExtension()),
        ]);
    }

    /** @return array{headers: array<int, string>, rows: array<int, array<int, mixed>>} */
    public function read(ResultsSmsUploadBatch $batch): array
    {
        $temporaryPath = $this->temporaryFile($batch);

        try {
            $reader = IOFactory::createReaderForFile($temporaryPath);
            $reader->setReadDataOnly(true);
            $worksheet = $reader->load($temporaryPath)->getActiveSheet();
            // Never evaluate spreadsheet formulas while inspecting an upload.
            $rows = $worksheet->toArray(null, false, true, false);
        } finally {
            @unlink($temporaryPath);
        }

        if ($rows === []) {
            return ['headers' => [], 'rows' => []];
        }

        $headers = array_map(fn ($value) => $this->normaliseHeader((string) $value), array_shift($rows));

        return ['headers' => $headers, 'rows' => $rows];
    }

    public function temporaryFile(ResultsSmsUploadBatch $batch): string
    {
        try {
            $decrypted = Crypt::decryptString(Storage::disk('local')->get($batch->stored_path));
        } catch (DecryptException $exception) {
            throw new RuntimeException('The protected upload could not be decrypted.', previous: $exception);
        }

        $contents = $this->contentsFromEnvelope($decrypted, $batch);
        $temporaryPath = tempnam(sys_get_temp_dir(), 'results-sms-');
        $extension = $batch->file_extension === 'csv' ? '.csv' : '.xlsx';
        $target = $temporaryPath.$extension;
        rename($temporaryPath, $target);
        file_put_contents($target, $contents, LOCK_EX);

        return $target;
    }

    private function contentsFromEnvelope(string $decrypted, ResultsSmsUploadBatch $batch): string
    {
        try {
            $envelope = json_decode($decrypted, true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            // Files stored before envelope support contain the original bytes.
            return $decrypted;
        }

        if (! is_array($envelope) || ($envelope['version'] ?? null) !== 1 || ! isset($envelope['contents'], $envelope['sha256'])) {
            throw new RuntimeException('The protected upload has an unsupported format.');
        }

        $contents = base64_decode($envelope['contents'], true);
        if ($contents === false || ! hash_equals($envelope['sha256'], hash('sha256', $contents)) || ! hash_equals($batch->file_hash, $envelope['sha256'])) {
            throw new RuntimeException('The protected upload did not pass its integrity check.');
        }

        return $contents;
    }

    public function normaliseHeader(string $header): string
    {
        return mb_strtolower(trim(str_replace("\xEF\xBB\xBF", '', $header)));
    }

    public function maskPhone(string $number): string
    {
        $visible = min(3, strlen($number));

        return str_repeat('•', max(0, strlen($number) - $visible)).substr($number, -$visible);
    }

    public function safeForSpreadsheet(?string $value): string
    {
        $value = (string) $value;

        return preg_match('/^[=+\-@]/', $value) ? "'".$value : $value;
    }

    public function findActiveStudents(array $studentIds): array
    {
        $students = [];
        foreach (array_chunk(array_values(array_unique(array_filter($studentIds))), 1000) as $studentIdChunk) {
            Student::active()
                ->whereIn('student_id', $studentIdChunk)
                ->get(['id', 'student_id', 'mobile_number'])
                ->each(fn (Student $student) => $students[trim((string) $student->student_id)] = $student);
        }

        return $students;
    }
}
