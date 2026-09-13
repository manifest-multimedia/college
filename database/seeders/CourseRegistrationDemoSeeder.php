<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Cohort;
use App\Models\CollegeClass;
use App\Models\CourseRegistration;
use App\Models\Department;
use App\Models\FeeStructure;
use App\Models\FeeType;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentFeeBill;
use App\Models\Subject;
use App\Models\User;
use App\Models\Year;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class CourseRegistrationDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Starting CourseRegistrationDemoSeeder...');

        DB::transaction(function () {
            // 1. Ensure essential roles exist
            $studentRole = Role::firstOrCreate(['name' => 'Student'], ['guard_name' => 'web']);
            $adminRole = Role::firstOrCreate(['name' => 'Admin'], ['guard_name' => 'web']);
            Role::firstOrCreate(['name' => 'Finance Officer'], ['guard_name' => 'web']);

            // 2. Academic Years
            AcademicYear::where('is_current', true)->update(['is_current' => false]);
            Semester::where('is_current', true)->update(['is_current' => false]);

            $ayPast = AcademicYear::firstOrCreate(
                ['name' => '2024/2025'],
                [
                    'slug' => '2024-2025',
                    'year' => 2024,
                    'start_date' => '2024-09-01',
                    'end_date' => '2025-07-31',
                    'is_current' => false,
                ]
            );

            $ayCurrent = AcademicYear::firstOrCreate(
                ['name' => '2025/2026'],
                [
                    'slug' => '2025-2026',
                    'year' => 2025,
                    'start_date' => '2025-09-01',
                    'end_date' => '2026-07-31',
                    'is_current' => true,
                ]
            );
            $ayCurrent->update(['is_current' => true]);

            // 3. Semesters
            // Past academic year semesters (catalog / legacy reference)
            $semPast1 = Semester::firstOrCreate(
                ['academic_year_id' => $ayPast->id, 'sequence' => 1],
                [
                    'name' => 'First Semester',
                    'slug' => 'first-semester-'.$ayPast->id,
                    'start_date' => '2024-09-01',
                    'end_date' => '2025-01-31',
                    'is_current' => false,
                    'description' => 'First Semester 2024/2025',
                ]
            );

            // Current academic year semesters
            $semCurrent1 = Semester::firstOrCreate(
                ['academic_year_id' => $ayCurrent->id, 'sequence' => 1],
                [
                    'name' => 'Semester 1',
                    'slug' => 'semester-1-'.$ayCurrent->id,
                    'start_date' => '2025-09-01',
                    'end_date' => '2026-01-31',
                    'is_current' => true,
                    'description' => 'First Semester 2025/2026',
                ]
            );
            $semCurrent1->update(['is_current' => true]);

            $semCurrent2 = Semester::firstOrCreate(
                ['academic_year_id' => $ayCurrent->id, 'sequence' => 2],
                [
                    'name' => 'Semester 2',
                    'slug' => 'semester-2-'.$ayCurrent->id,
                    'start_date' => '2026-02-01',
                    'end_date' => '2026-07-31',
                    'is_current' => false,
                    'description' => 'Second Semester 2025/2026',
                ]
            );

            // 4. Study Levels (Years)
            $year1 = Year::firstOrCreate(['name' => 'Year 1'], ['slug' => 'year-1']);
            $year2 = Year::firstOrCreate(['name' => 'Year 2'], ['slug' => 'year-2']);
            $year3 = Year::firstOrCreate(['name' => 'Year 3'], ['slug' => 'year-3']);

            // 5. Programs / CollegeClasses
            $programRgn = CollegeClass::firstOrCreate(
                ['short_name' => 'RGN'],
                [
                    'name' => 'Registered General Nursing',
                    'slug' => 'registered-general-nursing',
                    'description' => 'Registered General Nursing Programme',
                    'is_active' => true,
                    'is_deleted' => false,
                ]
            );

            $programRm = CollegeClass::firstOrCreate(
                ['short_name' => 'RM'],
                [
                    'name' => 'Registered Midwifery',
                    'slug' => 'registered-midwifery',
                    'description' => 'Registered Midwifery Programme',
                    'is_active' => true,
                    'is_deleted' => false,
                ]
            );

            // 6. Cohorts
            $cohort2024 = Cohort::firstOrCreate(
                ['slug' => '2024-intake'],
                [
                    'name' => '2024 Intake',
                    'description' => '2024 Batch',
                    'academic_year' => $ayPast->name,
                    'start_date' => $ayPast->start_date,
                    'end_date' => $ayPast->end_date,
                    'is_active' => true,
                    'is_deleted' => false,
                ]
            );

            $cohort2025 = Cohort::firstOrCreate(
                ['slug' => '2025-intake'],
                [
                    'name' => '2025 Intake',
                    'description' => '2025 Batch',
                    'academic_year' => $ayCurrent->name,
                    'start_date' => $ayCurrent->start_date,
                    'end_date' => $ayCurrent->end_date,
                    'is_active' => true,
                    'is_deleted' => false,
                ]
            );

            // 7. Departments & Fee Types
            $deptNursing = Department::firstOrCreate(
                ['code' => 'NUR'],
                ['name' => 'Nursing Department', 'is_active' => true]
            );

            $feeTypeTuition = FeeType::firstOrCreate(
                ['code' => 'TUI'],
                ['name' => 'Tuition Fee', 'description' => 'Academic Tuition', 'is_active' => true]
            );

            // Fee Structure for RGN in current Semester
            $feeStructure = FeeStructure::firstOrCreate(
                [
                    'fee_type_id' => $feeTypeTuition->id,
                    'college_class_id' => $programRgn->id,
                    'academic_year_id' => $ayCurrent->id,
                    'semester_id' => $semCurrent1->id,
                ],
                [
                    'amount' => 3000.00,
                    'is_mandatory' => true,
                    'is_active' => true,
                    'applicable_gender' => 'all',
                ]
            );

            // 8. Subjects
            // A) Year 1 RGN Semester 1 Subjects (linked to current semester)
            $subjectsRgnY1 = [
                ['code' => 'NUR 111', 'name' => 'Anatomy and Physiology I', 'credits' => 3.0, 'sem_id' => $semCurrent1->id],
                ['code' => 'NUR 112', 'name' => 'Fundamentals of Basic Nursing', 'credits' => 4.0, 'sem_id' => $semCurrent1->id],
                ['code' => 'NUR 113', 'name' => 'Microbiology & Infection Prevention', 'credits' => 3.0, 'sem_id' => $semCurrent1->id],
            ];
            foreach ($subjectsRgnY1 as $s) {
                Subject::updateOrCreate(
                    ['course_code' => $s['code']],
                    [
                        'name' => $s['name'],
                        'credit_hours' => $s['credits'],
                        'college_class_id' => $programRgn->id,
                        'year_id' => $year1->id,
                        'semester_id' => $s['sem_id'],
                        'slug' => Str::slug($s['name']),
                    ]
                );
            }

            // B) General Subject for RGN
            Subject::updateOrCreate(
                ['course_code' => 'GEN 101'],
                [
                    'name' => 'English Communication Skills',
                    'credit_hours' => 2.0,
                    'college_class_id' => $programRgn->id,
                    'year_id' => $year1->id,
                    'semester_id' => $semCurrent1->id,
                    'slug' => 'english-communication-skills',
                ]
            );

            // C) Catalog Subject created under past semester sequence 1 (testing catalog sequence matching)
            Subject::updateOrCreate(
                ['course_code' => 'NUR 114'],
                [
                    'name' => 'Therapeutic Communication in Nursing',
                    'credit_hours' => 2.0,
                    'college_class_id' => $programRgn->id,
                    'year_id' => $year1->id,
                    'semester_id' => $semPast1->id, // Linked to past semester instance of sequence 1
                    'slug' => 'therapeutic-communication-in-nursing',
                ]
            );

            // D) Year 2 RGN Semester 1 Subjects
            $subjectsRgnY2 = [
                ['code' => 'NUR 211', 'name' => 'Medical-Surgical Nursing I', 'credits' => 4.0],
                ['code' => 'NUR 212', 'name' => 'Pharmacology I', 'credits' => 3.0],
            ];
            foreach ($subjectsRgnY2 as $s) {
                Subject::updateOrCreate(
                    ['course_code' => $s['code']],
                    [
                        'name' => $s['name'],
                        'credit_hours' => $s['credits'],
                        'college_class_id' => $programRgn->id,
                        'year_id' => $year2->id,
                        'semester_id' => $semCurrent1->id,
                        'slug' => Str::slug($s['name']),
                    ]
                );
            }

            // E) Midwifery Subjects (RM)
            Subject::updateOrCreate(
                ['course_code' => 'RMD 111'],
                [
                    'name' => 'Introduction to Midwifery Practice',
                    'credit_hours' => 3.0,
                    'college_class_id' => $programRm->id,
                    'year_id' => $year1->id,
                    'semester_id' => $semCurrent1->id,
                    'slug' => 'introduction-to-midwifery-practice',
                ]
            );

            // 9. Demo Students and Scenarios
            $studentsData = [
                [
                    'email' => 'student.eligible@college.test',
                    'name' => 'Kwame Mensah',
                    'first' => 'Kwame',
                    'last' => 'Mensah',
                    'student_id' => 'NTC/DEMO/001',
                    'cohort' => $cohort2025,
                    'bill_status' => 'paid',
                    'paid_amount' => 3000.00,
                    'total_amount' => 3000.00,
                    'pre_register' => false,
                ],
                [
                    'email' => 'student.threshold@college.test',
                    'name' => 'Ama Asante',
                    'first' => 'Ama',
                    'last' => 'Asante',
                    'student_id' => 'NTC/DEMO/002',
                    'cohort' => $cohort2025,
                    'bill_status' => 'partial',
                    'paid_amount' => 1800.00, // Exactly 60% of 3000
                    'total_amount' => 3000.00,
                    'pre_register' => false,
                ],
                [
                    'email' => 'student.unpaid@college.test',
                    'name' => 'Kofi Boateng',
                    'first' => 'Kofi',
                    'last' => 'Boateng',
                    'student_id' => 'NTC/DEMO/003',
                    'cohort' => $cohort2025,
                    'bill_status' => 'partial',
                    'paid_amount' => 900.00, // 30% of 3000 (under 60%)
                    'total_amount' => 3000.00,
                    'pre_register' => false,
                ],
                [
                    'email' => 'student.nobill@college.test',
                    'name' => 'Abena Owusu',
                    'first' => 'Abena',
                    'last' => 'Owusu',
                    'student_id' => 'NTC/DEMO/004',
                    'cohort' => $cohort2025,
                    'bill_status' => 'none',
                    'paid_amount' => 0.00,
                    'total_amount' => 0.00,
                    'pre_register' => false,
                ],
                [
                    'email' => 'student.registered@college.test',
                    'name' => 'Yaw Adjei',
                    'first' => 'Yaw',
                    'last' => 'Adjei',
                    'student_id' => 'NTC/DEMO/005',
                    'cohort' => $cohort2025,
                    'bill_status' => 'paid',
                    'paid_amount' => 3000.00,
                    'total_amount' => 3000.00,
                    'pre_register' => true,
                ],
                [
                    'email' => 'student.year2@college.test',
                    'name' => 'Akua Bonsu',
                    'first' => 'Akua',
                    'last' => 'Bonsu',
                    'student_id' => 'NTC/DEMO/006',
                    'cohort' => $cohort2024, // Started in 2024/2025 -> Year 2 in 2025/2026
                    'bill_status' => 'paid',
                    'paid_amount' => 3000.00,
                    'total_amount' => 3000.00,
                    'pre_register' => false,
                ],
            ];

            foreach ($studentsData as $data) {
                // User
                $user = User::updateOrCreate(
                    ['email' => $data['email']],
                    [
                        'name' => $data['name'],
                        'password' => Hash::make('password'),
                        'role' => 'student',
                        'email_verified_at' => now(),
                    ]
                );

                if (! $user->hasRole('Student')) {
                    $user->assignRole($studentRole);
                }

                // Student
                $student = Student::updateOrCreate(
                    ['student_id' => $data['student_id']],
                    [
                        'user_id' => $user->id,
                        'first_name' => $data['first'],
                        'last_name' => $data['last'],
                        'gender' => 'Female',
                        'date_of_birth' => '2004-05-15',
                        'nationality' => 'Ghanaian',
                        'email' => $data['email'],
                        'mobile_number' => '0240000000',
                        'college_class_id' => $programRgn->id,
                        'cohort_id' => $data['cohort']->id,
                        'academic_year_id' => $data['cohort']->academic_year === $ayCurrent->name ? $ayCurrent->id : $ayPast->id,
                        'status' => 'active',
                    ]
                );

                // Fee bill
                if ($data['bill_status'] !== 'none') {
                    $pct = ($data['paid_amount'] / $data['total_amount']) * 100.0;
                    $bal = $data['total_amount'] - $data['paid_amount'];

                    StudentFeeBill::updateOrCreate(
                        [
                            'student_id' => $student->id,
                            'academic_year_id' => $ayCurrent->id,
                            'semester_id' => $semCurrent1->id,
                        ],
                        [
                            'total_amount' => $data['total_amount'],
                            'amount_paid' => $data['paid_amount'],
                            'balance' => $bal,
                            'payment_percentage' => $pct,
                            'status' => $data['bill_status'],
                            'billing_date' => now(),
                            'bill_reference' => 'BILL-'.$student->student_id.'-'.$semCurrent1->id,
                            'public_reference' => 'PB-'.Str::upper(Str::random(8)),
                        ]
                    );
                }

                // Pre-register courses if requested (Student 5)
                if ($data['pre_register']) {
                    CourseRegistration::where('student_id', $student->id)
                        ->where('academic_year_id', $ayCurrent->id)
                        ->where('semester_id', $semCurrent1->id)
                        ->delete();

                    $y1Courses = Subject::whereIn('course_code', ['NUR 111', 'NUR 112', 'NUR 113', 'GEN 101'])->get();
                    foreach ($y1Courses as $idx => $course) {
                        $approved = $idx < 2; // first 2 approved, rest pending
                        CourseRegistration::create([
                            'student_id' => $student->id,
                            'subject_id' => $course->id,
                            'academic_year_id' => $ayCurrent->id,
                            'semester_id' => $semCurrent1->id,
                            'registered_at' => now()->subDays(3),
                            'payment_percentage_at_registration' => 100.0,
                            'is_approved' => $approved,
                            'approved_at' => $approved ? now()->subDay() : null,
                            'approved_by' => $approved ? 1 : null,
                        ]);
                    }
                }
            }
        });

        $this->command->info('✅ CourseRegistrationDemoSeeder executed successfully!');
        $this->command->info('--------------------------------------------------');
        $this->command->info('Demo accounts (Password for all: password):');
        $this->command->info('  1) student.eligible@college.test   - Ready to register (100% fees paid)');
        $this->command->info('  2) student.threshold@college.test  - Eligible at 60% threshold');
        $this->command->info('  3) student.unpaid@college.test     - Ineligible (30% fees paid)');
        $this->command->info('  4) student.nobill@college.test     - Ineligible (no fee bill generated)');
        $this->command->info('  5) student.registered@college.test - Already registered (pre-selected + history)');
        $this->command->info('  6) student.year2@college.test      - Year 2 student (sees Year 2 subjects)');
        $this->command->info('--------------------------------------------------');
    }
}
