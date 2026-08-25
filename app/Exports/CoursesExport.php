<?php

namespace App\Exports;

use App\Models\Subject;
use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class CoursesExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithTitle
{
    protected $search;

    protected $collegeClassId;

    protected $yearId;

    protected $semesterId;

    public function __construct($search = '', $collegeClassId = '', $yearId = '', $semesterId = '')
    {
        $this->search = $search;
        $this->collegeClassId = $collegeClassId;
        $this->yearId = $yearId;
        $this->semesterId = $semesterId;
    }

    public function collection(): Enumerable
    {
        return Subject::query()
            ->when($this->search, function ($query) {
                return $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('course_code', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->collegeClassId, function ($query) {
                return $query->where('college_class_id', $this->collegeClassId);
            })
            ->when($this->yearId, function ($query) {
                return $query->where('year_id', $this->yearId);
            })
            ->when($this->semesterId, function ($query) {
                return $query->where('semester_id', $this->semesterId);
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
