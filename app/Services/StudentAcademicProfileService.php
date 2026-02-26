<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Cohort;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Year;

class StudentAcademicProfileService
{
    public function getProfile(Student $student): StudentAcademicProfile
    {
        $currentAcademicYear = $this->resolveCurrentAcademicYear();
        $currentSemester = $this->resolveCurrentSemester();

        $startAcademicYear = $this->resolveStartAcademicYearForStudent($student, $currentAcademicYear);

        $yearsElapsed = $this->calculateYearsElapsed($startAcademicYear, $currentAcademicYear);

        $yearOfStudy = $this->resolveYearOfStudy($yearsElapsed);

        $status = $this->determineStatus($student, $yearsElapsed);

        return new StudentAcademicProfile(
            student: $student,
            academicYear: $currentAcademicYear,
            semester: $currentSemester,
            startAcademicYear: $startAcademicYear,
            yearsElapsed: $yearsElapsed,
            yearOfStudy: $yearOfStudy,
            status: $status
        );
    }

    protected function resolveCurrentAcademicYear(): ?AcademicYear
    {
        $current = AcademicYear::getCurrent();

        if ($current) {
            return $current;
        }

        return AcademicYear::orderBy('start_date', 'desc')->first();
    }

    protected function resolveCurrentSemester(): ?Semester
    {
        $current = Semester::current()->first();

        if ($current) {
            return $current;
        }

        return Semester::orderBy('start_date', 'desc')->first();
    }

    protected function resolveStartAcademicYearForStudent(Student $student, ?AcademicYear $fallback): ?AcademicYear
    {
        if ($student->academicYear) {
            return $student->academicYear;
        }

        if ($student->cohort instanceof Cohort) {
            $academicYearName = $student->cohort->academic_year;

            if ($academicYearName) {
                $match = AcademicYear::where('name', $academicYearName)
                    ->orWhere('year', $academicYearName)
                    ->orderBy('start_date')
                    ->first();

                if ($match) {
                    return $match;
                }
            }
        }

        return $fallback;
    }

    protected function calculateYearsElapsed(?AcademicYear $start, ?AcademicYear $current): int
    {
        if (! $start || ! $current) {
            return 1;
        }

        $years = AcademicYear::orderBy('start_date')->get();

        $startIndex = $years->search(fn (AcademicYear $y) => $y->id === $start->id);
        $currentIndex = $years->search(fn (AcademicYear $y) => $y->id === $current->id);

        if ($startIndex === false || $currentIndex === false || $currentIndex < $startIndex) {
            return 1;
        }

        return ($currentIndex - $startIndex) + 1;
    }

    protected function resolveYearOfStudy(int $yearsElapsed): ?Year
    {
        if ($yearsElapsed < 1) {
            $yearsElapsed = 1;
        }

        $byName = Year::where('name', 'like', 'Year '.$yearsElapsed.'%')->first();

        if ($byName) {
            return $byName;
        }

        return Year::orderBy('id')
            ->skip($yearsElapsed - 1)
            ->take(1)
            ->first();
    }

    protected function determineStatus(Student $student, int $yearsElapsed): string
    {
        if (in_array(strtolower($student->status ?? ''), ['completed', 'graduated'])) {
            return 'completed';
        }

        // Use total configured study years as simple program duration proxy
        $configuredYears = Year::count();

        if ($configuredYears > 0 && $yearsElapsed > $configuredYears) {
            return 'completed';
        }

        return 'current';
    }
}

class StudentAcademicProfile
{
    public function __construct(
        public readonly Student $student,
        public readonly ?AcademicYear $academicYear,
        public readonly ?Semester $semester,
        public readonly ?AcademicYear $startAcademicYear,
        public readonly int $yearsElapsed,
        public readonly ?Year $yearOfStudy,
        public readonly string $status
    ) {
    }
}

