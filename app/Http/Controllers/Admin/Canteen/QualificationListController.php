<?php

namespace App\Http\Controllers\Admin\Canteen;

use App\Http\Controllers\Controller;
use App\Models\CanteenQualificationImport;
use App\Services\Canteen\CanteenPolicy;
use App\Services\Canteen\PortalQualificationImports;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class QualificationListController extends Controller
{
    public function __construct(private PortalQualificationImports $imports) {}

    public function index()
    {
        $batches = CanteenQualificationImport::where('institution_prefix', $this->imports->prefix())
            ->with(['importedBy', 'revokedBy'])->withCount(['qualifications', 'qualifications as qualified_count' => fn ($q) => $q->where('qualified', true)])
            ->orderByDesc('id')->paginate(20);

        return view('canteen.qualification-lists', ['batches' => $batches, 'period' => app(CanteenPolicy::class)->period(),
            'prefix' => $this->imports->prefix(), 'imports' => $this->imports]);
    }

    public function upload(Request $request)
    {
        $data = $request->validate(['files' => ['required', 'array', 'min:1', 'max:8'], 'files.*' => ['required', 'file', 'max:1024'],
            'academic_year' => ['required', 'string', 'max:32'], 'semester' => ['required', 'integer', 'min:1', 'max:99'], 'activate' => ['nullable', 'boolean']]);
        $token = $this->imports->prepare($data['files'], $data['academic_year'], (int) $data['semester'], $request->boolean('activate'), $request->user()->id);

        return redirect()->route('canteen.lists.preview', $token);
    }

    public function preview(Request $request, string $token)
    {
        try {
            $preview = $this->imports->preview($token, $request->user()->id);
        } catch (ValidationException $error) {
            return redirect()->route('canteen.lists.index')->withErrors($error->errors());
        }

        return view('canteen.qualification-preview', ['preview' => $preview]);
    }

    public function confirm(Request $request, string $token)
    {
        $request->validate(['acknowledge' => ['accepted']]);
        try {
            $result = $this->imports->commit($token, $request->user()->id);
        } catch (ValidationException $error) {
            return redirect()->route('canteen.lists.index')->withErrors($error->errors());
        } catch (HttpExceptionInterface $error) {
            throw $error;
        } catch (Throwable $error) {
            report($error);

            return redirect()->route('canteen.lists.index')->withErrors(['files' => 'The import could not complete. Upload and preview again.']);
        }
        $count = array_sum(array_column($result['files'], 'qualifications_created'));

        return redirect()->route('canteen.lists.index')->with('canteen_status', $count.' new qualification statuses imported. Unchanged files were left as recorded.');
    }

    public function withdrawal(string $id)
    {
        return view('canteen.qualification-withdrawal', ['batch' => $this->batch($id)]);
    }

    public function revoke(Request $request, string $id)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:500'], 'acknowledge' => ['accepted']]);
        Cache::lock('canteen.portal.import.'.hash('sha256', $this->imports->prefix()), 120)->block(5, function () use ($id, $request, $data) {
            DB::transaction(function () use ($id, $request, $data) {
                $batch = $this->batch($id, true);
                if (! $batch->revoked_at) {
                    $batch->update(['revoked_at' => now(), 'revoked_by' => $request->user()->id, 'revocation_reason' => trim($data['reason'])]);
                }
            });
        });

        return redirect()->route('canteen.lists.index')->with('canteen_status', 'List withdrawn. Its history is retained and older approvals will not be reactivated.');
    }

    public function source(string $id)
    {
        $batch = $this->batch($id);
        $path = $this->imports->archivePath($batch->institution_prefix, $batch->file_sha256);
        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path, basename($batch->file_name), ['Cache-Control' => 'no-store, private']);
    }

    private function batch(string $id, bool $lock = false): CanteenQualificationImport
    {
        $query = CanteenQualificationImport::where('institution_prefix', $this->imports->prefix());
        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->findOrFail($id);
    }
}
