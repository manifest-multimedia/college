<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\CollegeClass;
use App\Models\Exam;
use App\Models\Option;
use App\Models\Question;
use App\Models\QuestionSet;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Models\Year;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ExamDummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting Exam Dummy Data Seeder...');

        DB::beginTransaction();

        try {
            // Step 1: Ensure Academic Structure Exists
            $this->command->info('📚 Step 1: Setting up academic structure...');
            $academicStructure = $this->ensureAcademicStructure();

            // Step 2: Create or Get Test Students
            $this->command->info('👥 Step 2: Creating test students...');
            $students = $this->createTestStudents($academicStructure);

            // Step 3: Create Test Subjects/Courses
            $this->command->info('📖 Step 3: Creating test courses...');
            $subjects = $this->createTestSubjects($academicStructure);

            // Step 4: Create Question Sets
            $this->command->info('❓ Step 4: Creating question sets...');
            $questionSets = $this->createQuestionSets($subjects);

            // Step 5: Create Questions in Question Sets
            $this->command->info('🎯 Step 5: Adding questions to question sets...');
            $this->createQuestionsInSets($questionSets);

            // Step 6: Create Exams
            $this->command->info('📝 Step 6: Creating exams...');
            $exams = $this->createExams($subjects);

            // Step 7: Attach Question Sets to Exams
            $this->command->info('🔗 Step 7: Linking question sets to exams...');
            $this->attachQuestionSetsToExams($exams, $questionSets);

            DB::commit();

            $this->command->info('');
            $this->command->info('✅ ========================================');
            $this->command->info('✅ Exam Dummy Data Created Successfully!');
            $this->command->info('✅ ========================================');
            $this->command->info('');
            $this->command->info('📊 Summary:');
            $this->command->info("   • Students: {$students->count()}");
            $this->command->info("   • Subjects: {$subjects->count()}");
            $this->command->info("   • Question Sets: {$questionSets->count()}");
            $this->command->info("   • Exams: {$exams->count()}");
            $this->command->info('');
            $this->command->info('🔑 Test Student Credentials:');
            foreach ($students->take(3) as $student) {
                $this->command->info("   • {$student->student_id} / password");
            }
            $this->command->info('');
            $this->command->info('🔐 Exam Passwords: Each exam has unique password (format: [course_code][type]2024)');
            $this->command->info('   Examples: cs101quiz2024, cs101midterm2024, math101final2024');
            $this->command->info('');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Error: ' . $e->getMessage());
            $this->command->error('Stack trace: ' . $e->getTraceAsString());
        }
    }

    /**
     * Ensure academic structure exists
     */
    private function ensureAcademicStructure(): array
    {
        // Get or create Academic Year
        $academicYear = AcademicYear::firstOrCreate(
            ['name' => '2024/2025'],
            [
                'slug' => Str::slug('2024-2025'),
                'start_date' => '2024-09-01',
                'end_date' => '2025-08-31',
                'is_current' => true
            ]
        );

        // Get or create Semesters
        $semester1 = Semester::firstOrCreate(['name' => 'Semester 1']);
        $semester2 = Semester::firstOrCreate(['name' => 'Semester 2']);

        // Get or create College Classes
        $class1 = CollegeClass::firstOrCreate(
            ['name' => 'HND'],
            [
                'slug' => Str::slug('HND'),
                'description' => 'Higher National Diploma'
            ]
        );
        $class2 = CollegeClass::firstOrCreate(
            ['name' => 'Certificate'],
            [
                'slug' => Str::slug('Certificate'),
                'description' => 'Certificate Program'
            ]
        );

        // Get or create Years
        $year1 = Year::firstOrCreate(['name' => 'Year 1']);
        $year2 = Year::firstOrCreate(['name' => 'Year 2']);

        $this->command->info('   ✓ Academic structure ready');

        return [
            'academic_year' => $academicYear,
            'semesters' => [$semester1, $semester2],
            'classes' => [$class1, $class2],
            'years' => [$year1, $year2],
        ];
    }

    /**
     * Create test students
     */
    private function createTestStudents($structure): \Illuminate\Support\Collection
    {
        $students = collect();
        $class = $structure['classes'][0];
        $academicYear = $structure['academic_year'];

        for ($i = 1; $i <= 10; $i++) {
            $studentId = 'TEST' . str_pad($i, 4, '0', STR_PAD_LEFT);

            // Check if student already exists
            $student = Student::where('student_id', $studentId)->first();

            if (!$student) {
                // Create user first
                $user = User::create([
                    'name' => "Test Student {$i}",
                    'email' => "student{$i}@test.local",
                    'password' => Hash::make('password'),
                ]);

                // Assign Student role
                $studentRole = \Spatie\Permission\Models\Role::where('name', 'Student')->first();
                if ($studentRole) {
                    $user->assignRole($studentRole);
                }

                // Create student
                $student = Student::create([
                    'student_id' => $studentId,
                    'first_name' => "Test",
                    'last_name' => "Student{$i}",
                    'email' => "student{$i}@test.local",
                    'mobile_number' => '0200000' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'gender' => $i % 2 == 0 ? 'Male' : 'Female',
                    'college_class_id' => $class->id,
                    'academic_year_id' => $academicYear->id,
                    'user_id' => $user->id,
                    'status' => 'active',
                ]);
            }

            $students->push($student);
        }

        $this->command->info("   ✓ Created/verified {$students->count()} test students");
        return $students;
    }

    /**
     * Create test subjects
     */
    private function createTestSubjects($structure): \Illuminate\Support\Collection
    {
        $subjects = collect();
        $class = $structure['classes'][0];
        $year = $structure['years'][0];
        $semester = $structure['semesters'][0];

        $subjectData = [
            ['code' => 'CS101', 'name' => 'Introduction to Computer Science', 'credit_hours' => 3.0],
            ['code' => 'MATH101', 'name' => 'Mathematics for Computing', 'credit_hours' => 3.0],
            ['code' => 'ENG101', 'name' => 'Technical English', 'credit_hours' => 2.0],
            ['code' => 'PROG101', 'name' => 'Programming Fundamentals', 'credit_hours' => 4.0],
            ['code' => 'DB101', 'name' => 'Database Systems', 'credit_hours' => 3.0],
        ];

        foreach ($subjectData as $data) {
            $subject = Subject::firstOrCreate(
                [
                    'course_code' => $data['code'],
                    'college_class_id' => $class->id,
                    'year_id' => $year->id,
                    'semester_id' => $semester->id,
                ],
                [
                    'name' => $data['name'],
                    'credit_hours' => $data['credit_hours'],
                ]
            );

            $subjects->push($subject);
        }

        $this->command->info("   ✓ Created/verified {$subjects->count()} test courses");
        return $subjects;
    }

    /**
     * Create question sets for subjects
     */
    private function createQuestionSets($subjects): \Illuminate\Support\Collection
    {
        $questionSets = collect();
        $systemUser = User::where('email', 'system@college.local')->first() 
            ?? User::first();

        foreach ($subjects as $subject) {
            // Create 2 question sets per subject with different difficulty levels
            $difficulties = ['easy', 'medium'];

            foreach ($difficulties as $difficulty) {
                $questionSet = QuestionSet::firstOrCreate(
                    [
                        'name' => "{$subject->course_code} - " . ucfirst($difficulty) . " Questions",
                        'course_id' => $subject->id,
                    ],
                    [
                        'description' => "Collection of {$difficulty} level questions for {$subject->name}",
                        'difficulty_level' => $difficulty,
                        'created_by' => $systemUser->id,
                    ]
                );

                $questionSets->push($questionSet);
            }
        }

        $this->command->info("   ✓ Created/verified {$questionSets->count()} question sets");
        return $questionSets;
    }

    /**
     * Create questions in question sets
     */
    private function createQuestionsInSets($questionSets): void
    {
        $totalQuestions = 0;

        foreach ($questionSets as $questionSet) {
            // Skip if questions already exist
            if ($questionSet->questions()->count() > 0) {
                $totalQuestions += $questionSet->questions()->count();
                continue;
            }

            // Create 15-20 questions per set
            $numQuestions = rand(15, 20);

            for ($i = 1; $i <= $numQuestions; $i++) {
                $question = Question::create([
                    'question_set_id' => $questionSet->id,
                    'question_text' => "Question {$i} for {$questionSet->name}: What is the correct answer?",
                    'type' => 'MCQ',
                    'difficulty_level' => $questionSet->difficulty_level,
                    'mark' => 1,
                    'explanation' => "This is the explanation for question {$i}.",
                ]);

                // Create 4 options for each question
                $options = [
                    ['text' => 'Option A - Correct Answer', 'is_correct' => true],
                    ['text' => 'Option B - Incorrect', 'is_correct' => false],
                    ['text' => 'Option C - Incorrect', 'is_correct' => false],
                    ['text' => 'Option D - Incorrect', 'is_correct' => false],
                ];

                foreach ($options as $option) {
                    Option::create([
                        'question_id' => $question->id,
                        'option_text' => $option['text'],
                        'is_correct' => $option['is_correct'],
                    ]);
                }

                $totalQuestions++;
            }
        }

        $this->command->info("   ✓ Created {$totalQuestions} questions with options");
    }

    /**
     * Create exams
     */
    private function createExams($subjects): \Illuminate\Support\Collection
    {
        $exams = collect();
        $systemUser = User::where('email', 'system@college.local')->first() 
            ?? User::first();

        $examTypes = ['quiz', 'midterm', 'final'];

        foreach ($subjects->take(3) as $subject) {
            foreach ($examTypes as $type) {
                // Use a consistent slug without timestamp to prevent duplicates
                $slug = Str::slug("{$subject->course_code}-{$type}-test");
                
                // Generate unique password for each exam
                $uniquePassword = strtolower($subject->course_code) . $type . '2024';
                
                $exam = Exam::firstOrCreate(
                    [
                        'course_id' => $subject->id,
                        'type' => $type,
                        'slug' => $slug,
                    ],
                    [
                        'user_id' => $systemUser->id,
                        'duration' => $type === 'quiz' ? 30 : ($type === 'midterm' ? 60 : 120),
                        'questions_per_session' => $type === 'quiz' ? 10 : ($type === 'midterm' ? 20 : 30),
                        'passing_percentage' => 50.00,
                        'password' => $uniquePassword,
                        'status' => 'active',
                        'start_date' => now()->subDays(1),
                        'end_date' => now()->addDays(30),
                    ]
                );

                $exams->push($exam);
            }
        }

        $this->command->info("   ✓ Created/verified {$exams->count()} exams");
        return $exams;
    }

    /**
     * Attach question sets to exams
     */
    private function attachQuestionSetsToExams($exams, $questionSets): void
    {
        $attached = 0;

        foreach ($exams as $exam) {
            if ($exam->questionSets()->count() > 0) {
                continue;
            }

            $courseQuestionSets = $questionSets->filter(function ($qs) use ($exam) {
                return $qs->course_id === $exam->course_id;
            });

            foreach ($courseQuestionSets as $questionSet) {
                $exam->questionSets()->attach($questionSet->id, [
                    'shuffle_questions' => true,
                    'questions_to_pick' => $exam->type === 'quiz' ? 5 : 10,
                ]);
                $attached++;
            }
        }

        $this->command->info("   ✓ Linked {$attached} question sets to exams");
    }
}
