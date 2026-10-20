<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class StudentImportTemplateExport implements FromArray, WithColumnFormatting, WithHeadings, WithTitle
{
    public function array(): array
    {
        return [];
    }

    public function headings(): array
    {
        return [
            'Student ID',
            'First Name',
            'Last Name',
            'Other Name(s)',
            'Gender',
            'Date of Birth',
            'Nationality',
            'Country of Residence',
            'Home Region',
            'Home Town',
            'Religion',
            'Mobile Number',
            'Email',
            'GPS Address',
            'Postal Address',
            'Residential Address',
            'Marital Status',
            'Status',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'L' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function title(): string
    {
        return 'Students';
    }
}
