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
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TranscriptExport implements FromCollection, ShouldAutoSize, WithEvents, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $transcriptData;

    public function __construct($transcriptData)
    {
        $this->transcriptData = $transcriptData;
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
        return 'Student Transcript';
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
                $sheet->setCellValue('A1', 'OFFICIAL TRANSCRIPT');
                $sheet->mergeCells('A1:I1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->setCellValue('A3', 'Student ID:');
                $sheet->setCellValue('B3', $student->student_id);
                $sheet->setCellValue('A4', 'Student Name:');
                $sheet->setCellValue('B4', $student->full_name);
                $sheet->setCellValue('A5', 'Class:');
                $sheet->setCellValue('B5', $student->collegeClass->name ?? 'N/A');
                $sheet->setCellValue('A6', 'Academic Year:');
                $sheet->setCellValue('B6', $this->transcriptData['academic_year']->name ?? 'N/A');
                $sheet->setCellValue('A7', 'Generated:');
                $sheet->setCellValue('B7', $this->transcriptData['generated_at']->format('Y-m-d H:i:s'));

                // Style student info
                $sheet->getStyle('A3:A7')->getFont()->setBold(true);

                // Course data starts at row 9 (after headings)
                $lastRow = $sheet->getHighestRow();

                // Add summary section
                $summaryStartRow = $lastRow + 2;
                $sheet->setCellValue('A'.$summaryStartRow, 'ACADEMIC SUMMARY');
                $sheet->mergeCells('A'.$summaryStartRow.':I'.$summaryStartRow);
                $sheet->getStyle('A'.$summaryStartRow)->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A'.$summaryStartRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $summaryRow = $summaryStartRow + 2;
                $sheet->setCellValue('A'.$summaryRow, 'Total Credit Hours Attempted:');
                $sheet->setCellValue('C'.$summaryRow, $summary['total_credit_hours_attempted']);
                $summaryRow++;
                $sheet->setCellValue('A'.$summaryRow, 'Total Credit Hours Earned:');
                $sheet->setCellValue('C'.$summaryRow, $summary['total_credit_hours_earned']);
                $summaryRow++;
                $sheet->setCellValue('A'.$summaryRow, 'Total Grade Points:');
                $sheet->setCellValue('C'.$summaryRow, $summary['total_grade_points']);
                $summaryRow++;
                $sheet->setCellValue('A'.$summaryRow, 'Semester GPA:');
                $sheet->setCellValue('C'.$summaryRow, $summary['semester_gpa']);
                $summaryRow++;
                $sheet->setCellValue('A'.$summaryRow, 'Cumulative GPA:');
                $sheet->setCellValue('C'.$summaryRow, $summary['cumulative_gpa']);

                // Style summary
                $sheet->getStyle('A'.($summaryStartRow + 2).':A'.$summaryRow)->getFont()->setBold(true);

                // Add borders to the course table
                $courseTableStart = 9;
                $courseTableEnd = $lastRow;
                $sheet->getStyle('A'.$courseTableStart.':I'.$courseTableEnd)
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

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
