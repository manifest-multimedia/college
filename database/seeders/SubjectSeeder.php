<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Array of subjects to seed into the database
        $subjects = [
            'Anatomy and Physiology I',
            'Basic Nursing',
            'Microbiology and Infection Prevention/Control',
            'Therapeutic Communication',
            'Professional Adjustment in Nursing/Midwifery',
            'Introductory Sociology',
            'Nursing and Midwifery Infomatics',
            'Anatomy and Physiology of Human Reproductive System and the Feotus 1',
            'Pharmacology, Therapeutics & Pharmacovigilance',
            'Advanced Nursing',
            'Medical Nursing',
            'Surgical Nursing',
            'Physiology and management of Normal Pregnancy',
            'Supply Chain Management',
            'Physiology and Management of Abnormal Pregnancy',
            'Physiology and Management of Abnormal Labour',
            'Family Planning',
            'Family Centered Maternity Care Study',
            'Anatomy & Physiology III',
            'Advanced Nursing II',
            'Medical Nursing I',
            'Surgical Nursing I',
            'Nutrition and Dietetics',
            'Pharmacology, Therapeutics & Pharmacovigilance I',
            'Obstetric Nursing',
            'Management of Abnormal Puerperium',
            'Mental Health',
            'Paediatric Nursing',
            'Medical Nursing III',
            'Surgical Nursing III',
            'Gynaecological Condition',
            'Gerontology and Home Nursing',
            'Management and Administration',
            'Statistics',
            'Physiology and Management of Normal Pregnancy',
            'Anatomy and Physiology of Reproductive System I',
            'Gerontology',
            'First Aid, Emergency and Disaster Management'
        ];

        // Loop through each subject and insert it if it doesn't exist, with a unique course_code
        foreach ($subjects as $index => $subject) {
            // Generate course_code based on index, starting from C0001
            $courseCode = 'C' . str_pad($index + 1, 4, '0', STR_PAD_LEFT); // Ensures 4 digits

            // Insert subject only if the course_code doesn't already exist
            Subject::firstOrCreate(
                ['course_code' => $courseCode],  // Check for duplicates using course_code
                [
                    'name' => $subject,  // The subject name
                    'course_code' => $courseCode  // The generated course code
                ]
            );
        }
    }
}
