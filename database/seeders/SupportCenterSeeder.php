<?php

namespace Database\Seeders;

use App\Models\KnowledgeBaseCategory;
use App\Models\SupportCategory;
use Illuminate\Database\Seeder;

class SupportCenterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Support Categories
        $supportCategories = [
            [
                'name' => 'IT Support',
                'slug' => 'it-support',
                'description' => 'Technical support, login issues, system access, and passwords',
                'icon' => 'ki-abstract-26',
                'color' => 'warning',
                'order' => 1,
            ],
            [
                'name' => 'Finance',
                'slug' => 'finance',
                'description' => 'Fee structure, payments, invoices, and billing inquiries',
                'icon' => 'ki-bank',
                'color' => 'primary',
                'order' => 2,
            ],
            [
                'name' => 'Academic Affairs',
                'slug' => 'academic-affairs',
                'description' => 'Course registration, academic calendar, exams and grading',
                'icon' => 'ki-book',
                'color' => 'success',
                'order' => 3,
            ],
            [
                'name' => 'Examination',
                'slug' => 'examination',
                'description' => 'Exam schedules, examination rules, results, and exam clearance',
                'icon' => 'ki-calendar-8',
                'color' => 'danger',
                'order' => 4,
            ],
            [
                'name' => 'Course Registration',
                'slug' => 'course-registration',
                'description' => 'Course selection, registration issues, add/drop courses',
                'icon' => 'ki-notepad',
                'color' => 'info',
                'order' => 5,
            ],
            [
                'name' => 'Other',
                'slug' => 'other',
                'description' => 'General inquiries and other matters',
                'icon' => 'ki-question-2',
                'color' => 'secondary',
                'order' => 6,
            ],
        ];

        foreach ($supportCategories as $category) {
            SupportCategory::create($category);
        }

        // Create Knowledge Base Categories
        $kbCategories = [
            [
                'name' => 'Finance & Billing',
                'slug' => 'finance-billing',
                'description' => 'Fee structure, payments, invoices, and billing inquiries',
                'icon' => 'ki-bank',
                'color' => 'primary',
                'order' => 1,
            ],
            [
                'name' => 'Academic Affairs',
                'slug' => 'academic-affairs',
                'description' => 'Course registration, academic calendar, exams and grading',
                'icon' => 'ki-book',
                'color' => 'success',
                'order' => 2,
            ],
            [
                'name' => 'IT Support',
                'slug' => 'it-support',
                'description' => 'Technical support, login issues, system access, and passwords',
                'icon' => 'ki-abstract-26',
                'color' => 'warning',
                'order' => 3,
            ],
            [
                'name' => 'Examinations',
                'slug' => 'examinations',
                'description' => 'Exam schedules, examination rules, results, and exam clearance',
                'icon' => 'ki-calendar-8',
                'color' => 'danger',
                'order' => 4,
            ],
            [
                'name' => 'Student Services',
                'slug' => 'student-services',
                'description' => 'Student ID, profile updates, accommodations, and resources',
                'icon' => 'ki-office-bag',
                'color' => 'info',
                'order' => 5,
            ],
            [
                'name' => 'General Information',
                'slug' => 'general-information',
                'description' => 'Campus information, policies, procedures, and general FAQ',
                'icon' => 'ki-license',
                'color' => 'dark',
                'order' => 6,
            ],
        ];

        foreach ($kbCategories as $category) {
            KnowledgeBaseCategory::create($category);
        }

        $this->command->info('Support Center and Knowledge Base categories created successfully!');
    }
}

