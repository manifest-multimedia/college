<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\CollegeClass;

class CorrectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Dataset Location: public/datasets/students.csv
        $student_id = "PNMTC/DA/RGN/22/23/218";
        $first_name = "Leticia";
        $last_name = "Agyei";
        $other_names = "Abankwa";
        $gender = "female";
        $date_of_birth = "1998-11-01";
        $country_of_residence = "GHANA";
        $marital_status = "single";
        $home_region = "Bono";
        $home_town = "Dormaa Ahenkro";
        $religion = "Christian";
        $mobile_number = "024 123 4567";
        $email = "letabankwa1@gmail.com";
        $postal_address = "POST OFFICE BOX 4";
        $gps_address = "BD-0004-9981";
        $residential_address = "SM 27,DORMAA AHENKRO";
        $class = "Registered General Nursing";

        $student = Student::firstOrCreate([
            'student_id' => ucfirst(strtolower($student_id)),
            'first_name' => ucfirst(strtolower($first_name)),
            'last_name' => ucfirst(strtolower($last_name)),
            'other_name' => ucfirst(strtolower($other_names)),
            'gender' => ucfirst(strtolower($gender)),
            'date_of_birth' => ucfirst(strtolower($date_of_birth)),
            'nationality' => ucfirst(strtolower($country_of_residence)),
            'marital_status' => ucfirst(strtolower($marital_status)),
            'country_of_residence' => ucfirst(strtolower($country_of_residence)),
            'home_region' => ucfirst(strtolower($home_region)),
            'home_town' => ucfirst(strtolower($home_town)),
            'religion' => ucfirst(strtolower($religion)),
            'mobile_number' => $mobile_number,
            'email' => strtolower($email),
            'postal_address' => ucfirst(strtolower($postal_address)),
            'residential_address' => ucfirst(strtolower($residential_address)),
            'gps_address' => $gps_address,
        ]);
        $class = CollegeClass::firstOrCreate([
            'name' => $class,
            'slug' => strtolower($class),
        ]);
        // Associate the student with the class
        $student->collegeClass()->associate($class);
        $student->save();
    }
}
