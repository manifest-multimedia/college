<?php
namespace Tests\Feature;
use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
class ExcelExportSignatureTest extends TestCase {
 public static function exports(): array {
  return array_map(fn($class)=>[$class],[
   \App\Exports\ExamResultsExport::class, \App\Exports\ExamResultExport::class,
   \App\Exports\BulkExportResults::class, \App\Exports\ResultsExport::class,
   \App\Exports\ExportToExcel::class, \App\Exports\OfflineExamScoresExport::class,
   \App\Exports\TranscriptExport::class, \App\Exports\TranscriptSheetExport::class,
   \App\Exports\ElectionResultsExport::class,
  ]);
 }
 #[DataProvider('exports')]
 public function test_export_collection_signature_is_compatible(string $class): void {
  $this->assertTrue(is_subclass_of($class,FromCollection::class));
  $this->assertSame(Enumerable::class,(new \ReflectionMethod($class,'collection'))->getReturnType()->getName());
 }
}
