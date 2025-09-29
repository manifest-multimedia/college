# Question Bank Module Migration - Comprehensive Testing Plan

## Overview
This document outlines the comprehensive testing strategy to verify that the enhanced Question Bank Module functions correctly and maintains backward compatibility with existing CIS functionality.

## Testing Environment Setup
- **Environment**: CIS Development/Staging
- **Database**: Current migration with 4 question sets, 435 questions
- **Users**: Super Admin and regular user roles
- **Browser**: Chrome, Firefox, Safari testing

## Test Categories

### 1. Database Schema and Migration Testing ✅
**Status: VERIFIED**
- ✅ Question sets table structure
- ✅ Exam-question set pivot relationships
- ✅ Questions table new fields (question_set_id, type, difficulty_level)
- ✅ Foreign key constraints
- ✅ Data migration integrity (435 questions → 4 question sets)

### 2. Model Relationships Testing
**Areas to Test:**
- [ ] Question ↔ QuestionSet relationship
- [ ] Question ↔ Exam relationship (backward compatibility)
- [ ] Exam ↔ QuestionSet many-to-many relationship
- [ ] QuestionSet ↔ Subject relationship
- [ ] User permissions and role-based access

**Test Commands:**
```bash
# Test Question relationships
php artisan tinker --execute="
$question = App\Models\Question::with('questionSet', 'exam')->first();
echo 'Question Set: ' . ($question->questionSet->name ?? 'None') . PHP_EOL;
echo 'Exam: ' . ($question->exam->title ?? 'None') . PHP_EOL;
"

# Test Exam-QuestionSet relationships
php artisan tinker --execute="
$exam = App\Models\Exam::with('questionSets')->first();
echo 'Question Sets: ' . $exam->questionSets->count() . PHP_EOL;
echo 'Total Questions: ' . $exam->generateSessionQuestions()->count() . PHP_EOL;
"
```

### 3. QuestionBank Component Testing
**Areas to Test:**
- [ ] Question set grid view display
- [ ] Subject filtering functionality
- [ ] Create new question set workflow
- [ ] Question set CRUD operations
- [ ] Question editing within sets
- [ ] Question form validation (MCQ correct answers)
- [ ] Switch between question sets and question editing views

**Manual Test Steps:**
1. Navigate to Question Bank
2. Verify question sets display with correct badges
3. Create new question set
4. Edit existing question set
5. Add questions to question set
6. Test question validation (MCQ must have correct answer)
7. Delete question set (with/without questions)

### 4. ExamEdit Component Testing
**Areas to Test:**
- [ ] Question set assignment interface
- [ ] Question set configuration (questions_to_pick, shuffle)
- [ ] Remove question sets from exams
- [ ] Total questions calculation
- [ ] Real-time updates when configuration changes

**Manual Test Steps:**
1. Navigate to Exam Edit page
2. Click "Manage Question Sets"
3. Assign question sets to exam
4. Configure questions per set and shuffle options
5. Verify total questions calculation
6. Remove question sets
7. Verify backward compatibility with existing exams

### 5. Online Examination Testing
**Critical Backward Compatibility Tests:**
- [ ] Exam session creation
- [ ] Question generation (should return configured number)
- [ ] Question randomization/shuffle
- [ ] Question options display
- [ ] Answer submission and tracking
- [ ] Session completion and scoring

**Test Commands:**
```bash
# Test question generation
php artisan tinker --execute="
$exam = App\Models\Exam::first();
$questions = $exam->generateSessionQuestions();
echo 'Generated Questions: ' . $questions->count() . PHP_EOL;
foreach($questions->take(3) as $q) {
    echo 'Q: ' . substr($q->question_text, 0, 50) . '...' . PHP_EOL;
    echo 'Options: ' . $q->options->count() . PHP_EOL;
}
"
```

### 6. Performance Testing
**Areas to Test:**
- [ ] Question set loading with large datasets
- [ ] Question generation speed for exams
- [ ] Database query optimization
- [ ] Memory usage during bulk operations

### 7. Security and Permissions Testing
**Areas to Test:**
- [ ] Role-based access to question sets
- [ ] User can only edit own question sets (non-Super Admin)
- [ ] Question set assignment permissions
- [ ] Data validation and sanitization

### 8. Edge Cases and Error Handling
**Scenarios to Test:**
- [ ] Empty question sets
- [ ] Questions without correct answers
- [ ] Exam with no assigned question sets
- [ ] Question set deletion with dependencies
- [ ] Concurrent editing by multiple users
- [ ] Network timeouts during operations

## Automated Testing Script

### Database Integrity Test
```bash
#!/bin/bash
echo "=== Question Bank Migration Testing ==="

cd /home/johnsonsebire/www/college.local/cis

echo "1. Testing Database Schema..."
php artisan tinker --execute="
echo 'Question Sets: ' . App\Models\QuestionSet::count() . PHP_EOL;
echo 'Questions with Sets: ' . App\Models\Question::whereNotNull('question_set_id')->count() . PHP_EOL;
echo 'Exams: ' . App\Models\Exam::count() . PHP_EOL;
echo 'Subjects: ' . App\Models\Subject::count() . PHP_EOL;
"

echo "2. Testing Relationships..."
php artisan tinker --execute="
\$exam = App\Models\Exam::with('questionSets')->first();
if (\$exam) {
    echo 'Exam: ' . \$exam->title . PHP_EOL;
    echo 'Assigned Question Sets: ' . \$exam->questionSets->count() . PHP_EOL;
    echo 'Total Questions Available: ' . \$exam->generateSessionQuestions()->count() . PHP_EOL;
}
"

echo "3. Testing Question Generation..."
php artisan tinker --execute="
\$exam = App\Models\Exam::first();
if (\$exam) {
    \$questions = \$exam->generateSessionQuestions(true);
    echo 'Generated Questions: ' . \$questions->count() . PHP_EOL;
    echo 'First Question Options: ' . (\$questions->first()->options->count() ?? 0) . PHP_EOL;
}
"

echo "Testing completed!"
```

## Test Results Documentation

### Expected Results
- All database relationships should work correctly
- Question generation should return expected number of questions
- UI components should load without errors
- Backward compatibility should be maintained
- No data loss should occur during operations

### Success Criteria
- ✅ All 435 questions accessible through question sets
- ✅ Existing online examination functionality preserved  
- ✅ New question set features work as expected
- ✅ Performance remains acceptable
- ✅ No security vulnerabilities introduced

### Rollback Triggers
If any of the following issues are found:
- Data loss or corruption
- Existing examination system breaks
- Performance degradation > 50%
- Security vulnerabilities
- Critical UI/UX issues

## Test Schedule
1. **Database & Models**: Day 1 (2-3 hours)
2. **UI Components**: Day 2 (4-5 hours) 
3. **Integration Testing**: Day 3 (3-4 hours)
4. **Performance & Security**: Day 4 (2-3 hours)
5. **User Acceptance Testing**: Day 5 (2-3 hours)

## Test Team
- **Lead Tester**: Development Team Lead
- **Database Tester**: Backend Developer
- **UI Tester**: Frontend Developer  
- **Integration Tester**: Full Stack Developer
- **User Acceptance**: End Users/Stakeholders