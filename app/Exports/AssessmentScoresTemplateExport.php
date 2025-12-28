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
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AssessmentScoresTemplateExport implements WithMultipleSheets
{
    protected $students;

    protected $courseInfo;

    protected $weights;

    public function __construct($students, $courseInfo, $weights)
    {
        $this->students = $students;
        $this->courseInfo = $courseInfo;
        $this->weights = $weights;
    }

    public function sheets(): array
    {
        return [
            new TemplateSheet($this->students, $this->courseInfo),
            new TemplateInstructionsSheet($this->weights),
        ];
    }
}

class TemplateSheet implements FromCollection, ShouldAutoSize, WithEvents, WithHeadings, WithStyles, WithTitle
{
    protected $students;

    protected $courseInfo;

    public function __construct($students, $courseInfo)
    {
        $this->students = $students;
        $this->courseInfo = $courseInfo;
    }

    public function collection(): Collection
    {
        return $this->students->map(function ($student, $index) {
            return [
                'sn' => $index + 1,
                'index_no' => $student->student_id,
                'student_name' => $student->name,
                'assignment_1' => '',
                'assignment_2' => '',
                'assignment_3' => '',
                'mid_sem' => '',
                'end_sem' => '',
            ];
        });
    }

    public function headings(): array
    {
        return [
            ['COURSE: '.$this->courseInfo['course'], '', '', '', '', '', 'SEMESTER: '.$this->courseInfo['semester']],
            ['PROGRAMME: '.$this->courseInfo['programme'], '', '', '', '', '', 'ACADEMIC YEAR: '.$this->courseInfo['academic_year']],
            ['CLASS: '.$this->courseInfo['class'], '', '', '', '', '', 'DATE: '.now()->format('Y-m-d')],
            [],
            [
                'S/N',
                'INDEX NO',
                'STUDENT NAME',
                'ASSIGNMENT 1',
                'ASSIGNMENT 2',
                'ASSIGNMENT 3',
                'MID-SEM',
                'END-SEM',
            ],
            [
                '',
                '(Required)',
                '(Auto-filled)',
                '(0-100)',
                '(0-100)',
                '(0-100)',
                '(0-100)',
                '(0-100)',
            ],
        ];
    }

    public function title(): string
    {
        return 'Score Entry';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
            2 => ['font' => ['bold' => true]],
            3 => ['font' => ['bold' => true]],
            5 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']], 'font' => ['color' => ['rgb' => 'FFFFFF']]],
            6 => ['font' => ['italic' => true, 'size' => 9], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E7E6E6']]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Merge cells for header
                $sheet->mergeCells('A1:F1');
                $sheet->mergeCells('G1:H1');
                $sheet->mergeCells('A2:F2');
                $sheet->mergeCells('G2:H2');
                $sheet->mergeCells('A3:F3');
                $sheet->mergeCells('G3:H3');

                // Apply borders to data area
                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle('A5:H'.$highestRow)->applyFromArray([
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

                // Lock S/N, INDEX NO, and STUDENT NAME columns
                $sheet->getStyle('A7:C'.$highestRow)->getProtection()->setLocked(Protection::PROTECTION_PROTECTED);
                $sheet->getStyle('A1:H6')->getProtection()->setLocked(Protection::PROTECTION_PROTECTED);

                // Unlock score columns for editing
                $sheet->getStyle('D7:H'.$highestRow)->getProtection()->setLocked(Protection::PROTECTION_UNPROTECTED);

                // Add data validation for score columns (0-100)
                for ($row = 7; $row <= $highestRow; $row++) {
                    foreach (['D', 'E', 'F', 'G', 'H'] as $column) {
                        $validation = $sheet->getCell($column.$row)->getDataValidation();
                        $validation->setType(DataValidation::TYPE_DECIMAL);
                        $validation->setErrorStyle(DataValidation::STYLE_STOP);
                        $validation->setAllowBlank(true);
                        $validation->setOperator(DataValidation::OPERATOR_BETWEEN);
                        $validation->setFormula1('0');
                        $validation->setFormula2('100');
                        $validation->setShowErrorMessage(true);
                        $validation->setErrorTitle('Invalid Score');
                        $validation->setError('Score must be between 0 and 100');
                        $validation->setPromptTitle('Enter Score');
                        $validation->setPrompt('Enter a score between 0 and 100 (decimals allowed). Leave blank if not yet available.');
                        $validation->setShowInputMessage(true);
                    }
                }

                // Protect sheet but allow users to select and edit unlocked cells
                $sheet->getProtection()->setSheet(true);
                $sheet->getProtection()->setSort(false);
                $sheet->getProtection()->setInsertRows(false);
                $sheet->getProtection()->setFormatCells(false);
                $sheet->getProtection()->setPassword(''); // No password, just visual protection
            },
        ];
    }
}

class TemplateInstructionsSheet implements FromCollection, ShouldAutoSize, WithStyles, WithTitle
{
    protected $weights;

    public function __construct($weights)
    {
        $this->weights = $weights;
    }

    public function collection(): Collection
    {
        return collect([
            ['CONTINUOUS ASSESSMENT SCORE IMPORT INSTRUCTIONS'],
            [''],
            ['HOW TO USE THIS TEMPLATE:'],
            [''],
            ['1. DO NOT MODIFY:'],
            ['   - S/N column (auto-numbered)'],
            ['   - INDEX NO column (student identification)'],
            ['   - STUDENT NAME column (pre-filled)'],
            ['   - Header rows (rows 1-6)'],
            [''],
            ['2. ENTER SCORES:'],
            ['   - Enter scores in columns: ASSIGNMENT 1, ASSIGNMENT 2, ASSIGNMENT 3, MID-SEM, END-SEM'],
            ['   - Valid range: 0-100 (decimals allowed, e.g., 85.5)'],
            ['   - Leave cells EMPTY if score not yet available (do NOT enter 0 unless student scored zero)'],
            ['   - To mark a student as absent or failed, enter 0'],
            [''],
            ['3. VALIDATION:'],
            ['   - Cells will show error if you enter values outside 0-100 range'],
            ['   - Empty cells will not overwrite existing scores in the system'],
            ['   - Red background indicates invalid data'],
            [''],
            ['4. AFTER FILLING:'],
            ['   - Save the file'],
            ['   - Go to Assessment Scores module'],
            ['   - Click "Import from Excel" button'],
            ['   - Upload this file'],
            ['   - Review preview before confirming'],
            [''],
            ['5. GRADING SCALE:'],
            ['   A:  80-100%'],
            ['   B+: 75-79%'],
            ['   B:  70-74%'],
            ['   C+: 65-69%'],
            ['   C:  60-64%'],
            ['   D+: 55-59%'],
            ['   D:  50-54%'],
            ['   E:  0-49% (Fail)'],
            [''],
            ['6. WEIGHT CONFIGURATION:'],
            ['   Assignments: '.$this->weights['assignment'].'% (average of all assignments)'],
            ['   Mid-Semester Exam: '.$this->weights['mid_semester'].'%'],
            ['   End-of-Semester Exam: '.$this->weights['end_semester'].'%'],
            [''],
            ['7. CALCULATION EXAMPLE:'],
            ['   Student enters: Assignment 1: 85, Assignment 2: 90, Assignment 3: 80'],
            ['   Assignment Average: (85+90+80)/3 = 85.0'],
            ['   Assignment Weighted: 85.0 × '.$this->weights['assignment'].'% = '.round(85 * $this->weights['assignment'] / 100, 1)],
            [''],
            ['   Mid-Semester: 75'],
            ['   Mid-Sem Weighted: 75 × '.$this->weights['mid_semester'].'% = '.round(75 * $this->weights['mid_semester'] / 100, 1)],
            [''],
            ['   End-Semester: 82'],
            ['   End-Sem Weighted: 82 × '.$this->weights['end_semester'].'% = '.round(82 * $this->weights['end_semester'] / 100, 1)],
            [''],
            ['   TOTAL: '.round(85 * $this->weights['assignment'] / 100 + 75 * $this->weights['mid_semester'] / 100 + 82 * $this->weights['end_semester'] / 100, 1).'%'],
            ['   GRADE: B+'],
            [''],
            ['TROUBLESHOOTING:'],
            [''],
            ['Problem: Cannot edit cells'],
            ['Solution: Make sure you are only editing score columns (D-H). Other columns are locked for protection.'],
            [''],
            ['Problem: Red background appears'],
            ['Solution: Check that your value is between 0 and 100. Remove any text characters.'],
            [''],
            ['Problem: Upload fails'],
            ['Solution: Ensure you have not modified INDEX NO or STUDENT NAME columns. Download a fresh template if needed.'],
            [''],
            ['Problem: Missing students'],
            ['Solution: Download a fresh template from the system to get the latest student list.'],
            [''],
            ['NEED HELP?'],
            ['Contact your system administrator or IT support team.'],
        ]);
    }

    public function title(): string
    {
        return 'Instructions';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '4472C4']]],
            3 => ['font' => ['bold' => true, 'size' => 14]],
            5 => ['font' => ['bold' => true, 'size' => 12]],
            11 => ['font' => ['bold' => true, 'size' => 12]],
            18 => ['font' => ['bold' => true, 'size' => 12]],
            25 => ['font' => ['bold' => true, 'size' => 12]],
            32 => ['font' => ['bold' => true, 'size' => 12]],
            39 => ['font' => ['bold' => true, 'size' => 12]],
            47 => ['font' => ['bold' => true, 'size' => 12]],
            59 => ['font' => ['bold' => true, 'size' => 12]],
            62 => ['font' => ['bold' => true, 'size' => 14]],
        ];
    }
}
