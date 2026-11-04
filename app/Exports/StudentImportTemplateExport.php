<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

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
            'Emergency Contact Full Name',
            'Emergency Contact Relationship',
            'Emergency Contact Mobile Number',
            'Emergency Contact Alternate Phone',
            'Emergency Contact Email',
            'Emergency Contact GPS Address',
            'Emergency Contact Residential Address',
            'Emergency Contact 2 Full Name',
            'Emergency Contact 2 Relationship',
            'Emergency Contact 2 Mobile Number',
            'Emergency Contact 2 Alternate Phone',
            'Emergency Contact 2 Email',
            'Emergency Contact 2 GPS Address',
            'Emergency Contact 2 Residential Address',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'L' => NumberFormat::FORMAT_TEXT,
            'U' => NumberFormat::FORMAT_TEXT,
            'V' => NumberFormat::FORMAT_TEXT,
            'AB' => NumberFormat::FORMAT_TEXT,
            'AC' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function title(): string
    {
        return 'Students';
    }
}
