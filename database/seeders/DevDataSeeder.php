<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\AssessmentScore;
use App\Models\Cohort;
use App\Models\CollegeClass;
use App\Models\CourseLecturer;
use App\Models\CourseRegistration;
use App\Models\Department;
use App\Models\Exam;
use App\Models\ExamClearance;
use App\Models\ExamEntryTicket;
use App\Models\ExamType;
use App\Models\FeePayment;
use App\Models\FeeStructure;
use App\Models\FeeType;
use App\Models\Grade;
use App\Models\Office;
use App\Models\OfflineExam;
use App\Models\OfflineExamScore;
use App\Models\Option;
use App\Models\Question;
use App\Models\QuestionSet;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentFeeBill;
use App\Models\StudentFeeBillItem;
use App\Models\StudentGrade;
use App\Models\Subject;
use App\Models\User;
use App\Models\Year;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * Development-only seeder with realistic sample data for the college system.
 *
 * Usage: php artisan db:seed --class=DevDataSeeder
 */
class DevDataSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'development', 'testing')) {
            $this->command->error('DevDataSeeder can only run in local/development/testing environments!');

            return;
        }

        $this->command->info('🌱 Starting DevDataSeeder...');

        DB::transaction(function () {
            $this->seedTier1();
            $this->seedTier2();
            $this->seedTier3();
            $this->seedTier4();
            $this->seedTier5();
            $this->seedTier6();
        });

        $this->command->info('✅ DevDataSeeder completed successfully!');
    }

    // ─── Ghanaian name helpers ───────────────────────────────────────

    private function ghanaianFirstNames(string $gender): array
    {
        return $gender === 'Male'
            ? ['Kwame', 'Kofi', 'Yaw', 'Kwaku', 'Kwesi', 'Kojo', 'Kwadwo', 'Nana', 'Ebo', 'Fiifi', 'Akwasi', 'Mensah', 'Osei', 'Bright', 'Emmanuel', 'Samuel', 'Daniel', 'Michael', 'Isaac', 'Joseph']
            : ['Ama', 'Abena', 'Akua', 'Yaa', 'Afua', 'Adwoa', 'Efua', 'Esi', 'Akosua', 'Adjoa', 'Gifty', 'Mercy', 'Grace', 'Priscilla', 'Florence', 'Comfort', 'Vida', 'Beatrice', 'Doris', 'Patience'];
    }

    private array $ghanaianLastNames = [
        'Mensah', 'Asante', 'Boateng', 'Owusu', 'Adjei', 'Amankwah', 'Bonsu', 'Darko',
        'Frimpong', 'Gyamfi', 'Agyeman', 'Amoako', 'Appiah', 'Baah', 'Danso', 'Essien',
        'Fosu', 'Gyasi', 'Kusi', 'Manu', 'Nyarko', 'Ofori', 'Okyere', 'Sarpong',
        'Tetteh', 'Yeboah', 'Ansah', 'Badu', 'Donkor', 'Twumasi',
    ];

    private array $ghanaianRegions = [
        'Greater Accra', 'Ashanti', 'Central', 'Eastern', 'Western',
        'Northern', 'Volta', 'Bono', 'Upper East', 'Upper West',
    ];

    private array $ghanaianTowns = [
        'Accra', 'Kumasi', 'Cape Coast', 'Tamale', 'Koforidua', 'Sunyani',
        'Ho', 'Bolgatanga', 'Wa', 'Takoradi', 'Tema', 'Obuasi',
        'Techiman', 'Nkawkaw', 'Winneba', 'Hohoe',
    ];

    // ─── TIER 1 ─────────────────────────────────────────────────────

    private array $academicYears = [];

    private array $departments = [];

    private array $feeTypes = [];

    private array $examTypes = [];

    private array $collegeClasses = [];

    private array $cohorts = [];

    private array $grades = [];

    private function seedTier1(): void
    {
        $this->command->info('  → Tier 1: Academic Years, Departments, Fee Types, Exam Types, Classes, Cohorts, Grades');

        // Academic Years
        $yearsData = [
            ['name' => '2024/2025', 'year' => 2024, 'start' => '2024-09-01', 'end' => '2025-07-31', 'current' => false],
            ['name' => '2025/2026', 'year' => 2025, 'start' => '2025-09-01', 'end' => '2026-07-31', 'current' => true],
        ];
        foreach ($yearsData as $y) {
            $this->academicYears[] = AcademicYear::firstOrCreate(
                ['name' => $y['name']],
                ['slug' => Str::slug($y['name']), 'start_date' => $y['start'], 'end_date' => $y['end'], 'year' => $y['year'], 'is_current' => $y['current']]
            );
        }

        // Departments
        $depts = [
            ['name' => 'Nursing Department', 'code' => 'NUR'],
            ['name' => 'Midwifery Department', 'code' => 'MID'],
            ['name' => 'Community Health Department', 'code' => 'CHD'],
            ['name' => 'General Studies', 'code' => 'GEN'],
            ['name' => 'Administration', 'code' => 'ADM'],
        ];
        foreach ($depts as $d) {
            $this->departments[] = Department::firstOrCreate(
                ['code' => $d['code']],
                ['name' => $d['name'], 'description' => $d['name'].' of the college', 'is_active' => true]
            );
        }

        // Fee Types
        $fts = [
            ['name' => 'Tuition Fee', 'code' => 'TUI'],
            ['name' => 'Library Fee', 'code' => 'LIB'],
            ['name' => 'Examination Fee', 'code' => 'EXM'],
            ['name' => 'ICT Fee', 'code' => 'ICT'],
            ['name' => 'SRC Dues', 'code' => 'SRC'],
            ['name' => 'Clinical Fee', 'code' => 'CLN'],
            ['name' => 'Hostel Fee', 'code' => 'HST'],
        ];
        foreach ($fts as $ft) {
            $this->feeTypes[] = FeeType::firstOrCreate(
                ['code' => $ft['code']],
                ['name' => $ft['name'], 'description' => $ft['name'], 'is_active' => true]
            );
        }

        // Exam Types
        $ets = [
            ['name' => 'Mid-Semester Examination', 'code' => 'MID', 'threshold' => 60.00],
            ['name' => 'End of Semester Examination', 'code' => 'END', 'threshold' => 100.00],
            ['name' => 'Supplementary Examination', 'code' => 'SUP', 'threshold' => 80.00],
        ];
        foreach ($ets as $et) {
            $this->examTypes[] = ExamType::firstOrCreate(
                ['code' => $et['code']],
                ['name' => $et['name'], 'payment_threshold' => $et['threshold'], 'description' => $et['name'], 'is_active' => true]
            );
        }

        // College Classes (Programs)
        $programs = [
            ['name' => 'Registered General Nursing', 'short_name' => 'RGN', 'max' => 120],
            ['name' => 'Registered Midwifery', 'short_name' => 'RM', 'max' => 80],
            ['name' => 'Community Health Nursing', 'short_name' => 'CHN', 'max' => 100],
            ['name' => 'Health Assistant Clinical', 'short_name' => 'HAC', 'max' => 60],
            ['name' => 'Registered Mental Health Nursing', 'short_name' => 'RMN', 'max' => 50],
        ];
        foreach ($programs as $p) {
            $this->collegeClasses[] = CollegeClass::firstOrCreate(
                ['short_name' => $p['short_name']],
                [
                    'name' => $p['name'], 'description' => $p['name'].' Programme',
                    'slug' => Str::slug($p['name']),
                    'is_active' => true, 'is_deleted' => false,
                ]
            );
        }

        // Cohorts
        foreach (['2024 Intake', '2025 Intake'] as $i => $cName) {
            $ay = $this->academicYears[$i];
            $this->cohorts[] = Cohort::firstOrCreate(
                ['slug' => Str::slug($cName)],
                [
                    'name' => $cName, 'description' => $cName.' cohort',
                    'academic_year' => $ay->name,
                    'start_date' => $ay->start_date, 'end_date' => $ay->end_date,
                    'is_active' => $i === 1, 'is_deleted' => false,
                ]
            );
        }

        // Grades
        $gradesData = [
            ['name' => 'A+', 'value' => 4.0, 'type' => 'letter'],
            ['name' => 'A',  'value' => 4.0, 'type' => 'letter'],
            ['name' => 'B+', 'value' => 3.5, 'type' => 'letter'],
            ['name' => 'B',  'value' => 3.0, 'type' => 'letter'],
            ['name' => 'C+', 'value' => 2.5, 'type' => 'letter'],
            ['name' => 'C',  'value' => 2.0, 'type' => 'letter'],
            ['name' => 'D',  'value' => 1.0, 'type' => 'letter'],
            ['name' => 'F',  'value' => 0.0, 'type' => 'letter'],
        ];
        foreach ($gradesData as $g) {
            $this->grades[] = Grade::firstOrCreate(
                ['name' => $g['name']],
                [
                    'type' => $g['type'], 'value' => $g['value'],
                    'description' => 'Grade '.$g['name'], 'slug' => Str::slug('grade-'.$g['name']),
                    'is_deleted' => false,
                ]
            );
        }
    }

    // ─── TIER 2 ─────────────────────────────────────────────────────

    private array $semesters = [];

    private array $years = [];

    private array $adminUsers = [];

    private array $lecturerUsers = [];

    private array $offices = [];

    private function seedTier2(): void
    {
        $this->command->info('  → Tier 2: Semesters, Years, Users, Offices');

        $currentAY = $this->academicYears[1]; // 2025/2026

        // Semesters
        $this->semesters[] = Semester::firstOrCreate(
            ['slug' => 'semester-1-'.$currentAY->id],
            ['name' => 'Semester 1', 'academic_year_id' => $currentAY->id, 'start_date' => '2025-09-01', 'end_date' => '2026-01-31', 'is_current' => true, 'description' => 'First Semester']
        );
        $this->semesters[] = Semester::firstOrCreate(
            ['slug' => 'semester-2-'.$currentAY->id],
            ['name' => 'Semester 2', 'academic_year_id' => $currentAY->id, 'start_date' => '2026-02-01', 'end_date' => '2026-07-31', 'is_current' => false, 'description' => 'Second Semester']
        );

        // Years (academic levels)
        $yearNames = ['Year 1', 'Year 2', 'Year 3'];
        foreach ($yearNames as $i => $yn) {
            $this->years[] = Year::firstOrCreate(
                ['slug' => Str::slug($yn)],
                ['name' => $yn]
            );
        }

        // Roles (firstOrCreate to avoid conflicts)
        $adminRole = Role::firstOrCreate(['name' => 'Admin'], ['guard_name' => 'web']);
        $lecturerRole = Role::firstOrCreate(['name' => 'Lecturer'], ['guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Student'], ['guard_name' => 'web']);

        // Admin users
        $admins = [
            ['name' => 'Dr. Kwame Asante', 'email' => 'admin@college.test'],
            ['name' => 'Mrs. Abena Mensah', 'email' => 'admin2@college.test'],
        ];
        foreach ($admins as $a) {
            $user = User::firstOrCreate(
                ['email' => $a['email']],
                ['name' => $a['name'], 'password' => Hash::make('password'), 'role' => 'admin']
            );
            if (! $user->hasRole('Admin')) {
                $user->assignRole($adminRole);
            }
            $this->adminUsers[] = $user;
        }

        // Lecturer users
        $lecturers = [
            'Dr. Osei Bonsu', 'Mrs. Akua Frimpong', 'Mr. Yaw Darko', 'Dr. Efua Sarpong',
            'Mr. Kofi Adjei', 'Mrs. Ama Gyamfi', 'Dr. Kwesi Tetteh', 'Mr. Nana Boateng',
        ];
        foreach ($lecturers as $i => $name) {
            $user = User::firstOrCreate(
                ['email' => 'lecturer'.($i + 1).'@college.test'],
                ['name' => $name, 'password' => Hash::make('password'), 'role' => 'lecturer']
            );
            if (! $user->hasRole('Lecturer')) {
                $user->assignRole($lecturerRole);
            }
            $this->lecturerUsers[] = $user;
        }

        // Offices
        foreach ($this->departments as $dept) {
            $this->offices[] = Office::firstOrCreate(
                ['name' => $dept->name.' Office'],
                ['department_id' => $dept->id, 'code' => $dept->code.'-OFF', 'is_active' => true, 'description' => 'Main office for '.$dept->name]
            );
        }

        // Department-User pivot
        if (Schema::hasTable('department_user')) {
            foreach ($this->lecturerUsers as $i => $lec) {
                $deptId = $this->departments[$i % count($this->departments)]->id;
                DB::table('department_user')->insertOrIgnore([
                    'department_id' => $deptId,
                    'user_id' => $lec->id,
                    'is_head' => $i < count($this->departments), // first N lecturers are heads
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }
    }

    // ─── TIER 3 ─────────────────────────────────────────────────────

    private array $subjects = [];

    private array $feeStructures = [];

    private array $students = [];

    private function seedTier3(): void
    {
        $this->command->info('  → Tier 3: Subjects, Fee Structures, Students, Course Lecturers');

        $semester = $this->semesters[0]; // Semester 1
        $year = $this->years[0]; // Year 1

        // Subjects
        $subjectsData = [
            ['code' => 'NUR101', 'name' => 'Fundamentals of Nursing', 'class' => 0, 'credits' => 3],
            ['code' => 'NUR102', 'name' => 'Human Anatomy & Physiology I', 'class' => 0, 'credits' => 4],
            ['code' => 'NUR103', 'name' => 'Pharmacology I', 'class' => 0, 'credits' => 3],
            ['code' => 'MID101', 'name' => 'Introduction to Midwifery', 'class' => 1, 'credits' => 3],
            ['code' => 'MID102', 'name' => 'Reproductive Health', 'class' => 1, 'credits' => 3],
            ['code' => 'CHN101', 'name' => 'Community Health Principles', 'class' => 2, 'credits' => 3],
            ['code' => 'CHN102', 'name' => 'Public Health Nursing', 'class' => 2, 'credits' => 3],
            ['code' => 'GEN101', 'name' => 'English Communication Skills', 'class' => 0, 'credits' => 2],
            ['code' => 'GEN102', 'name' => 'Introduction to ICT', 'class' => 0, 'credits' => 2],
            ['code' => 'HAC101', 'name' => 'Health Assessment', 'class' => 3, 'credits' => 3],
            ['code' => 'RMN101', 'name' => 'Introduction to Mental Health', 'class' => 4, 'credits' => 3],
            ['code' => 'NUR104', 'name' => 'Medical-Surgical Nursing I', 'class' => 0, 'credits' => 4],
        ];
        foreach ($subjectsData as $s) {
            $this->subjects[] = Subject::firstOrCreate(
                ['course_code' => $s['code']],
                [
                    'name' => $s['name'],
                    'semester_id' => $semester->id,
                    'year_id' => $year->id,
                    'college_class_id' => $this->collegeClasses[$s['class']]->id,
                    'credit_hours' => $s['credits'],
                ]
            );
        }

        // Fee Structures
        $currentAY = $this->academicYears[1];
        $amounts = [2500.00, 150.00, 200.00, 100.00, 50.00, 350.00, 800.00]; // maps to feeTypes
        foreach ($this->collegeClasses as $cc) {
            foreach ($this->feeTypes as $i => $ft) {
                $this->feeStructures[] = FeeStructure::firstOrCreate(
                    ['fee_type_id' => $ft->id, 'college_class_id' => $cc->id, 'academic_year_id' => $currentAY->id, 'semester_id' => $semester->id],
                    [
                        'amount' => $amounts[$i] ?? 100.00,
                        'is_mandatory' => $i < 5, // first 5 mandatory
                        'is_active' => true,
                        'applicable_gender' => 'all',
                    ]
                );
            }
        }

        // Students
        $studentRole = Role::firstOrCreate(['name' => 'Student'], ['guard_name' => 'web']);
        $religions = ['Christianity', 'Islam', 'Traditional', 'Christianity', 'Christianity'];
        $maritalStatuses = ['Single', 'Single', 'Single', 'Married', 'Single'];

        for ($i = 0; $i < 40; $i++) {
            $gender = $i % 3 === 0 ? 'Male' : 'Female'; // ~67% female for nursing college
            $firstNames = $this->ghanaianFirstNames($gender);
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $this->ghanaianLastNames[array_rand($this->ghanaianLastNames)];
            $otherName = fake()->optional(0.4)->randomElement($this->ghanaianLastNames);
            $studentId = 'NTC/'.str_pad($i + 1, 4, '0', STR_PAD_LEFT).'/'.date('Y');
            $email = strtolower($firstName.'.'.$lastName.($i + 1)).'@student.college.test';
            $classIdx = $i % count($this->collegeClasses);
            $cohortIdx = $i < 20 ? 0 : 1;

            $user = User::firstOrCreate(
                ['email' => $email],
                ['name' => $firstName.' '.$lastName, 'password' => Hash::make('password'), 'role' => 'student']
            );
            if (! $user->hasRole('Student')) {
                $user->assignRole($studentRole);
            }

            $this->students[] = Student::firstOrCreate(
                ['student_id' => $studentId],
                [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'other_name' => $otherName,
                    'gender' => $gender,
                    'date_of_birth' => fake()->dateTimeBetween('-28 years', '-18 years')->format('Y-m-d'),
                    'nationality' => 'Ghanaian',
                    'country_of_residence' => 'Ghana',
                    'home_region' => $this->ghanaianRegions[array_rand($this->ghanaianRegions)],
                    'home_town' => $this->ghanaianTowns[array_rand($this->ghanaianTowns)],
                    'religion' => $religions[array_rand($religions)],
                    'mobile_number' => '02'.fake()->numerify('########'),
                    'email' => $email,
                    'gps_address' => 'GA-'.fake()->numerify('###-####'),
                    'postal_address' => 'P.O. Box '.fake()->numerify('###').', '.$this->ghanaianTowns[array_rand($this->ghanaianTowns)],
                    'residential_address' => fake()->numerify('##').' '.fake()->randomElement(['Main Street', 'Hospital Road', 'College Road', 'Market Street']),
                    'marital_status' => $maritalStatuses[array_rand($maritalStatuses)],
                    'college_class_id' => $this->collegeClasses[$classIdx]->id,
                    'cohort_id' => $this->cohorts[$cohortIdx]->id,
                    'academic_year_id' => $this->academicYears[1]->id,
                    'status' => 'active',
                    'user_id' => $user->id,
                ]
            );
        }

        // Course Lecturers
        foreach ($this->subjects as $i => $subj) {
            $lecIdx = $i % count($this->lecturerUsers);
            CourseLecturer::firstOrCreate(
                ['user_id' => $this->lecturerUsers[$lecIdx]->id, 'subject_id' => $subj->id]
            );
        }
    }

    // ─── TIER 4 ─────────────────────────────────────────────────────

    private array $bills = [];

    private array $registrations = [];

    private array $questionSets = [];

    private function seedTier4(): void
    {
        $this->command->info('  → Tier 4: Fee Bills, Course Registrations, Question Sets');

        $currentAY = $this->academicYears[1];
        $semester = $this->semesters[0];

        // Student Fee Bills
        foreach ($this->students as $student) {
            // Calculate total from fee structures for this student's class
            $classStructures = collect($this->feeStructures)->filter(fn ($fs) => $fs->college_class_id === $student->college_class_id && $fs->semester_id === $semester->id);
            $total = $classStructures->sum('amount');

            $this->bills[] = StudentFeeBill::firstOrCreate(
                ['student_id' => $student->id, 'academic_year_id' => $currentAY->id, 'semester_id' => $semester->id],
                [
                    'total_amount' => $total,
                    'amount_paid' => 0,
                    'balance' => $total,
                    'payment_percentage' => 0,
                    'status' => 'pending',
                    'billing_date' => '2025-09-15',
                    'bill_reference' => 'BILL-'.$student->student_id.'-'.$semester->id,
                ]
            );
        }

        // Course Registrations
        foreach ($this->students as $si => $student) {
            // Register each student in 3-5 subjects relevant to their class
            $classSubjects = collect($this->subjects)->filter(fn ($s) => $s->college_class_id === $student->college_class_id)->values();
            // Also add general subjects
            $genSubjects = collect($this->subjects)->filter(fn ($s) => in_array($s->course_code, ['GEN101', 'GEN102']))->values();
            $toRegister = $classSubjects->merge($genSubjects)->take(5);

            foreach ($toRegister as $subj) {
                $payPct = $si < 10 ? 100.0 : ($si < 25 ? fake()->randomFloat(1, 60, 80) : fake()->randomFloat(1, 20, 50));
                $isApproved = $payPct >= 60;
                $this->registrations[] = CourseRegistration::firstOrCreate(
                    ['student_id' => $student->id, 'subject_id' => $subj->id, 'academic_year_id' => $currentAY->id, 'semester_id' => $semester->id],
                    [
                        'registered_at' => fake()->dateTimeBetween('2025-09-01', '2025-10-15'),
                        'payment_percentage_at_registration' => $payPct,
                        'is_approved' => $isApproved,
                        'approved_at' => $isApproved ? fake()->dateTimeBetween('2025-10-01', '2025-10-20') : null,
                        'approved_by' => $isApproved ? $this->adminUsers[0]->id : null,
                    ]
                );
            }
        }

        // Question Sets
        foreach ($this->subjects as $i => $subj) {
            if ($i >= 6) {
                break;
            } // 6 question sets
            $lecIdx = $i % count($this->lecturerUsers);
            $this->questionSets[] = QuestionSet::firstOrCreate(
                ['name' => $subj->name.' - Question Bank'],
                [
                    'description' => 'Question bank for '.$subj->name,
                    'course_id' => $subj->id,
                    'difficulty_level' => fake()->randomElement(['easy', 'medium', 'hard']),
                    'created_by' => $this->lecturerUsers[$lecIdx]->id,
                ]
            );
        }
    }

    // ─── TIER 5 ─────────────────────────────────────────────────────

    private array $exams = [];

    private array $offlineExams = [];

    private function seedTier5(): void
    {
        $this->command->info('  → Tier 5: Bill Items, Payments, Questions, Exams, Clearances, Grades');

        $semester = $this->semesters[0];

        // Student Fee Bill Items
        foreach ($this->bills as $bill) {
            $student = collect($this->students)->first(fn ($s) => $s->id === $bill->student_id);
            $structures = collect($this->feeStructures)->filter(fn ($fs) => $fs->college_class_id === $student->college_class_id && $fs->semester_id === $semester->id);
            foreach ($structures as $fs) {
                StudentFeeBillItem::firstOrCreate(
                    ['student_fee_bill_id' => $bill->id, 'fee_type_id' => $fs->fee_type_id, 'fee_structure_id' => $fs->id],
                    ['amount' => $fs->amount]
                );
            }
        }

        // Fee Payments - vary by student index
        foreach ($this->bills as $bi => $bill) {
            if ($bi < 10) {
                // Fully paid
                $payAmt = $bill->total_amount;
            } elseif ($bi < 25) {
                // Partial (60-80%)
                $payAmt = round($bill->total_amount * fake()->randomFloat(2, 0.60, 0.80), 2);
            } else {
                // Unpaid — skip payment
                continue;
            }

            FeePayment::firstOrCreate(
                ['student_fee_bill_id' => $bill->id, 'reference_number' => 'PAY-'.$bill->id.'-001'],
                [
                    'student_id' => $bill->student_id,
                    'amount' => $payAmt,
                    'payment_method' => fake()->randomElement(['cash', 'bank_transfer', 'mobile_money']),
                    'receipt_number' => 'RCT-'.str_pad($bill->id, 6, '0', STR_PAD_LEFT),
                    'note' => $bi < 10 ? 'Full payment' : 'Partial payment',
                    'recorded_by' => $this->adminUsers[0]->id,
                    'payment_date' => fake()->dateTimeBetween('2025-09-15', '2025-10-30'),
                ]
            );

            // Recalculate bill status
            try {
                $bill->refresh();
                $bill->recalculatePaymentStatus();
            } catch (\Throwable $e) {
                // Method might not exist yet or might fail — update manually
                $bill->update([
                    'amount_paid' => $payAmt,
                    'balance' => $bill->total_amount - $payAmt,
                    'payment_percentage' => round(($payAmt / $bill->total_amount) * 100, 2),
                    'status' => $payAmt >= $bill->total_amount ? 'paid' : 'partially_paid',
                ]);
            }
        }

        // Questions & Options — realistic nursing/health questions
        $difficulties = ['easy', 'medium', 'hard'];
        $nursingQuestions = [
            'NUR' => [
                'What is the normal range for adult blood pressure?',
                'Which vital sign should be assessed first in a critically ill patient?',
                'What is the primary purpose of hand hygiene in nursing?',
                'Which position is most appropriate for a patient with dyspnea?',
                'What is the normal adult body temperature range?',
                'Which assessment technique is used first during physical examination?',
                'What is the correct sequence for donning PPE?',
                'Which nursing intervention is appropriate for a febrile patient?',
                'What is the normal adult respiratory rate per minute?',
                'Which documentation method uses the SOAP format?',
            ],
            'MID' => [
                'What is the normal duration of human pregnancy in weeks?',
                'Which hormone is primarily responsible for maintaining pregnancy?',
                'What is the Apgar score assessment performed at?',
                'Which stage of labour involves delivery of the placenta?',
                'What is the recommended frequency of antenatal visits for a normal pregnancy?',
                'Which complication is characterized by hypertension and proteinuria in pregnancy?',
                'What is the normal fetal heart rate range?',
                'Which method is used to determine the expected date of delivery?',
                'What is the primary purpose of the partograph?',
                'Which vitamin supplementation is essential in early pregnancy?',
            ],
            'CHN' => [
                'What is the primary focus of community health nursing?',
                'Which level of disease prevention includes immunization?',
                'What is the purpose of epidemiological surveillance?',
                'Which model is used for health promotion in communities?',
                'What is the role of a community health nurse in disease outbreak?',
                'Which indicator is used to measure community health status?',
                'What is the purpose of a community health assessment?',
                'Which strategy is most effective for health education in communities?',
                'What is the recommended childhood immunization schedule for BCG?',
                'Which organization sets global health standards?',
            ],
            'GEN' => [
                'Which communication skill is essential for patient interviews?',
                'What is the primary purpose of professional communication in healthcare?',
                'Which component is NOT part of the communication process?',
                'What is the correct format for academic referencing?',
                'Which software application is commonly used for data analysis?',
                'What is the purpose of spreadsheet formulas in healthcare data management?',
                'Which internet protocol is used for secure data transmission?',
                'What is the primary function of a database management system?',
                'Which file format is most suitable for medical imaging?',
                'What is the importance of digital literacy in modern nursing?',
            ],
        ];

        foreach ($this->questionSets as $qs) {
            // Determine question category based on course code
            $subj = Subject::find($qs->course_id);
            $prefix = $subj ? substr($subj->course_code, 0, 3) : 'NUR';
            $questionPool = $nursingQuestions[$prefix] ?? $nursingQuestions['NUR'];

            for ($q = 0; $q < 10; $q++) {
                $qText = $questionPool[$q] ?? 'What is the correct answer for concept '.($q + 1)." in {$qs->name}?";
                $correctIdx = fake()->numberBetween(0, 3);
                $optionLabels = ['A', 'B', 'C', 'D'];

                $question = Question::firstOrCreate(
                    ['question_set_id' => $qs->id, 'question_text' => $qText],
                    [
                        'mark' => fake()->numberBetween(1, 5),
                        'explanation' => 'The correct answer demonstrates understanding of key concepts in '.($subj->name ?? $qs->name).'.',
                        'type' => 'MCQ',
                        'difficulty_level' => $difficulties[array_rand($difficulties)],
                    ]
                );

                foreach ($optionLabels as $oi => $opt) {
                    $optText = $oi === $correctIdx
                        ? "Correct answer for: {$qText}"
                        : "Distractor {$opt} for Q".($q + 1);
                    Option::firstOrCreate(
                        ['question_id' => $question->id, 'option_text' => $optText],
                        ['is_correct' => $oi === $correctIdx]
                    );
                }
            }
        }

        // Online Exams
        $midExamType = $this->examTypes[0]; // Mid-Semester
        $endExamType = $this->examTypes[1]; // End of Semester
        foreach ($this->subjects as $i => $subj) {
            if ($i >= 6) {
                break;
            }
            $lecIdx = $i % count($this->lecturerUsers);
            $this->exams[] = Exam::firstOrCreate(
                ['slug' => Str::slug($subj->name.'-mid-sem-exam')],
                [
                    'course_id' => $subj->id,
                    'user_id' => $this->lecturerUsers[$lecIdx]->id,
                    'type' => 'mid_semester',
                    'type_id' => $midExamType->id,
                    'duration' => 60,
                    'questions_per_session' => 10,
                    'passing_percentage' => 50,
                    'status' => 'upcoming',
                    'start_date' => '2025-11-15',
                    'end_date' => '2025-11-20',
                ]
            );
        }

        // Link Exams to Question Sets via pivot
        if (Schema::hasTable('exam_question_set')) {
            foreach ($this->exams as $i => $exam) {
                if (isset($this->questionSets[$i])) {
                    DB::table('exam_question_set')->insertOrIgnore([
                        'exam_id' => $exam->id,
                        'question_set_id' => $this->questionSets[$i]->id,
                        'shuffle_questions' => true,
                        'questions_to_pick' => 10,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            $this->command->info('    ✓ Linked '.count($this->exams).' exams to question sets');
        }

        // Offline Exams
        if (Schema::hasTable('offline_exams')) {
            foreach ($this->subjects as $i => $subj) {
                if ($i >= 4) {
                    break;
                }
                $lecIdx = $i % count($this->lecturerUsers);
                $this->offlineExams[] = OfflineExam::firstOrCreate(
                    ['title' => $subj->name.' End of Semester Exam'],
                    [
                        'description' => 'End of semester examination for '.$subj->name,
                        'date' => '2026-01-'.str_pad(15 + $i, 2, '0', STR_PAD_LEFT),
                        'duration' => 120,
                        'status' => 'published',
                        'course_id' => $subj->id,
                        'user_id' => $this->lecturerUsers[$lecIdx]->id,
                        'venue' => fake()->randomElement(['Main Hall', 'Lecture Theatre A', 'Exam Hall B', 'Skills Lab']),
                        'clearance_threshold' => 100,
                        'passing_percentage' => 50,
                    ]
                );
            }
        }

        // Exam Clearances
        $currentAY = $this->academicYears[1];
        foreach ($this->students as $si => $student) {
            $bill = collect($this->bills)->first(fn ($b) => $b->student_id === $student->id);
            if (! $bill) {
                continue;
            }
            $bill->refresh();

            // Mid-semester clearance (threshold 60%)
            $isClearedMid = ($bill->payment_percentage ?? 0) >= $midExamType->payment_threshold;
            if ($si < 30 && count($this->exams) > 0) {
                $exam = $this->exams[$si % count($this->exams)];
                ExamClearance::firstOrCreate(
                    ['student_id' => $student->id, 'semester_id' => $semester->id, 'clearable_type' => Exam::class, 'clearable_id' => $exam->id],
                    [
                        'academic_year_id' => $currentAY->id,
                        'exam_type_id' => $midExamType->id,
                        'is_cleared' => $isClearedMid,
                        'status' => $isClearedMid ? 'Cleared' : 'Pending',
                        'cleared_by' => $isClearedMid ? $this->adminUsers[0]->id : null,
                        'cleared_at' => $isClearedMid ? now() : null,
                        'clearance_code' => strtoupper(Str::random(8)),
                    ]
                );
            }

            // Offline exam clearance (threshold 100%)
            if ($si < 20 && count($this->offlineExams) > 0) {
                $oExam = $this->offlineExams[$si % count($this->offlineExams)];
                $isClearedEnd = ($bill->payment_percentage ?? 0) >= $endExamType->payment_threshold;
                ExamClearance::firstOrCreate(
                    ['student_id' => $student->id, 'semester_id' => $semester->id, 'clearable_type' => OfflineExam::class, 'clearable_id' => $oExam->id],
                    [
                        'academic_year_id' => $currentAY->id,
                        'exam_type_id' => $endExamType->id,
                        'is_cleared' => $isClearedEnd,
                        'status' => $isClearedEnd ? 'Cleared' : 'Pending',
                        'cleared_by' => $isClearedEnd ? $this->adminUsers[0]->id : null,
                        'cleared_at' => $isClearedEnd ? now() : null,
                        'clearance_code' => strtoupper(Str::random(8)),
                    ]
                );
            }
        }

        // Student Grades
        foreach ($this->students as $si => $student) {
            if ($si >= 20) {
                break;
            }
            $gradeIdx = $si % count($this->grades);
            StudentGrade::firstOrCreate(
                ['student_id' => $student->id, 'college_class_id' => $student->college_class_id],
                [
                    'grade_id' => $this->grades[$gradeIdx]->id,
                    'comments' => fake()->randomElement(['Excellent performance', 'Good work', 'Satisfactory', 'Needs improvement', 'Well done']),
                    'graded_by' => $this->lecturerUsers[$si % count($this->lecturerUsers)]->id,
                ]
            );
        }
    }

    // ─── TIER 6 ─────────────────────────────────────────────────────

    private function seedTier6(): void
    {
        $this->command->info('  → Tier 6: Entry Tickets, Offline Scores, Assessment Scores');

        $midExamType = $this->examTypes[0];
        $endExamType = $this->examTypes[1];

        // Exam Entry Tickets — from cleared exam clearances
        $clearances = ExamClearance::where('is_cleared', true)->get();
        foreach ($clearances as $cl) {
            ExamEntryTicket::firstOrCreate(
                ['exam_clearance_id' => $cl->id, 'student_id' => $cl->student_id],
                [
                    'exam_type_id' => $cl->exam_type_id,
                    'ticketable_type' => $cl->clearable_type,
                    'ticketable_id' => $cl->clearable_id,
                    'is_active' => true,
                    'expires_at' => now()->addMonths(2),
                    'ticket_number' => 'TKT-'.strtoupper(Str::random(8)),
                ]
            );
        }

        // Offline Exam Scores
        if (Schema::hasTable('offline_exam_scores') && count($this->offlineExams) > 0) {
            foreach ($this->offlineExams as $oExam) {
                $scoredStudents = collect($this->students)->take(10);
                foreach ($scoredStudents as $student) {
                    $score = fake()->numberBetween(30, 95);
                    OfflineExamScore::firstOrCreate(
                        ['offline_exam_id' => $oExam->id, 'student_id' => $student->id],
                        [
                            'score' => $score,
                            'total_marks' => 100,
                            'percentage' => $score,
                            'remarks' => $score >= 50 ? 'Pass' : 'Fail',
                            'recorded_by' => $this->lecturerUsers[0]->id,
                            'exam_date' => $oExam->date,
                        ]
                    );
                }
            }
        }

        // Assessment Scores
        if (Schema::hasTable('assessment_scores')) {
            $semester = $this->semesters[0];
            $currentAY = $this->academicYears[1];
            foreach ($this->subjects as $si => $subj) {
                if ($si >= 4) {
                    break;
                }
                $lecIdx = $si % count($this->lecturerUsers);
                foreach (collect($this->students)->take(15) as $student) {
                    AssessmentScore::firstOrCreate(
                        ['course_id' => $subj->id, 'student_id' => $student->id, 'semester_id' => $semester->id, 'academic_year_id' => $currentAY->id],
                        [
                            'cohort_id' => $student->cohort_id,
                            'assignment_1_score' => fake()->randomFloat(1, 5, 10),
                            'assignment_2_score' => fake()->randomFloat(1, 4, 10),
                            'assignment_3_score' => fake()->optional(0.6)->randomFloat(1, 5, 10),
                            'assignment_4_score' => null,
                            'assignment_5_score' => null,
                            'assignment_count' => 3,
                            'mid_semester_score' => fake()->randomFloat(1, 15, 30),
                            'end_semester_score' => fake()->optional(0.3)->randomFloat(1, 20, 50),
                            'assignment_weight' => 30,
                            'mid_semester_weight' => 30,
                            'end_semester_weight' => 40,
                            'recorded_by' => $this->lecturerUsers[$lecIdx]->id,
                            'remarks' => fake()->optional(0.3)->randomElement(['Good', 'Satisfactory', 'Excellent']),
                            'is_published' => fake()->boolean(40),
                            'published_at' => fake()->optional(0.4)->dateTimeBetween('2025-12-01', '2026-01-15'),
                            'published_by' => fake()->optional(0.4)->randomElement(collect($this->lecturerUsers)->pluck('id')->toArray()),
                        ]
                    );
                }
            }
        }
    }
}
