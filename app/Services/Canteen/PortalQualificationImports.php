<?php

namespace App\Services\Canteen;

use App\Models\CanteenQualification;
use App\Models\CanteenQualificationImport;
use App\Services\StudentIdGenerationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;
use ZipArchive;

class PortalQualificationImports
{
    public function prefix(): string
    {
        $prefix = strtoupper(trim(app(StudentIdGenerationService::class)->configuredInstitutionPrefix() ?? ''));
        if ($prefix === '') {
            throw ValidationException::withMessages(['files' => 'Configure the College institution prefix before importing.']);
        }

        return $prefix;
    }

    private function state(string $year, int $semester): string
    {
        return hash('sha256', json_encode([
            $this->prefix(), app(CanteenPolicy::class)->period(),
            CanteenQualificationImport::where('institution_prefix', $this->prefix())->where('academic_year', $year)->where('semester', $semester)
                ->orderBy('id')->get(['id', 'file_sha256', 'revoked_at'])->toArray(),
        ]));
    }

    public function prepare(array $uploads, string $year, int $semester, bool $activate, int $actor): string
    {
        $this->purgeExpired();
        $token = (string) Str::uuid();
        $directory = Storage::disk('local')->path('canteen/previews/'.$token);
        File::ensureDirectoryExists($directory, 0700);
        $files = [];
        try {
            $total = 0;
            foreach ($uploads as $upload) {
                $name = basename(str_replace('\\', '/', $upload->getClientOriginalName()));
                $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                $name = pathinfo($name, PATHINFO_FILENAME).'.'.$extension;
                if (! in_array($extension, ['xlsx', 'xlsm'], true) || isset($files[$name])) {
                    throw ValidationException::withMessages(['files' => 'Upload distinct .xlsx or .xlsm files only.']);
                }
                $total += $upload->getSize();
                if ($total > 5 * 1024 * 1024) {
                    throw ValidationException::withMessages(['files' => 'The combined upload must be at most 5 MB.']);
                }
                $path = $directory.'/'.$name;
                if ($name === '' || str_starts_with($name, '.') || preg_match('/[\x00-\x1f]/', $name)) {
                    throw ValidationException::withMessages(['files' => 'One workbook has an unsupported file name.']);
                }
                $upload->move($directory, $name);
                chmod($path, 0600);
                $this->checkWorkbook($path);
                $files[$name] = hash_file('sha256', $path);
            }
            $state = $this->state($year, $semester);
            $previous = CanteenQualificationImport::where('institution_prefix', $this->prefix())->where('academic_year', $year)->where('semester', $semester)->whereNull('revoked_at')->get();
            DB::beginTransaction();
            try {
                $result = app(CanteenQualificationImportService::class)->importFromDirectory($directory, $year, $semester, $actor);
                $this->assertResult($result);
                foreach ($result['files'] as &$file) {
                    $batch = CanteenQualificationImport::findOrFail($file['import_id']);
                    $file['revoked_replay'] = $batch->revoked_at !== null;
                    $replaced = $file['replayed'] ? collect() : $previous->where('class_label', $file['class'])->where('id', '!=', $batch->id);
                    $newIndexes = CanteenQualification::where('import_id', $batch->id)->pluck('index_number');
                    $file['replaces'] = $replaced->pluck('id')->values()->all();
                    $file['omitted'] = $file['replayed'] ? 0 : CanteenQualification::whereIn('import_id', $file['replaces'])->whereNotIn('index_number', $newIndexes)->distinct()->count('index_number');
                    unset($file['import_id']); // Rolled-back IDs must never be presented as committed audit IDs.
                }
                unset($file);
            } finally {
                DB::rollBack();
            }
            $preview = ['token' => $token, 'actor' => $actor, 'prefix' => $this->prefix(), 'year' => $year, 'semester' => $semester,
                'activate' => $activate, 'state' => $state, 'files' => $files, 'summary' => $result['files'], 'expires_at' => now()->addMinutes(30)->toIso8601String()];
            $old = Cache::get('canteen.preview.owner.'.$actor);
            if ($old) {
                $this->discard($old, $actor);
            }
            Cache::put('canteen.preview.'.$token, $preview, now()->addMinutes(30));
            Cache::put('canteen.preview.owner.'.$actor, $token, now()->addMinutes(30));

            return $token;
        } catch (Throwable $error) {
            File::deleteDirectory($directory);
            if ($error instanceof ValidationException) {
                throw $error;
            }
            report($error);
            throw ValidationException::withMessages(['files' => 'Unable to read the workbooks. Check their layout, file sizes and contents.']);
        }
    }

    public function preview(string $token, int $actor): array
    {
        $preview = Cache::get('canteen.preview.'.$token);
        abort_if($preview && $preview['actor'] !== $actor, 403);
        if (! $preview || $preview['prefix'] !== $this->prefix() || now()->gte($preview['expires_at'])) {
            throw ValidationException::withMessages(['files' => 'This preview expired or the institution changed. Upload the files again.']);
        }

        return $preview;
    }

    public function commit(string $token, int $actor): array
    {
        return Cache::lock('canteen.portal.import.'.hash('sha256', $this->prefix()), 120)->block(5, function () use ($token, $actor) {
            $preview = $this->preview($token, $actor);
            if ($preview['state'] !== $this->state($preview['year'], $preview['semester'])) {
                throw ValidationException::withMessages(['files' => 'The lists or active period changed after this preview. Upload and preview again.']);
            }
            $directory = Storage::disk('local')->path('canteen/previews/'.$token);
            foreach ($preview['files'] as $name => $hash) {
                if (! is_file($directory.'/'.$name) || hash_file('sha256', $directory.'/'.$name) !== $hash) {
                    throw ValidationException::withMessages(['files' => 'An uploaded file changed or is missing. Upload and preview again.']);
                }
            }
            // Retain exact evidence privately; source files are never served from public storage.
            foreach ($preview['files'] as $name => $hash) {
                $archive = $this->archivePath($preview['prefix'], $hash);
                if (! Storage::disk('local')->put($archive, file_get_contents($directory.'/'.$name))) {
                    throw ValidationException::withMessages(['files' => 'The private source archive could not be saved. No lists were imported.']);
                }
            }
            $result = DB::transaction(function () use ($preview, $directory, $actor) {
                $result = app(CanteenQualificationImportService::class)->importFromDirectory($directory, $preview['year'], $preview['semester'], $actor);
                $this->assertResult($result);
                if ($preview['activate']) {
                    app(CanteenPolicy::class)->setPeriod($preview['year'], $preview['semester']);
                }

                return $result;
            });
            $this->discard($token, $actor);

            return $result;
        });
    }

    public function archivePath(string $prefix, string $hash): string
    {
        return 'canteen/sources/'.hash('sha256', $prefix).'/'.$hash.'.workbook';
    }

    public function discard(string $token, int $actor): void
    {
        $preview = Cache::get('canteen.preview.'.$token);
        if ($preview && $preview['actor'] === $actor) {
            File::deleteDirectory(Storage::disk('local')->path('canteen/previews/'.$token));
            Cache::forget('canteen.preview.'.$token);
            if (Cache::get('canteen.preview.owner.'.$actor) === $token) {
                Cache::forget('canteen.preview.owner.'.$actor);
            }
        }
    }

    public function purgeExpired(): void
    {
        $root = Storage::disk('local')->path('canteen/previews');
        if (! is_dir($root)) {
            return;
        }
        foreach (File::directories($root) as $directory) {
            if (filemtime($directory) < now()->subHour()->timestamp) {
                File::deleteDirectory($directory);
            }
        }
    }

    private function checkWorkbook(string $path): void
    {
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            throw ValidationException::withMessages(['files' => 'A file is not a readable Excel workbook.']);
        }
        try {
            $expanded = 0;
            if ($zip->numFiles > 2000 || $zip->locateName('xl/workbook.xml') === false) {
                throw new \RuntimeException('Invalid workbook');
            }
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $expanded += $zip->statIndex($i)['size'];
                if ($expanded > 20 * 1024 * 1024) {
                    throw new \RuntimeException('Expanded workbook too large');
                }
            }
        } finally {
            $zip->close();
        }
    }

    private function assertResult(array $result): void
    {
        if (! $result['files'] || $result['errors']) {
            $error = $result['errors'][0] ?? 'No workbooks found.';
            $known = ['The file has', 'A student index', 'Duplicate student index', 'Multiple files describe', 'A student appears', 'Missing class label'];
            $safe = collect($known)->contains(fn ($prefix) => str_starts_with($error, $prefix));
            throw ValidationException::withMessages(['files' => $safe ? $error : 'Workbook validation failed. Check the layout, statuses, institution prefix and duplicate rows.']);
        }
    }
}
