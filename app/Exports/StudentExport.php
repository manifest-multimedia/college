<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class StudentExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    protected $search;
    protected $programFilter;
    protected $cohortFilter;

    public function __construct($search = '', $programFilter = '', $cohortFilter = '')
    {
        $this->search = $search;
        $this->programFilter = $programFilter;
        $this->cohortFilter = $cohortFilter;
    }

    public function collection()
    {
        return Student::query()
            ->when($this->search, function($query) {
                return $query->where(function($q) {
                    $q->where('student_id', 'like', '%' . $this->search . '%')
                      ->orWhere('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->programFilter, function($query) {
                return $query->where('college_class_id', $this->programFilter);
            })
            ->when($this->cohortFilter, function($query) {
                return $query->where('cohort_id', $this->cohortFilter);
            })
            ->with(['collegeClass', 'cohort'])
            ->get();
    }

    public function headings(): array
    {
        return [
            'Student ID',
            'Last Name',
            'First Name',
            'Other Name',
            'Email',
            'Program',
            'Cohort',
            'Status',
        ];
    }

    public function map($student): array
    {
        return [
            $student->student_id,
            $student->last_name,
            $student->first_name,
            $student->other_name ?? '',
            $student->email,
            $student->collegeClass->name ?? 'N/A',
            $student->cohort->name ?? 'N/A',
            $student->status ?? 'Unknown',
        ];
    }

    public function title(): string
    {
        return 'Students';
    }
}