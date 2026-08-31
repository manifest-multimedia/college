<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\CollegeClass;
use App\Models\Grade;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentGrade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AcademicsService
{
    /**
     * Get the current academic year
     *
     * @return \App\Models\AcademicYear|null
     */
    public function getCurrentAcademicYear()
    {
        return AcademicYear::where('is_current', true)->first();
    }

    /**
     * Get the current semester
     *
     * @return \App\Models\Semester|null
     */
    public function getCurrentSemester()
    {
        $academicYear = $this->getCurrentAcademicYear();

        return $academicYear
            ? Semester::where('is_current', true)->where('academic_year_id', $academicYear->id)->first()
            : null;
    }

    /**
     * Set current academic year
     *
     * @param  int  $academicYearId
     * @return bool
     */
    public function setCurrentAcademicYear($academicYearId)
    {
        try {
            $academicYear = AcademicYear::findOrFail($academicYearId);

            return $academicYear->setAsCurrent();
        } catch (\Exception $e) {
            Log::error('Error setting current academic year: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Set current semester
     *
     * @param  int  $semesterId
     * @return bool
     */
    public function setCurrentSemester($semesterId)
    {
        try {
            $semester = Semester::findOrFail($semesterId);

            return $semester->setAsCurrent();
        } catch (\Exception $e) {
            Log::error('Error setting current semester: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Set the current academic calendar context atomically. A current semester
     * must always belong to the current Academic Year.
     */
    public function setCurrentAcademicContext(int $academicYearId, int $semesterId): bool
    {
        try {
            return DB::transaction(function () use ($academicYearId, $semesterId): bool {
                $academicYear = AcademicYear::lockForUpdate()->findOrFail($academicYearId);
                $semester = Semester::lockForUpdate()->findOrFail($semesterId);

                if ((int) $semester->academic_year_id !== (int) $academicYear->id) {
                    throw new \InvalidArgumentException('The selected semester does not belong to the selected Academic Year.');
                }

                AcademicYear::where('is_current', true)->update(['is_current' => false]);
                Semester::where('is_current', true)->update(['is_current' => false]);

                $academicYear->update(['is_current' => true]);
                $semester->update(['is_current' => true]);

                return true;
            });
        } catch (\Throwable $e) {
            Log::error('Error setting current academic context: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Assign a grade to a student for a specific class
     *
     * @param  int  $studentId
     * @param  int  $collegeClassId
     * @param  int  $gradeId
     * @param  string|null  $comments
     * @param  int|null  $gradedBy  User ID of the grader
     * @return \App\Models\StudentGrade
     */
    public function assignStudentGrade($studentId, $collegeClassId, $gradeId, $comments = null, $gradedBy = null)
    {
        return DB::transaction(function () use ($studentId, $collegeClassId, $gradeId, $comments, $gradedBy) {
            // Check if a grade already exists for this student and class
            $existingGrade = StudentGrade::where('student_id', $studentId)
                ->where('college_class_id', $collegeClassId)
                ->first();

            if ($existingGrade) {
                // Update existing grade
                $existingGrade->grade_id = $gradeId;
                $existingGrade->comments = $comments;
                if ($gradedBy) {
                    $existingGrade->graded_by = $gradedBy;
                }
                $existingGrade->save();

                return $existingGrade;
            } else {
                // Create new grade
                return StudentGrade::create([
                    'student_id' => $studentId,
                    'college_class_id' => $collegeClassId,
                    'grade_id' => $gradeId,
                    'comments' => $comments,
                    'graded_by' => $gradedBy,
                ]);
            }
        });
    }

    /**
     * Get classes for a specific semester
     *
     * @param  int  $semesterId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getClassesBySemester($semesterId)
    {
        return CollegeClass::where('semester_id', $semesterId)
            ->with(['course', 'instructor'])
            ->get();
    }

    /**
     * Get grades for a specific student in a semester
     *
     * @param  int  $studentId
     * @param  int  $semesterId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getStudentGradesBySemester($studentId, $semesterId)
    {
        return StudentGrade::whereHas('collegeClass', function ($query) use ($semesterId) {
            $query->where('semester_id', $semesterId);
        })
            ->where('student_id', $studentId)
            ->with(['collegeClass.course', 'grade'])
            ->get();
    }
}
