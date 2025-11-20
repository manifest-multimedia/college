<?php

namespace App\Exports;

use App\Models\OfflineExamScore;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OfflineExamScoresExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $examId;

    protected $classId;

    public function __construct($examId, $classId = null)
    {
        $this->examId = $examId;
        $this->classId = $classId;
    }

    public function collection()
    {
        $query = OfflineExamScore::with(['student.collegeClass', 'offlineExam.course', 'recordedBy'])
            ->where('offline_exam_id', $this->examId);

        if ($this->classId) {
            $query->whereHas('student', function ($q) {
                $q->where('college_class_id', $this->classId);
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function map($score): array
    {
        return [
            $score->student->student_id ?? 'N/A',
            $score->student->full_name ?? 'N/A',
            $score->student->collegeClass->name ?? 'N/A',
            $score->score,
            $score->total_marks,
            number_format($score->percentage, 1).'%',
            $score->grade_letter,
            $score->is_passed ? 'Passed' : 'Failed',
            $score->remarks ?? '',
            $score->recordedBy->name ?? 'N/A',
            $score->created_at->format('d/m/Y H:i'),
            $score->exam_date ? $score->exam_date->format('d/m/Y') : 'N/A',
        ];
    }

    public function headings(): array
    {
        return [
            'Student ID',
            'Student Name',
            'Class',
            'Score',
            'Total Marks',
            'Percentage',
            'Grade',
            'Status',
            'Remarks',
            'Recorded By',
            'Recorded Date',
            'Exam Date',
        ];
    }

    public function title(): string
    {
        return 'Offline Exam Scores';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the header row
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '366092'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ],
            // Style for all data rows
            'A2:L1000' => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
}
