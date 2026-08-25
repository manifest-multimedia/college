<?php

namespace App\Exports;

use App\Models\Subject;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class CoursesExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithTitle
{
    protected $search;

    public function __construct($search = '')
    {
        $this->search = $search;
    }

    public function collection()
    {
        return Subject::query()
            ->when($this->search, function ($query) {
                return $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('course_code', 'like', '%'.$this->search.'%');
                });
            })
            ->with(['collegeClass', 'year', 'semester'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Course Code',
            'Course Name',
            'Credit Hours',
            'Program',
            'Year',
            'Semester',
            'Description',
        ];
    }

    public function map($subject): array
    {
        return [
            $subject->course_code,
            $subject->name,
            $subject->credit_hours,
            $subject->collegeClass->name ?? '',
            $subject->year->name ?? '',
            $subject->semester->name ?? '',
            $subject->description ?? '',
        ];
    }

    public function title(): string
    {
        return 'Courses';
    }
}
