<?php

namespace App\Exports;

use App\Models\Student;
use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class StudentExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithTitle
{
    protected $search;

    protected $programFilter;

    protected $cohortFilter;

    protected $genderFilter;

    protected $selectedIds;

    public function __construct($search = '', $programFilter = '', $cohortFilter = '', $genderFilter = '', array $selectedIds = [])
    {
        $this->search = $search;
        $this->programFilter = $programFilter;
        $this->cohortFilter = $cohortFilter;
        $this->genderFilter = $genderFilter;
        $this->selectedIds = $selectedIds;
    }

    public function collection(): Enumerable
    {
        return Student::query()
            ->when(!empty($this->selectedIds), function ($query) {
                return $query->whereIn('id', $this->selectedIds);
            })
            ->when(empty($this->selectedIds) && $this->search, function ($query) {
                return $query->where(function ($q) {
                    $q->where('student_id', 'like', '%'.$this->search.'%')
                        ->orWhere('first_name', 'like', '%'.$this->search.'%')
                        ->orWhere('last_name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->when(empty($this->selectedIds) && $this->programFilter, function ($query) {
                return $query->where('college_class_id', $this->programFilter);
            })
            ->when(empty($this->selectedIds) && $this->cohortFilter, function ($query) {
                return $query->where('cohort_id', $this->cohortFilter);
            })
            ->when(empty($this->selectedIds) && $this->genderFilter, function ($query) {
                return $query->whereRaw('LOWER(gender) = ?', [strtolower($this->genderFilter)]);
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
            'Gender',
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
            $student->gender ?? 'N/A',
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
