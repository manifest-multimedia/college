<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AssessmentScoresExport implements WithMultipleSheets
{
    protected $scores;

    protected $courseInfo;

    protected $weights;

    public function __construct($scores, $courseInfo, $weights)
    {
        $this->scores = $scores;
        $this->courseInfo = $courseInfo;
        $this->weights = $weights;
    }

    public function sheets(): array
    {
        return [
            new AssessmentScoresSheet($this->scores, $this->courseInfo, $this->weights),
            new InstructionsSheet($this->weights),
        ];
    }
}

class AssessmentScoresSheet implements FromCollection, ShouldAutoSize, WithEvents, WithHeadings, WithStyles, WithTitle
{
    protected $scores;

    protected $courseInfo;

    protected $weights;

    public function __construct($scores, $courseInfo, $weights)
    {
        $this->scores = $scores;
        $this->courseInfo = $courseInfo;
        $this->weights = $weights;
    }

    public function collection(): Collection
    {
        return $this->scores->map(function ($score, $index) {
            return [
                'sn' => $index + 1,
                'index_no' => $score['student_number'],
                'student_name' => $score['student_name'],
                'assignment_1' => $score['assignment_1'] ?? '',
                'assignment_2' => $score['assignment_2'] ?? '',
                'assignment_3' => $score['assignment_3'] ?? '',
                'assignment_avg' => $score['assignment_average'] ?? '',
                'assignment_weighted' => $score['assignment_weighted'] ?? '',
                'mid_semester' => $score['mid_semester'] ?? '',
                'mid_weighted' => $score['mid_weighted'] ?? '',
                'end_semester' => $score['end_semester'] ?? '',
                'end_weighted' => $score['end_weighted'] ?? '',
                'total' => $score['total'] ?? '',
                'grade' => $score['grade'] ?? '',
            ];
        });
    }

    public function headings(): array
    {
        return [
            ['COURSE: '.$this->courseInfo['course'], '', '', '', '', '', '', '', 'SEMESTER: '.$this->courseInfo['semester']],
            ['PROGRAMME: '.$this->courseInfo['programme'], '', '', '', '', '', '', '', 'ACADEMIC YEAR: '.$this->courseInfo['academic_year']],
            ['CLASS: '.$this->courseInfo['class'], '', '', '', '', '', '', '', 'DATE: '.now()->format('Y-m-d')],
            [],
            [
                'S/N',
                'INDEX NO',
                'STUDENT NAME',
                'ASSIGN 1',
                'ASSIGN 2',
                'ASSIGN 3',
                'AVG',
                'WEIGHTED',
                'MID-SEM',
                'WEIGHTED',
                'END-SEM',
                'WEIGHTED',
                'TOTAL',
                'GRADE',
            ],
            [
                '',
                '',
                '',
                '100%',
                '100%',
                '100%',
                '100%',
                $this->weights['assignment'].'%',
                '100%',
                $this->weights['mid_semester'].'%',
                '100%',
                $this->weights['end_semester'].'%',
                '100%',
                '',
            ],
        ];
    }

    public function title(): string
    {
        return 'Assessment Scores';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
            2 => ['font' => ['bold' => true]],
            3 => ['font' => ['bold' => true]],
            5 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D3D3D3']]],
            6 => ['font' => ['italic' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0F0F0']]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Merge cells for header
                $sheet->mergeCells('A1:H1');
                $sheet->mergeCells('I1:N1');
                $sheet->mergeCells('A2:H2');
                $sheet->mergeCells('I2:N2');
                $sheet->mergeCells('A3:H3');
                $sheet->mergeCells('I3:N3');

                // Apply borders
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $sheet->getStyle('A5:'.$highestColumn.$highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Left align student names
                $sheet->getStyle('C7:C'.$highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            },
        ];
    }
}

class InstructionsSheet implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    protected $weights;

    public function __construct($weights)
    {
        $this->weights = $weights;
    }

    public function collection(): Collection
    {
        return collect([
            ['CONTINUOUS ASSESSMENT SCORE SHEET INSTRUCTIONS'],
            [''],
            ['1. GRADING SCALE:'],
            ['   A:  80-100%'],
            ['   B+: 75-79%'],
            ['   B:  70-74%'],
            ['   C+: 65-69%'],
            ['   C:  60-64%'],
            ['   D+: 55-59%'],
            ['   D:  50-54%'],
            ['   E:  0-49% (Fail)'],
            [''],
            ['2. WEIGHT CONFIGURATION:'],
            ['   Assignments: '.$this->weights['assignment'].'% (average of all assignments)'],
            ['   Mid-Semester Exam: '.$this->weights['mid_semester'].'%'],
            ['   End-of-Semester Exam: '.$this->weights['end_semester'].'%'],
            [''],
            ['3. CALCULATION EXAMPLE:'],
            ['   Assignment 1: 85%, Assignment 2: 90%, Assignment 3: 80%'],
            ['   Assignment Average: (85+90+80)/3 = 85.0%'],
            ['   Assignment Weighted: 85.0 × '.$this->weights['assignment'].'% = '.round(85 * $this->weights['assignment'] / 100, 1)],
            [''],
            ['   Mid-Semester: 75%'],
            ['   Mid-Sem Weighted: 75 × '.$this->weights['mid_semester'].'% = '.round(75 * $this->weights['mid_semester'] / 100, 1)],
            [''],
            ['   End-Semester: 82%'],
            ['   End-Sem Weighted: 82 × '.$this->weights['end_semester'].'% = '.round(82 * $this->weights['end_semester'] / 100, 1)],
            [''],
            ['   TOTAL: '.round(85 * $this->weights['assignment'] / 100, 1).' + '.round(75 * $this->weights['mid_semester'] / 100, 1).' + '.round(82 * $this->weights['end_semester'] / 100, 1).' = '.round(85 * $this->weights['assignment'] / 100 + 75 * $this->weights['mid_semester'] / 100 + 82 * $this->weights['end_semester'] / 100, 1).'%'],
            ['   GRADE: B+ (75 ≤ '.round(85 * $this->weights['assignment'] / 100 + 75 * $this->weights['mid_semester'] / 100 + 82 * $this->weights['end_semester'] / 100, 1).' < 80)'],
            [''],
            ['4. NOTES:'],
            ['   - Empty cells are treated as not yet entered (not zero)'],
            ['   - To mark absent/failed, enter 0'],
            ['   - Decimal scores are supported (e.g., 85.5)'],
            ['   - Only entered assignments are included in the average'],
        ]);
    }

    public function headings(): array
    {
        return [];
    }

    public function title(): string
    {
        return 'Instructions';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            3 => ['font' => ['bold' => true]],
            13 => ['font' => ['bold' => true]],
            18 => ['font' => ['bold' => true]],
            31 => ['font' => ['bold' => true]],
        ];
    }
}
