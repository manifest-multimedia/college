<?php

use App\Models\Student;

use Illuminate\Support\Str;


if (!function_exists('getAge')) {
    function getAge($date_of_birth)
    {
        $age = date_diff(date_create($date_of_birth), date_create('today'))->y;
        return $age;
    }
}


// GenerateSlugFunction 
if (!function_exists('generateSlug')) {
    function generateSlug($string)
    {
        return Str::slug($string);
    }
}


if (!function_exists('getTitle')) {
    function getTitle($gender = null, $age = null, $marital_status = null)
    {
        $titles = [
            'Mr',
            'Mrs',
            'Ms',
            'Miss',
            'Dr',
            'Prof',
            'Rev',
            'Sr',
            'Jr',
            'Master'
        ];

        // Default title for unknown gender or age
        $title = 'Unknown';

        // Handle gender-specific titles
        if ($gender === 'male') {
            if ($marital_status === 'married') {
                $title = 'Mr';
            } elseif ($age < 18) {
                $title = 'Master';
            } else {
                $title = 'Mr';
            }
        } elseif ($gender === 'female') {
            if ($marital_status === 'married') {
                $title = 'Mrs';
            } elseif ($age < 18) {
                $title = 'Miss';
            } else {
                $title = 'Ms';
            }
        }

        // Handle professional titles (assuming these are independent of gender, age, and marital status)
        if (in_array($marital_status, ['Dr', 'Prof', 'Rev'])) {
            $title = $marital_status;
        }

        // Handle suffixes (Sr., Jr.)
        if (in_array($marital_status, ['Sr', 'Jr'])) {
            $title .= ' ' . $marital_status;
        }

        return $title;
    }
}

// Get First Letter
if (!function_exists('getFirstLetter')) {
    function getFirstLetter($string)
    {
        return substr($string, 0, 1);
    }
}

//Generate Student ID
if (!function_exists('generateStudentID')) {
    /**
     * This function is used to generate student IDS based on the student group
     * The student group returns the list of all students without IDS for a particular class
     * The function sorts the list of students by their names in ascending order
     * The function generates the ids for starts in the sorted order based on the prefix for their class or course.
     * Classes are: RM and RGN where RM= Registered Midwifery and RGN is Registered General Nursing
     * The function updates the student database with the generated IDS
     *
     * @param \App\Models\Student[]|null $student_group The list of all students without IDS for a particular class
     * @param string|null $class The class of the students which the IDS are being generated for
     * @return void
     */
    function generateStudentID($class = null)
    {
        $students = Student::with('collegeClass')->get();

        // Group students by class
        $studentsGroupedByClass = $students->groupBy(function ($student) {
            return $student->collegeClass()->first()->name;
        });

        foreach ($studentsGroupedByClass as $className => $studentsInClass) {
            // Sort each class group by last name
            $sortedStudentsInClass = $studentsInClass->sortBy('last_name');

            $start_number = 1; // Reset numbering for each class

            // Replace 'DM' class prefix with 'RM'
            if ($className == "DM") {
                $className = "RM";
            }

            $prefix = generateStudentIdPrefix($className, '24/25');

            foreach ($sortedStudentsInClass as $student) {
                $numbering = sprintf("%03d", $start_number);
                $student->student_id = $prefix . $numbering;
                $student->save();
                $start_number++;
            }
        }
    }
}

if (!function_exists('generateStudentIdPrefix')) {
    /*************  ✨ Codeium Command ⭐  *************/
    /**
     * Generates the prefix for student ids based on their class and academic year
     * 
     * @param string $class The class of the student (RM or RGN)
     * @param string $academic_year The academic year of the student
     * 
     * @return string The generated prefix for the student id
     */
    /******  8dc1a823-a575-4a27-9322-6e0f24b982d2  *******/

    function generateStudentIdPrefix($class, $academic_year)
    {
        $school_prefix = config('school.prefix');
        return "$school_prefix/$class/$academic_year/";
    }
}

if (!function_exists('getAcademicYear')) {
    function getAcademicYear()
    {

        return date('Y');
    }
}
