<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TranscriptSheetExport implements FromCollection, ShouldAutoSize, WithEvents, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $transcriptData;

    protected $sheetTitle;

    public function __construct($transcriptData, $sheetTitle)
    {
        $this->transcriptData = $transcriptData;
        $this->sheetTitle = substr($sheetTitle, 0, 31); // Excel sheet name limit
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return collect($this->transcriptData['transcript_entries']);
    }

    /**
     * @param  mixed  $entry
     */
    public function map($entry): array
    {
        return [
            $entry['subject_code'],
            $entry['subject_name'],
            $entry['credit_hours'],
            $entry['online_score'] ?? 'N/A',
            $entry['offline_score'] ?? 'N/A',
            $entry['final_score'],
            $entry['letter_grade'],
            $entry['grade_points'],
            $entry['status'],
        ];
    }

    public function headings(): array
    {
        return [
            'Course Code',
            'Course Name',
            'Credit Hours',
            'Online Score (%)',
            'Offline Score (%)',
            'Final Score (%)',
            'Letter Grade',
            'Grade Points',
            'Status',
        ];
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }

    /**
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $student = $this->transcriptData['student'];
                $summary = $this->transcriptData['summary'];

                // Insert student info at the top
                $sheet->insertNewRowBefore(1, 8);

                // Student information
                $sheet->setCellValue('A1', 'TRANSCRIPT - '.$student->student_id);
                $sheet->mergeCells('A1:I1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->setCellValue('A3', 'Student ID:');
                $sheet->setCellValue('B3', $student->student_id);
                $sheet->setCellValue('A4', 'Student Name:');
                $sheet->setCellValue('B4', $student->full_name);
                $sheet->setCellValue('A5', 'Class:');
                $sheet->setCellValue('B5', $student->collegeClass->name ?? 'N/A');
                $sheet->setCellValue('A6', 'Generated:');
                $sheet->setCellValue('B6', $this->transcriptData['generated_at']->format('Y-m-d H:i:s'));

                // Style student info
                $sheet->getStyle('A3:A6')->getFont()->setBold(true);

                // Course data starts at row 9 (after headings)
                $lastRow = $sheet->getHighestRow();

                // Add summary section
                $summaryStartRow = $lastRow + 2;
                $sheet->setCellValue('A'.$summaryStartRow, 'SUMMARY');
                $sheet->mergeCells('A'.$summaryStartRow.':I'.$summaryStartRow);
                $sheet->getStyle('A'.$summaryStartRow)->getFont()->setBold(true);

                $summaryRow = $summaryStartRow + 1;
                $sheet->setCellValue('A'.$summaryRow, 'Semester GPA: '.$summary['semester_gpa']);
                $sheet->setCellValue('D'.$summaryRow, 'Cumulative GPA: '.$summary['cumulative_gpa']);
                $summaryRow++;
                $sheet->setCellValue('A'.$summaryRow, 'Credit Hours Earned: '.$summary['total_credit_hours_earned']);
                $sheet->setCellValue('D'.$summaryRow, 'Grade Points: '.$summary['total_grade_points']);

                // Set column widths
                $sheet->getColumnDimension('A')->setWidth(12);
                $sheet->getColumnDimension('B')->setWidth(30);
                $sheet->getColumnDimension('C')->setWidth(12);
                $sheet->getColumnDimension('D')->setWidth(15);
                $sheet->getColumnDimension('E')->setWidth(15);
                $sheet->getColumnDimension('F')->setWidth(15);
                $sheet->getColumnDimension('G')->setWidth(12);
                $sheet->getColumnDimension('H')->setWidth(12);
                $sheet->getColumnDimension('I')->setWidth(10);
            },
        ];
    }
}
