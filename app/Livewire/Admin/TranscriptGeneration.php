<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Student;
use App\Models\CollegeClass;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Services\TranscriptService;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use App\Exports\BulkTranscriptExport;

class TranscriptGeneration extends Component
{
    use WithPagination;

    // Search and filter properties
    public $search = '';
    public $selectedClassId = null;
    public $selectedAcademicYearId = null;
    public $selectedSemesterId = null;
    public $selectedFormat = 'pdf';
    public $perPage = 15;

    // Individual transcript generation
    public $selectedStudentId = null;
    public $showTranscriptModal = false;
    public $transcriptData = null;
    public $isGenerating = false;

    // Bulk generation
    public $selectedStudents = [];
    public $bulkGeneration = false;
    public $bulkProgress = 0;

    public function mount()
    {
        $this->selectedAcademicYearId = AcademicYear::where('is_current', true)->first()?->id;
        $this->selectedSemesterId = Semester::where('is_current', true)->first()?->id;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedClassId()
    {
        $this->resetPage();
        $this->selectedStudents = [];
    }

    public function updatedSelectedAcademicYearId()
    {
        $this->selectedStudents = [];
    }

    public function updatedSelectedSemesterId()
    {
        $this->selectedStudents = [];
    }

    public function generateTranscript($studentId)
    {
        $this->selectedStudentId = $studentId;
        $this->isGenerating = true;

        try {
            $transcriptService = new TranscriptService();
            $this->transcriptData = $transcriptService->generateTranscriptData(
                $studentId,
                $this->selectedAcademicYearId,
                $this->selectedSemesterId
            );

            $this->showTranscriptModal = true;

        } catch (\Exception $e) {
            Log::error('Error generating transcript', [
                'student_id' => $studentId,
                'error' => $e->getMessage()
            ]);

            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error generating transcript: ' . $e->getMessage(),
                'timer' => 5000
            ]);
        }

        $this->isGenerating = false;
    }

    public function downloadTranscript($format = null)
    {
        if (!$this->transcriptData) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No transcript data available.',
                'timer' => 3000
            ]);
            return;
        }

        $format = $format ?? $this->selectedFormat;

        try {
            $transcriptService = new TranscriptService();
            
            if ($format === 'csv') {
                return $this->downloadCSV();
            } elseif ($format === 'pdf') {
                return $transcriptService->generatePDF($this->transcriptData);
            } elseif ($format === 'excel') {
                return $transcriptService->generateExcel($this->transcriptData);
            }

        } catch (\Exception $e) {
            Log::error('Error downloading transcript', [
                'student_id' => $this->selectedStudentId,
                'format' => $format,
                'error' => $e->getMessage()
            ]);

            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error downloading transcript: ' . $e->getMessage(),
                'timer' => 5000
            ]);
        }
    }

    protected function downloadCSV()
    {
        $transcriptService = new TranscriptService();
        $csvData = $transcriptService->generateCSV($this->transcriptData);
        $student = $this->transcriptData['student'];
        $filename = 'transcript_' . $student->student_id . '_' . now()->format('Y-m-d') . '.csv';

        $callback = function() use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function toggleStudentSelection($studentId)
    {
        if (in_array($studentId, $this->selectedStudents)) {
            $this->selectedStudents = array_filter($this->selectedStudents, function($id) use ($studentId) {
                return $id != $studentId;
            });
        } else {
            $this->selectedStudents[] = $studentId;
        }
    }

    public function selectAllStudents()
    {
        $students = $this->getFilteredStudents();
        $this->selectedStudents = $students->pluck('id')->toArray();
    }

    public function deselectAllStudents()
    {
        $this->selectedStudents = [];
    }

    public function bulkGenerateTranscripts()
    {
        if (empty($this->selectedStudents)) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Please select at least one student.',
                'timer' => 3000
            ]);
            return;
        }

        $this->bulkGeneration = true;
        $this->bulkProgress = 0;

        try {
            $totalStudents = count($this->selectedStudents);
            $transcriptService = new TranscriptService();

            if ($this->selectedFormat === 'pdf') {
                return $this->bulkGeneratePDFs($transcriptService, $totalStudents);
            } elseif ($this->selectedFormat === 'excel') {
                return $this->bulkGenerateExcel($transcriptService, $totalStudents);
            } else {
                return $this->bulkGenerateCSV($transcriptService, $totalStudents);
            }

        } catch (\Exception $e) {
            Log::error('Error in bulk transcript generation', [
                'error' => $e->getMessage(),
                'selected_students' => $this->selectedStudents
            ]);

            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error generating bulk transcripts: ' . $e->getMessage(),
                'timer' => 5000
            ]);
        } finally {
            $this->bulkGeneration = false;
            $this->bulkProgress = 0;
            $this->selectedStudents = [];
        }
    }

    protected function bulkGeneratePDFs($transcriptService, $totalStudents)
    {
        // Create temporary directory for PDFs
        $tempDir = storage_path('app/temp/transcripts/' . uniqid());
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $fileNames = [];

        foreach ($this->selectedStudents as $index => $studentId) {
            $transcriptData = $transcriptService->generateTranscriptData(
                $studentId,
                $this->selectedAcademicYearId,
                $this->selectedSemesterId
            );

            // Generate PDF content
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.transcript-pdf', $transcriptData);
            $fileName = 'transcript_' . $transcriptData['student']->student_id . '.pdf';
            $filePath = $tempDir . '/' . $fileName;
            
            file_put_contents($filePath, $pdf->output());
            $fileNames[] = $filePath;

            $this->bulkProgress = round((($index + 1) / $totalStudents) * 90); // Reserve 10% for ZIP creation
        }

        // Create ZIP file
        $zipFileName = 'transcripts_' . now()->format('Y-m-d_H-i-s') . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);
        
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            foreach ($fileNames as $filePath) {
                $zip->addFile($filePath, basename($filePath));
            }
            $zip->close();
            
            // Clean up individual PDF files
            foreach ($fileNames as $filePath) {
                unlink($filePath);
            }
            rmdir($tempDir);
            
            $this->bulkProgress = 100;
            
            // Return the ZIP file for download
            return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
        } else {
            throw new \Exception('Failed to create ZIP file');
        }
    }

    protected function bulkGenerateExcel($transcriptService, $totalStudents)
    {
        // For Excel, we'll create one file with multiple sheets
        $allTranscriptData = [];
        
        foreach ($this->selectedStudents as $index => $studentId) {
            $transcriptData = $transcriptService->generateTranscriptData(
                $studentId,
                $this->selectedAcademicYearId,
                $this->selectedSemesterId
            );
            $allTranscriptData[] = $transcriptData;
            
            $this->bulkProgress = round((($index + 1) / $totalStudents) * 100);
        }

        // Generate Excel file with multiple sheets
        $fileName = 'bulk_transcripts_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(
            new BulkTranscriptExport($allTranscriptData), 
            $fileName
        );
    }

    protected function bulkGenerateCSV($transcriptService, $totalStudents)
    {
        $allCsvData = [];
        
        foreach ($this->selectedStudents as $index => $studentId) {
            $transcriptData = $transcriptService->generateTranscriptData(
                $studentId,
                $this->selectedAcademicYearId,
                $this->selectedSemesterId
            );
            
            $csvData = $transcriptService->generateCSV($transcriptData);
            
            // Add separator between students
            if ($index > 0) {
                $allCsvData[] = []; // Empty row
                $allCsvData[] = ['==========================================='];
                $allCsvData[] = []; // Empty row
            }
            
            $allCsvData = array_merge($allCsvData, $csvData);
            
            $this->bulkProgress = round((($index + 1) / $totalStudents) * 100);
        }

        $fileName = 'bulk_transcripts_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $callback = function() use ($allCsvData) {
            $file = fopen('php://output', 'w');
            foreach ($allCsvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function closeTranscriptModal()
    {
        $this->showTranscriptModal = false;
        $this->transcriptData = null;
        $this->selectedStudentId = null;
    }

    protected function getFilteredStudents()
    {
        $query = Student::with(['collegeClass', 'user']);

        if ($this->selectedClassId) {
            $query->where('college_class_id', $this->selectedClassId);
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('student_id', 'like', '%' . $this->search . '%')
                  ->orWhere('first_name', 'like', '%' . $this->search . '%')
                  ->orWhere('last_name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        return $query->orderBy('student_id');
    }

    public function render()
    {
        $students = $this->getFilteredStudents()->paginate($this->perPage);
        
        $collegeClasses = CollegeClass::orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('year', 'desc')->get();
        $semesters = Semester::orderBy('name')->get();

        return view('livewire.admin.transcript-generation', [
            'students' => $students,
            'collegeClasses' => $collegeClasses,
            'academicYears' => $academicYears,
            'semesters' => $semesters,
        ]);
    }
}
