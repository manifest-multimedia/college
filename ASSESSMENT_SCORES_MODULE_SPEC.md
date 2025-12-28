# Continuous Assessment Scores Module - Implementation Specification

## Executive Summary

This specification outlines the redesign of the existing "Offline Exam Scores" module into a simplified, user-friendly "Continuous Assessment Scores" module for recording student scores from assessments taken outside the online examination system. This module will integrate seamlessly with the transcript generation service.

---

## 1. Current State Analysis

### 1.1 Existing Module: "Offline Exams"
**Location:** `/admin/exams/offline` and `/admin/exams/offline-scores`

**Current Components:**
- **OfflineExams Component** (`app/Livewire/Admin/OfflineExams.php`)
- **OfflineExamScores Component** (`app/Livewire/Admin/OfflineExamScores.php`)
- **Database Tables:**
  - `offline_exams` (stores exam metadata)
  - `offline_exam_scores` (stores student scores)

**Current Features:**
1. Create offline exams with detailed metadata:
   - Title, description, date, duration
   - Course/Subject association
   - Proctor assignment
   - Venue specification
   - Fee clearance threshold
   - Passing percentage
   - Status management (draft, published, completed, cancelled)
   
2. Score entry methods:
   - Single score entry (modal-based)
   - Bulk score entry (spreadsheet-like)
   - Edit/delete existing scores
   
3. Filtering and organization:
   - Filter by academic year
   - Filter by semester
   - Filter by class
   - Search by exam name
   
4. Export functionality:
   - Export to Excel
   - Includes student ID, name, class, score, percentage, grade, status

### 1.2 Integration Points

**Transcript Service** (`app/Services/TranscriptService.php`)
- Line 208: `getOfflineExamScore()` method retrieves offline scores
- Line 127: Combines online and offline scores for final grade calculation
- Uses weighted average: Online (40%) + Offline (60%) if both exist

**Results Service** (`app/Services/ResultsService.php`)
- Provides consistent score calculation methods
- Grade determination logic
- Pass/fail status computation

### 1.3 Current Pain Points

1. **Complexity Overload:**
   - Too many fields required for creating an "exam" just to record scores
   - Proctor, venue, duration, clearance threshold - not needed for simple score recording
   - Status management adds unnecessary workflow complexity

2. **Workflow Inefficiency:**
   - Two-step process: Create offline exam → Record scores
   - Mandatory fields slow down score entry
   - Bulk entry requires loading students first

3. **Naming Confusion:**
   - "Offline Exam" implies a full exam setup (like online exams)
   - Users expect to record simple test/quiz scores, not create exam events

4. **Navigation Issues:**
   - Buried under exam management section
   - Not intuitive for continuous assessment recording

---

## 2. Proposed Solution: Continuous Assessment Scores Module

### 2.1 New Module Name
**"Continuous Assessment Scores"** or **"Assessment Scores"**
- More accurate representation of functionality
- Aligns with academic terminology
- Clear distinction from formal examination system

### 2.2 Simplified Workflow

**Core Concept:** Direct score recording without exam setup overhead

**Three-Step Process:**
1. **Select Course/Subject** → Filter by program/class
2. **Load Class Scoresheet** → Shows all students with assessment columns
3. **Enter Scores** → Save immediately with auto-calculation

### 2.2.1 Grading Components Structure

Based on institutional standards, the module supports three main assessment categories:

**1. Assignments (Multiple entries):**
- Support for 3 or more assignments per course
- Each assignment scored out of 100%
- Final assignment score is the average of all assignments
- Configurable weight (default: 20% of total)

**2. Mid-Semester Exam (Single entry):**
- One mid-semester examination score out of 100%
- Configurable weight (default: 20% of total)

**3. End-of-Semester Exam (Single entry):**
- Final examination score out of 100%
- Configurable weight (default: 60% of total)
- Note: Some institutions use 70% for final exams

**Total Calculation:**
```
Assignment Average = (Assignment1 + Assignment2 + Assignment3 + ...) / Number of Assignments
Assignment Weighted = Assignment Average × Assignment Weight%

Mid-Sem Weighted = Mid-Sem Score × Mid-Sem Weight%

End-Sem Weighted = End-Sem Score × End-Sem Weight%

TOTAL = Assignment Weighted + Mid-Sem Weighted + End-Sem Weighted
GRADE = Auto-calculated based on TOTAL using institutional grading scale
```

**Example (Default 20-20-60 System):**
- Assignment 1: 85%, Assignment 2: 90%, Assignment 3: 80%
- Assignment Average: (85 + 90 + 80) / 3 = 85%
- Assignment Weighted: 85 × 20% = 17.0

- Mid-Sem: 75%
- Mid-Sem Weighted: 75 × 20% = 15.0

- End-Sem: 82%
- End-Sem Weighted: 82 × 60% = 49.2

- **TOTAL: 17.0 + 15.0 + 49.2 = 81.2%**
- **GRADE: B+ (based on grading scale)**

### 2.3 User Interface Design

#### 2.3.1 Main View Structure (Comprehensive Scoresheet)

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│  📊 Continuous Assessment Scores                                                       │
├────────────────────────────────────────────────────────────────────────────────────────┤
│                                                                                         │
│  [ Select Assessment Details ]                                                          │
│                                                                                         │
│  Course/Subject: [Dropdown: All subjects         ▼]    Academic Year: [2025/2026    ▼] │
│  Program/Class:  [Dropdown: All classes          ▼]    Semester:      [First Semester ▼]│
│                                                                                         │
│  [🔍 Load Scoresheet]  [⚙️ Configure Weights]  [📥 Import from Excel]                 │
│                                                                                         │
│  ┌─ Assessment Weights (Click to edit) ──────────────────────────────────────────────┐ │
│  │  Assignments: [20%]  │  Mid-Semester: [20%]  │  End-of-Semester: [60%]  = 100%  │ │
│  └─────────────────────────────────────────────────────────────────────────────────────┘│
│                                                                                         │
└────────────────────────────────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────────────────────────────────┐
│  📋 Scoresheet: [Course Name] - [Class Name] - [Semester] - [Academic Year]           │
├────────────────────────────────────────────────────────────────────────────────────────┤
│                                                                                         │
│  ┌──────────────────────────────────────────────────────────────────────────────────┐ │
│  │     │         │               │── ASSIGNMENTS (100% each) ──│ MID-SEM │ SEM EXAMS│  ││
│  │  #  │ INDEX   │ STUDENT NAME  │ Assign1 │ Assign2 │ Assign3 │  100%   │   100%   │  ││
│  │     │   NO    │               │  100%   │  100%   │  100%   │   20%   │    60%   │  ││
│  ├─────┼─────────┼───────────────┼─────────┼─────────┼─────────┼─────────┼──────────┼──┤│
│  │  1  │ MHIA001 │ John Doe      │  [85]   │  [90]   │  [80]   │  [75]   │   [82]   │  ││
│  │     │         │               │         │         │  20%:17 │ 20%:15  │  60%:49.2│  ││
│  │     │         │               │         │         │         │         │          │  ││
│  │  2  │ MHIA002 │ Jane Smith    │  [92]   │  [88]   │  [95]   │  [85]   │   [90]   │  ││
│  │     │         │               │         │         │  20%:18 │ 20%:17  │  60%:54  │  ││
│  │     │         │               │         │         │         │         │          │  ││
│  │  3  │ MHIA003 │ Bob Johnson   │  [78]   │  [82]   │  [75]   │  [70]   │   [68]   │  ││
│  │     │         │               │         │         │  20%:16 │ 20%:14  │  60%:40.8│  ││
│  └─────┴─────────┴───────────────┴─────────┴─────────┴─────────┴─────────┴──────────┴──┘│
│                                                                                         │
│  ┌──────────────────────────────────────────────────────────────────────────────────┐ │
│  │ TOTAL (100%) │ GRADE │ Actions                                                    │ │
│  ├──────────────┼───────┼────────────────────────────────────────────────────────────┤ │
│  │    81.2      │   B+  │  [👁️ View] [✏️ Edit] [📊 Details]                         │ │
│  │    89.0      │   A   │  [👁️ View] [✏️ Edit] [📊 Details]                         │ │
│  │    70.8      │   B-  │  [👁️ View] [✏️ Edit] [📊 Details]                         │ │
│  └──────────────┴───────┴────────────────────────────────────────────────────────────┘ │
│                                                                                         │
│  [💾 Save All Scores]  [📤 Export to Excel]  [📄 Export to PDF]  [🔄 Recalculate All]│
│                                                                                         │
└────────────────────────────────────────────────────────────────────────────────────────┘
```

**Key UI Features:**
- **Horizontal Scrolling:** For courses with more than 3 assignments
- **Inline Calculation Display:** Shows weighted scores below raw scores
- **Color Coding:** Green (Pass), Red (Fail), Yellow (Pending)
- **Auto-Grade Column:** Automatically updates based on total
- **Configurable Weights:** Click "Configure Weights" to adjust percentages

#### 2.3.2 Key Interface Features

1. **Auto-calculation:**
   - Weighted scores calculated automatically as user types
   - Assignment average computed from all entered assignments
   - Total score updates in real-time (Assignment + Mid-Sem + End-Sem weighted scores)
   - Grade automatically determined based on institutional grading scale
   - Visual indicators: Green (Pass ≥50), Red (Fail <50), Yellow (Incomplete)

2. **Inline Editing:**
   - Edit score directly in table cell
   - Tab navigation between cells (left to right across assignments)
   - Enter key moves to next student (down)
   - Shift+Tab for backward navigation
   - Auto-focus on next empty cell

3. **Dynamic Weight Configuration:**
   - Click "Configure Weights" button to open weight settings modal
   - Adjust Assignment weight (default: 20%)
   - Adjust Mid-Semester weight (default: 20%)
   - Adjust End-of-Semester weight (default: 60%)
   - System validates that total = 100%
   - Save configuration per course or apply globally
   - Common presets: 20-20-60, 20-10-70, 30-20-50

4. **Flexible Assignment Count:**
   - Support for 3 or more assignments (up to 10)
   - Add/remove assignment columns dynamically
   - System automatically recalculates average when assignments added/removed
   - Empty assignments excluded from average calculation

5. **Quick Filters:**
   - Show only students with complete scores
   - Show only students with incomplete scores
   - Show only passing students (Total ≥ 50%)
   - Show only failing students (Total < 50%)
   - Filter by student name/ID (live search)
   - Filter by grade (A, B+, B, C+, etc.)

6. **Bulk Operations:**
   - **Import scores from Excel template:**
     * Download Excel template with pre-populated student list
     * Template includes columns: INDEX NO, STUDENT NAME, ASSIGNMENT 1, ASSIGNMENT 2, ASSIGNMENT 3, MID-SEM, END-SEM
     * Lecturers fill in scores offline and upload
     * System validates data (scores 0-100, required columns present)
     * Preview imported data before saving
     * Option to update existing scores or skip duplicates
     * Import error reporting with line numbers
   - Export current scoresheet to Excel (matches snapshot format)
   - Export to PDF with institutional branding
   - Mark all as absent for specific assessment (set to 0)
   - Clear all unsaved scores
   - Copy scores from previous semester (for reference)

7. **Validation:**
   - Score cannot exceed 100 for any assessment
   - Decimal scores supported (e.g., 85.5)
   - Warning if end-of-semester exam not entered
   - Visual error highlighting with tooltips
   - Prevent saving incomplete rows (must have at least Total score)
   - Warn if weights don't sum to 100%

8. **Grade Display:**
   - Auto-calculate grade based on Total percentage
   - Use institutional grading scale:
     * A: 80-100%
     * B+: 75-79%
     * B: 70-74%
     * C+: 65-69%
     * C: 60-64%
     * D+: 55-59%
     * D: 50-54%
     * E: 0-49% (Fail)
   - Configurable grading scale per institution

### 2.4 Data Structure Simplification

#### Current Database Schema
```sql
offline_exams:
  - id, title, description, date, duration, status
  - course_id, user_id, type_id, proctor_id
  - venue, clearance_threshold, passing_percentage
  - created_at, updated_at

offline_exam_scores:
  - id, offline_exam_id, student_id
  - score, total_marks, percentage
  - remarks, recorded_by, exam_date
  - created_at, updated_at
```

#### Proposed Schema
```sql
assessment_scores:
  - id (primary key)
  - course_id (foreign key to subjects table)
  - student_id (foreign key to students table)
  - academic_year_id (foreign key to academic_years table)
  - semester_id (foreign key to semesters table)
  
  -- Assignment Scores (each out of 100)
  - assignment_1_score (decimal 5,2, nullable)
  - assignment_2_score (decimal 5,2, nullable)
  - assignment_3_score (decimal 5,2, nullable)
  - assignment_4_score (decimal 5,2, nullable)
  - assignment_5_score (decimal 5,2, nullable)
  - assignment_count (integer, default 3) -- Number of assignments to consider
  - assignment_average (decimal 5,2, computed) -- Average of entered assignments
  - assignment_weighted (decimal 5,2, computed) -- Average × Weight%
  
  -- Mid-Semester Exam
  - mid_semester_score (decimal 5,2, nullable, out of 100)
  - mid_semester_weighted (decimal 5,2, computed) -- Score × Weight%
  
  -- End-of-Semester Exam
  - end_semester_score (decimal 5,2, nullable, out of 100)
  - end_semester_weighted (decimal 5,2, computed) -- Score × Weight%
  
  -- Totals and Grade
  - total_score (decimal 5,2, computed) -- Sum of all weighted scores
  - grade_letter (varchar 5, computed) -- A, B+, B, C+, C, D+, D, E
  - grade_points (decimal 3,2, computed) -- 4.0 scale
  - is_passed (boolean, computed) -- total_score >= 50
  
  -- Weight Configuration (percentages, must sum to 100)
  - assignment_weight (decimal 5,2, default 20.00) -- % of total
  - mid_semester_weight (decimal 5,2, default 20.00) -- % of total
  - end_semester_weight (decimal 5,2, default 60.00) -- % of total
  
  -- Metadata
  - recorded_by (foreign key to users table)
  - remarks (text, nullable)
  - created_at (timestamp)
  - updated_at (timestamp)
  
  UNIQUE KEY: (course_id, student_id, academic_year_id, semester_id)
  INDEX: (student_id, academic_year_id, semester_id)
  INDEX: (course_id, academic_year_id, semester_id)
```

**Alternative: Normalized Schema (for more than 5 assignments)**
```sql
assessment_score_master:
  - id, course_id, student_id
  - academic_year_id, semester_id
  - assignment_weight, mid_semester_weight, end_semester_weight
  - total_score, grade_letter, grade_points, is_passed
  - recorded_by, created_at, updated_at
  UNIQUE KEY: (course_id, student_id, academic_year_id, semester_id)

assessment_score_details:
  - id, assessment_score_master_id
  - assessment_type (enum: 'assignment', 'mid_semester', 'end_semester')
  - assessment_number (integer, e.g., 1 for Assignment 1)
  - score (decimal 5,2, out of 100)
  - weighted_score (decimal 5,2, computed)
  - created_at, updated_at
  UNIQUE KEY: (assessment_score_master_id, assessment_type, assessment_number)
```

**Benefits:**
- Single record per student per course per semester
- No "exam" creation overhead
- Direct score entry with dynamic weighting
- Supports multiple assignments (flexible count)
- Auto-calculation of totals and grades
- Prevents duplicate entries
- Maintains historical weight configurations
- Compatible with transcript generation service

---

## 3. Technical Implementation

### 3.1 Migration Strategy

#### Phase 1: Deprecation (Week 1)
1. Create new `assessment_scores` table
2. Add deprecation notice to old modules
3. Update navigation to show new module
4. Keep old module accessible with "Legacy" badge

#### Phase 2: Data Migration (Week 1-2)
```php
// Migration script to transfer existing data
class MigrateOfflineExamScoresToAssessmentScores extends Migration
{
    public function up()
    {
        DB::table('offline_exam_scores')
            ->join('offline_exams', 'offline_exam_scores.offline_exam_id', '=', 'offline_exams.id')
            ->select([
                'offline_exam_scores.student_id',
                'offline_exams.course_id',
                'offline_exams.title as assessment_title',
                'offline_exam_scores.score',
                'offline_exam_scores.total_marks',
                'offline_exam_scores.percentage',
                'offline_exam_scores.exam_date as assessment_date',
                'offline_exam_scores.recorded_by',
                'offline_exam_scores.remarks',
                'offline_exam_scores.created_at',
                'offline_exam_scores.updated_at',
            ])
            ->each(function ($score) {
                AssessmentScore::create([
                    'student_id' => $score->student_id,
                    'course_id' => $score->course_id,
                    'assessment_type' => 'test', // Default type
                    'assessment_title' => $score->assessment_title,
                    'score' => $score->score,
                    'total_marks' => $score->total_marks,
                    'percentage' => $score->percentage,
                    'assessment_date' => $score->assessment_date,
                    'recorded_by' => $score->recorded_by,
                    'remarks' => $score->remarks,
                    'created_at' => $score->created_at,
                    'updated_at' => $score->updated_at,
                ]);
            });
    }
}
```

#### Phase 3: Integration Updates (Week 2)
1. Update `TranscriptService` to use new table
2. Maintain backward compatibility during transition
3. Update exports and reports

#### Phase 4: Complete Migration (Week 3)
1. Archive old tables (don't drop)
2. Remove old routes
3. Update documentation
4. Train users on new interface

### 3.2 New Component Structure

```
app/Livewire/Admin/AssessmentScores.php
  ├── Properties
  │   ├── $selectedCourseId
  │   ├── $selectedClassId
  │   ├── $selectedAcademicYearId
  │   ├── $selectedSemesterId
  │   ├── $assignmentWeight (default: 20)
  │   ├── $midSemesterWeight (default: 20)
  │   ├── $endSemesterWeight (default: 60)
  │   ├── $assignmentCount (default: 3)
  │   ├── $studentScores[] // Array of student score objects
  │   ├── $isLoaded
  │   ├── $showWeightConfig // Toggle weight configuration modal
  │   └── $importFile
  │
  ├── Computed Properties
  │   ├── getTotalWeight()     // Returns sum of weights (must = 100)
  │   ├── getPassingStudents() // Count of students with total >= 50
  │   ├── getFailingStudents() // Count of students with total < 50
  │   └── getClassAverage()    // Average total score for class
  │
  ├── Methods
  │   ├── loadScoresheet()           // Loads students with existing scores
  │   ├── saveScores()               // Bulk save all scores with calculations
  │   ├── updateScore($studentId, $field, $value) // Real-time update
  │   ├── calculateStudentTotal($studentIndex)    // Calculate weighted total
  │   ├── calculateAssignmentAverage($studentIndex) // Average assignments
  │   ├── determineGrade($totalScore) // Get letter grade from total
  │   ├── recalculateAllScores()     // Recalculate after weight change
  │   ├── saveWeightConfiguration()  // Save custom weights
  │   ├── addAssignmentColumn()      // Increase assignment count
  │   ├── removeAssignmentColumn()   // Decrease assignment count
  │   ├── downloadExcelTemplate()    // Generate template with student list
  │   ├── importFromExcel()          // Import scores from uploaded file
  │   ├── validateImportData($data)  // Validate imported scores
  │   ├── previewImport()            // Show import preview before saving
  │   ├── confirmImport()            // Save validated import data
  │   ├── exportToExcel()            // Export in institutional format
  │   ├── exportToPDF()              // Export formatted PDF
  │   └── validateWeights()          // Ensure weights sum to 100
  │
  ├── Validation Rules
  │   ├── assignment_*_score: nullable|numeric|min:0|max:100
  │   ├── mid_semester_score: nullable|numeric|min:0|max:100
  │   ├── end_semester_score: nullable|numeric|min:0|max:100
  │   ├── assignment_weight: required|numeric|min:0|max:100
  │   ├── mid_semester_weight: required|numeric|min:0|max:100
  │   ├── end_semester_weight: required|numeric|min:0|max:100
  │   └── weights_sum: required|in:100 // Custom validation
  │
  └── Listeners
      ├── scoreUpdated        // Triggered when score changes
      ├── weightsChanged      // Triggered when weights modified
      └── assignmentAdded     // Triggered when assignment column added
```

### 3.3 Livewire Component Code (Skeleton)

```php
<?php

namespace App\Livewire\Admin;

use App\Models\AssessmentScore;
use App\Models\Student;
use App\Models\Subject;
use App\Models\CollegeClass;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class AssessmentScores extends Component
{
    use WithFileUploads;

    // Filter properties
    public $selectedCourseId = null;
    public $selectedClassId = null;
    public $selectedAcademicYearId = null;
    public $selectedSemesterId = null;

    // Assessment details
    public $assessmentType = 'quiz';
    public $assessmentTitle = '';
    public $assessmentDate = null;
    public $totalMarks = 100;

    // Scoresheet data
    public $studentScores = [];
    public $isLoaded = false;

    // Import/Export
    public $importFile = null;

    public function mount()
    {
        $this->assessmentDate = now()->format('Y-m-d');
    }

    public function loadScoresheet()
    {
        $this->validate([
            'selectedCourseId' => 'required',
            'selectedClassId' => 'required',
            'totalMarks' => 'required|numeric|min:1',
            'assessmentDate' => 'required|date',
        ]);

        // Load students for the selected class
        $students = Student::where('college_class_id', $this->selectedClassId)
            ->orderBy('student_id')
            ->get();

        $this->studentScores = [];

        foreach ($students as $student) {
            // Check for existing score
            $existingScore = AssessmentScore::where([
                'course_id' => $this->selectedCourseId,
                'student_id' => $student->id,
                'assessment_title' => $this->assessmentTitle,
                'assessment_date' => $this->assessmentDate,
            ])->first();

            $this->studentScores[] = [
                'student_id' => $student->id,
                'student_number' => $student->student_id,
                'student_name' => $student->name,
                'score' => $existingScore ? $existingScore->score : null,
                'percentage' => $existingScore ? $existingScore->percentage : null,
                'existing_id' => $existingScore ? $existingScore->id : null,
            ];
        }

        $this->isLoaded = true;
    }

    public function saveScores()
    {
        foreach ($this->studentScores as $studentScore) {
            if ($studentScore['score'] === null || $studentScore['score'] === '') {
                continue; // Skip empty scores
            }

            $data = [
                'course_id' => $this->selectedCourseId,
                'student_id' => $studentScore['student_id'],
                'assessment_type' => $this->assessmentType,
                'assessment_title' => $this->assessmentTitle,
                'score' => $studentScore['score'],
                'total_marks' => $this->totalMarks,
                'percentage' => ($studentScore['score'] / $this->totalMarks) * 100,
                'assessment_date' => $this->assessmentDate,
                'recorded_by' => Auth::id(),
            ];

            if ($studentScore['existing_id']) {
                AssessmentScore::find($studentScore['existing_id'])->update($data);
            } else {
                AssessmentScore::create($data);
            }
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Scores saved successfully!',
        ]);
    }

    public function render()
    {
        $courses = Subject::orderBy('name')->get();
        $collegeClasses = CollegeClass::orderBy('name')->get();

        return view('livewire.admin.assessment-scores', [
            'courses' => $courses,
            'collegeClasses' => $collegeClasses,
        ]);
    }
}
```

---

## 4. Features Comparison

| Feature | Current (Offline Exams) | Proposed (Assessment Scores) |
|---------|-------------------------|------------------------------|
| **Setup Complexity** | High (8 required fields) | Low (Select course & class) |
| **Workflow Steps** | 2 (Create exam → Add scores) | 1 (Direct score entry) |
| **Learning Curve** | Steep | Gentle |
| **Score Entry Speed** | Slow (modal-based) | Fast (spreadsheet-like inline) |
| **Multiple Assessments** | Not supported | 3+ assignments per course |
| **Dynamic Weighting** | Fixed | Configurable (20-20-60, 20-10-70, etc.) |
| **Auto-Calculation** | Manual | Real-time (assignments, totals, grades) |
| **Bulk Operations** | Limited | Comprehensive |
| **Excel Import/Export** | Export only | Both import & export |
| **Institutional Format** | Generic | Matches snapshot format |
| **Duplicate Prevention** | Manual | Automatic (unique constraint) |
| **Mobile Friendly** | No | Yes (responsive tables) |
| **Search/Filter** | Basic | Advanced (by grade, status, name) |
| **Transcript Integration** | Yes | Yes (improved with weighted scores) |

---

## 5. Grading Calculation Logic

### 5.1 Score Calculation Flow

```
Step 1: Calculate Assignment Average
─────────────────────────────────────
Input: assignment_1_score, assignment_2_score, assignment_3_score, ...
Filter: Only include non-null assignments
Formula: assignment_average = SUM(assignments) / COUNT(non_null_assignments)

Example:
  Assignment 1: 85
  Assignment 2: 90
  Assignment 3: 80
  Assignment 4: NULL (not entered)
  
  assignment_average = (85 + 90 + 80) / 3 = 85.0

Step 2: Calculate Weighted Scores
──────────────────────────────────
assignment_weighted = assignment_average × (assignment_weight / 100)
mid_semester_weighted = mid_semester_score × (mid_semester_weight / 100)
end_semester_weighted = end_semester_score × (end_semester_weight / 100)

Example (using 20-20-60 weights):
  assignment_weighted = 85.0 × 0.20 = 17.0
  mid_semester_weighted = 75.0 × 0.20 = 15.0
  end_semester_weighted = 82.0 × 0.60 = 49.2

Step 3: Calculate Total Score
──────────────────────────────
total_score = assignment_weighted + mid_semester_weighted + end_semester_weighted

Example:
  total_score = 17.0 + 15.0 + 49.2 = 81.2

Step 4: Determine Grade
───────────────────────
Based on total_score and institutional grading scale:
  
  IF total_score >= 80 THEN grade = 'A'
  ELSE IF total_score >= 75 THEN grade = 'B+'
  ELSE IF total_score >= 70 THEN grade = 'B'
  ELSE IF total_score >= 65 THEN grade = 'C+'
  ELSE IF total_score >= 60 THEN grade = 'C'
  ELSE IF total_score >= 55 THEN grade = 'D+'
  ELSE IF total_score >= 50 THEN grade = 'D'
  ELSE grade = 'E'
  
  is_passed = (total_score >= 50)

Example:
  total_score = 81.2
  grade_letter = 'B+' (because 75 <= 81.2 < 80)
  is_passed = TRUE
```

### 5.2 Edge Cases

**Case 1: Missing Assignments**
```
Assignment 1: 85
Assignment 2: NULL
Assignment 3: 80
Mid-Sem: 75
End-Sem: 82

assignment_average = (85 + 80) / 2 = 82.5  // Only count entered assignments
assignment_weighted = 82.5 × 0.20 = 16.5
// ... rest of calculation proceeds normally
```

**Case 2: Missing Mid-Semester Score**
```
Option A: Weight Redistribution (Recommended)
  If mid_semester_score is NULL:
    - Redistribute mid_semester_weight to end_semester_weight
    - end_semester_weight_adjusted = end_semester_weight + mid_semester_weight
    
  Example:
    assignment_weight = 20%
    mid_semester_weight = 20% (not entered, redistribute)
    end_semester_weight = 60% + 20% = 80%

Option B: Proportional Calculation
  Calculate total from only entered components:
    total = (assignment_weighted + end_semester_weighted) / (assignment_weight + end_semester_weight) × 100
```

**Case 3: Zero Score vs NULL**
```
NULL = Not yet entered (excluded from calculations)
0.0 = Failed/Absent (included in calculations)

Example:
  Assignment 1: 0 (failed attempt, counts as zero)
  Assignment 2: NULL (not yet entered, excluded)
  Assignment 3: 85
  
  assignment_average = (0 + 85) / 2 = 42.5
```

**Case 4: Different Weight Configurations**
```
Configuration A: 20-20-60 (Default)
  Assignments: 20%, Mid-Sem: 20%, End-Sem: 60%

Configuration B: 20-10-70 (Heavier final exam)
  Assignments: 20%, Mid-Sem: 10%, End-Sem: 70%

Configuration C: 30-20-50 (More continuous assessment)
  Assignments: 30%, Mid-Sem: 20%, End-Sem: 50%

System must validate: assignment_weight + mid_semester_weight + end_semester_weight = 100
```

### 5.3 Database Calculations

**Using Laravel Model Accessors:**
```php
// In AssessmentScore model

public function getAssignmentAverageAttribute()
{
    $scores = array_filter([
        $this->assignment_1_score,
        $this->assignment_2_score,
        $this->assignment_3_score,
        $this->assignment_4_score,
        $this->assignment_5_score,
    ], fn($score) => $score !== null);
    
    return count($scores) > 0 ? array_sum($scores) / count($scores) : null;
}

public function getAssignmentWeightedAttribute()
{
    return $this->assignment_average ? 
        ($this->assignment_average * $this->assignment_weight / 100) : 0;
}

public function getMidSemesterWeightedAttribute()
{
    return $this->mid_semester_score ? 
        ($this->mid_semester_score * $this->mid_semester_weight / 100) : 0;
}

public function getEndSemesterWeightedAttribute()
{
    return $this->end_semester_score ? 
        ($this->end_semester_score * $this->end_semester_weight / 100) : 0;
}

public function getTotalScoreAttribute()
{
    return round(
        $this->assignment_weighted + 
        $this->mid_semester_weighted + 
        $this->end_semester_weighted,
        2
    );
}

public function getGradeLetterAttribute()
{
    $total = $this->total_score;
    
    if ($total >= 80) return 'A';
    if ($total >= 75) return 'B+';
    if ($total >= 70) return 'B';
    if ($total >= 65) return 'C+';
    if ($total >= 60) return 'C';
    if ($total >= 55) return 'D+';
    if ($total >= 50) return 'D';
    return 'E';
}

public function getIsPassedAttribute()
{
    return $this->total_score >= 50;
}
```

---

## 6. Success Metrics

### 5.1 User Experience
- **Setup Time:** Reduce from 5 minutes to 30 seconds
- **Score Entry Time:** Reduce by 60% (modal → inline editing)
- **Error Rate:** Reduce duplicate entries by 90%
- **User Satisfaction:** Target 90%+ approval rating

### 5.2 Technical Performance
- **Page Load Time:** < 2 seconds
- **Bulk Save Performance:** Handle 100+ students in < 5 seconds
- **Database Queries:** Optimize to < 10 queries per page load

### 5.3 Adoption Metrics
- **Active Users:** 100% of lecturers using new module within 1 month
- **Data Accuracy:** 99%+ score accuracy
- **Support Tickets:** Reduce by 70%

---

## 6. Risk Assessment & Mitigation

### 6.1 Risks

| Risk | Severity | Mitigation Strategy |
|------|----------|---------------------|
| Data loss during migration | High | - Backup before migration<br>- Test on staging<br>- Keep old tables archived |
| User resistance to change | Medium | - Phased rollout<br>- Training sessions<br>- Keep old module accessible temporarily |
| Transcript calculation errors | High | - Extensive testing<br>- Parallel run period<br>- Automated tests |
| Performance issues with large classes | Medium | - Pagination<br>- Lazy loading<br>- Database indexing |

### 6.2 Rollback Plan
1. Keep old tables untouched for 3 months
2. Maintain old routes with deprecation warning
3. Document rollback procedure
4. Train admin staff on rollback process

---

## 7. Timeline & Milestones

### Week 1: Planning & Setup
- [ ] Review and approve specification
- [ ] Create new database migration
- [ ] Set up development environment
- [ ] Create component skeleton

### Week 2: Core Development
- [ ] Build Livewire component
- [ ] Implement scoresheet loading
- [ ] Implement inline score entry
- [ ] Add validation and error handling
- [ ] Build auto-calculation logic (assignments, totals, grades)
### Week 3: Advanced Features
- [ ] Implement Excel template download
- [ ] Implement Excel import with validation
- [ ] Implement import preview and confirmation
- [ ] Implement Excel/PDF export
- [ ] Add filtering and search
- [ ] Build mobile-responsive UI
- [ ] Implement weight configuration modalsive UI
- [ ] Implement auto-save

### Week 4: Integration & Testing
- [ ] Update TranscriptService
- [ ] Update navigation and routes
- [ ] Write automated tests
- [ ] Perform user acceptance testing

### Week 5: Migration & Deployment
- [ ] Run data migration
- [ ] Deploy to staging
- [ ] Conduct user training
- [ ] Deploy to production

### Week 6: Monitoring & Refinement
- [ ] Monitor usage and errors
- [ ] Collect user feedback
- [ ] Make refinements
- [ ] Archive old module

---

## 8. Required Resources

### 8.1 Development Team
- **Backend Developer:** 40 hours
- **Frontend Developer:** 20 hours
- **UI/UX Designer:** 10 hours
- **QA Tester:** 15 hours

### 8.2 Infrastructure
- Database: Existing MySQL
- Storage: No additional requirements
- Caching: Redis (existing)

### 8.3 Third-Party Libraries
- Livewire 3 (existing)
- Maatwebsite Excel (existing)
- Alpine.js (existing)
- Bootstrap 5 (existing)

---

## 9. Future Enhancements (Post-MVP)

### Phase 2 Features:
1. **Assessment Templates:**
   - Save assessment configurations for reuse
   - Common assessment types per course

2. **Grade Statistics:**
   - Class average, median, mode
   - Performance distribution charts
   - Grade trends over time

3. **Student Portal:**
   - Students view their own scores
   - Score history per course
   - Performance analytics

4. **Notifications:**
   - Email/SMS when scores are published
   - Remind lecturers to record scores

5. **Advanced Analytics:**
   - Compare class performance
   - Identify struggling students
   - Generate intervention reports

6. **Mobile App:**
   - Native iOS/Android app
   - Offline score recording
   - Sync when online

---

## 10. Documentation & Training

### 10.1 User Documentation
- [ ] User guide with screenshots
- [ ] Video tutorials (3-5 minutes each)
- [ ] FAQs document
- [ ] Quick reference card

### 10.2 Technical Documentation
- [ ] API documentation
- [ ] Database schema documentation
- [ ] Component architecture diagram
- [ ] Deployment guide

### 10.3 Training Materials
- [ ] Lecturer training session (1 hour)
- [ ] Admin training session (30 minutes)
- [ ] Support staff training (45 minutes)

---

## 11. Questions for Clarification

Before implementation, please provide feedback on:

1. **Assignment Count Flexibility:** Should we enforce a maximum number of assignments (e.g., 10) or allow unlimited assignments?

2. **Grading Scale Configuration:** Should the grading scale (A=80-100, B+=75-79, etc.) be configurable per institution or fixed?

3. **Permissions:** Who should be able to edit scores after submission? Only the creator, or admins and academic officers too?

4. **Weight Presets:** Should we provide weight presets (20-20-60, 20-10-70, 30-20-50) or allow completely custom weights?

5. **Academic Year/Semester:** Should these be automatically detected based on current date or manually selected?

6. **Audit Trail:** Should we keep a history of score changes (who changed what and when) for accountability?

7. **Partial Scores:** How should incomplete scoresheets be handled? Allow saving with missing scores or enforce completion?

8. **Absent vs Zero:** Should we differentiate between absent (no score) and zero score (failed attempt)?

9. **Mid-Semester Optional:** Some courses may not have mid-semester exams. Should this field be optional with weight redistribution?

10. **Publication Control:** Should scores be hidden from students until explicitly published by the lecturer?

11. **Module Naming:** Do you prefer "Continuous Assessment Scores" or "Assessment Scores" or another name?

12. **Grade Point Scale:** Should we support 4.0 scale (A=4.0, B+=3.5, etc.) for GPA calculation?

13. **Rounding Rules:** Should total scores be rounded to 1 decimal place, 2 decimal places, or whole numbers?

14. **Bulk Edit:** Should we allow editing multiple students' scores for a single assessment column at once?

15. **Score Freeze:** Should there be a deadline after which scores cannot be edited without admin approval?

---

## 12. Appendices

### Appendix A: Current File Locations
```
app/Livewire/Admin/
  ├── OfflineExams.php
  └── OfflineExamScores.php

app/Models/
  ├── OfflineExam.php
  └── OfflineExamScore.php

database/migrations/
  ├── 2025_07_14_001800_create_offline_exams_table.php
  └── 2025_07_14_001801_create_offline_exam_scores_table.php

resources/views/
  ├── admin/exam/offline-exams.blade.php
  ├── admin/exam/offline-scores.blade.php
  └── livewire/admin/
      ├── offline-exams.blade.php
      └── offline-exam-scores.blade.php

routes/web.php (lines 663-670)
```

### Appendix B: Database Relationships
```
students (id) ←─── assessment_scores (student_id)
subjects (id) ←─── assessment_scores (course_id)
users (id)    ←─── assessment_scores (recorded_by)
academic_years (id) ←─── assessment_scores (academic_year_id)
semesters (id) ←─── assessment_scores (semester_id)
```

### Appendix C: Related Services
```
app/Services/
  ├── TranscriptService.php (Lines 127, 208)
### Appendix D: Excel Import/Export Functionality

#### D.1 Excel Import Workflow

```
Step 1: Download Template
─────────────────────────
User clicks "📥 Download Excel Template" button
  ↓
System generates Excel file with:
  - Course/Class/Semester/Academic Year in header rows
  - Pre-populated student list (INDEX NO, STUDENT NAME)
  - Empty columns for scores (ASSIGNMENT 1, 2, 3, MID-SEM, END-SEM)
  - Instructions sheet with grading scale and weight information
  - Data validation rules (scores must be 0-100)

Step 2: Offline Score Entry
────────────────────────────
Lecturer opens downloaded Excel file
  ↓
Fills in scores offline (supports copy-paste from other sources)
  ↓
Saves file

Step 3: Upload and Validate
────────────────────────────
User clicks "📥 Import from Excel" button
**Excel Import Template (Simplified for data entry):**
```
Row 1-3: Header Information (read-only, grayed out)
Row 4: Column Headers
Row 5+: Student Data

┌──────┬────────────┬──────────────────┬──────────────┬──────────────┬──────────────┬─────────┬─────────┐
│ S/N  │ INDEX NO   │ STUDENT NAME     │ ASSIGNMENT 1 │ ASSIGNMENT 2 │ ASSIGNMENT 3 │ MID-SEM │ END-SEM │
│      │ (Required) │ (Auto-filled)    │ (0-100)      │ (0-100)      │ (0-100)      │ (0-100) │ (0-100) │
├──────┼────────────┼──────────────────┼──────────────┼──────────────┼──────────────┼─────────┼─────────┤
│  1   │MHIAFRG001  │ RACHEAL OFORI    │              │              │              │         │         │
│  2   │MHIAFRG002  │ HUDA YAHYA       │              │              │              │         │         │
│  3   │MHIAFRG003  │ LYDIA LARWEH     │              │              │              │         │         │
│  4   │MHIAFRG004  │ EMELIA KWETEY    │              │              │              │         │         │
└──────┴────────────┴──────────────────┴──────────────┴──────────────┴──────────────┴─────────┴─────────┘

Cell Formatting:
- INDEX NO & STUDENT NAME: Locked (cannot be edited)
- Score columns: Data validation (0-100, decimal allowed)
- Empty cells: White background
- Invalid data: Red background with error tooltip
```

**Sheet 2: Instructions**
```
CONTINUOUS ASSESSMENT SCORE IMPORT INSTRUCTIONS
===============================================

1. DO NOT modify the INDEX NO or STUDENT NAME columns
2. Enter scores in the range 0-100 (decimals allowed, e.g., 85.5)
3. Leave cells empty if score not yet available (do not enter 0 unless student scored zero)
4. Empty cells will not overwrite existing scores in the system
5. To mark a student as absent, enter 0

GRADING SCALE:
A:  80-100%
B+: 75-79%
B:  70-74%
C+: 65-69%
C:  60-64%
D+: 55-59%
D:  50-54%
E:  0-49% (Fail)

WEIGHT CONFIGURATION:
Assignments: 20% (average of all assignments)
Mid-Semester: 20%
End-of-Semester: 60%

CALCULATION EXAMPLE:
Student: RACHEAL OFORI
Assignment 1: 85, Assignment 2: 90, Assignment 3: 80
Assignment Average: (85+90+80)/3 = 85.0
Assignment Weighted: 85.0 × 20% = 17.0

Mid-Semester: 75
Mid-Sem Weighted: 75 × 20% = 15.0

End-Semester: 82
End-Sem Weighted: 82 × 60% = 49.2

TOTAL: 17.0 + 15.0 + 49.2 = 81.2%
GRADE: B+ (because 75 ≤ 81.2 < 80)

TROUBLESHOOTING:
- Red cells: Invalid data (check value is between 0-100)
- Upload fails: Ensure you haven't modified locked columns
- Missing students: Download fresh template from system
```

#### D.3 Import Validation Rules

```php
// Validation logic in AssessmentScoresImport class

public function rules(): array
{
    return [
        '*.index_no' => ['required', 'exists:students,student_id'],
        '*.assignment_1' => ['nullable', 'numeric', 'min:0', 'max:100'],
        '*.assignment_2' => ['nullable', 'numeric', 'min:0', 'max:100'],
        '*.assignment_3' => ['nullable', 'numeric', 'min:0', 'max:100'],
        '*.mid_semester' => ['nullable', 'numeric', 'min:0', 'max:100'],
        '*.end_semester' => ['nullable', 'numeric', 'min:0', 'max:100'],
    ];
}

public function customValidationMessages(): array
{
    return [
        '*.index_no.required' => 'Student INDEX NO is required (Row :row)',
        '*.index_no.exists' => 'Student :value not found in database (Row :row)',
        '*.assignment_1.numeric' => 'Assignment 1 must be a number (Row :row)',
        '*.assignment_1.max' => 'Assignment 1 cannot exceed 100 (Row :row)',
        // ... similar messages for other fields
    ];
}

public function withValidator($validator)
{
    $validator->after(function ($validator) {
        // Custom validation: Check for duplicate entries
        $indexNumbers = collect($this->rows)->pluck('index_no');
        $duplicates = $indexNumbers->duplicates();
        
        if ($duplicates->isNotEmpty()) {
            $validator->errors()->add('duplicates', 
                'Duplicate INDEX NO found: ' . $duplicates->implode(', ')
            );
        }
    });
}
```

#### D.4 Import Preview Interface

```
┌────────────────────────────────────────────────────────────────────────┐
│  📋 Import Preview - Review Before Saving                              │
├────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  File: assessment_scores_2025.xlsx                                     │
│  Uploaded: Dec 27, 2025 10:30 AM                                       │
│                                                                         │
│  Summary:                                                              │
│  ✓ 45 valid records found                                              │
│  ⚠ 3 students with existing scores (will be updated)                  │
│  ℹ 2 students with incomplete data (will save partial scores)         │
│                                                                         │
│  ┌──────────────────────────────────────────────────────────────────┐ │
│  │ INDEX NO   │ Name          │ Action │ Changes                     │ │
│  ├────────────┼───────────────┼────────┼─────────────────────────────┤ │
│  │ MHIAFRG001 │ RACHEAL OFORI │ UPDATE │ Assign1:85→90, MidSem:75→80 │ │
│  │ MHIAFRG002 │ HUDA YAHYA    │ NEW    │ All scores entered          │ │
│  │ MHIAFRG003 │ LYDIA LARWEH  │ NEW    │ Missing End-Sem (partial)   │ │
│  └────────────┴───────────────┴────────┴─────────────────────────────┘ │
│                                                                         │
│  Options:                                                              │
│  [✓] Update existing scores                                            │
│  [✓] Skip students with errors                                         │
│  [ ] Send email notification to students                               │
│                                                                         │
│  [✅ Confirm & Save]  [❌ Cancel]  [📄 Download Full Preview]          │
│                                                                         │
└────────────────────────────────────────────────────────────────────────┘
```

#### D.5 Import Error Handling

**Error Report Display:**
```
❌ Import Failed - Please Fix Errors Below

Errors Found: 5

Row 12: MHIAFRG234 - Student not found in database
  → Solution: Verify INDEX NO is correct

Row 15: Assignment 1 score "ABC" is invalid
  → Solution: Enter numeric value between 0-100

Row 23: Mid-Semester score 150 exceeds maximum
  → Solution: Score must be between 0-100

Row 31: Duplicate INDEX NO "MHIAFRG005"
  → Solution: Remove duplicate entry

Row 45: INDEX NO is missing
  → Solution: Ensure INDEX NO column is filled

[📥 Download Detailed Error Report]  [🔄 Fix and Re-upload]  [❌ Cancel]
```

**Notes:**
- System auto-calculates weighted scores, totals, and grades on import
- Empty cells treated as not yet entered (not zero)
- Decimal scores supported (e.g., 85.5)
- Export includes both raw scores and calculated values for transparency
- Import maintains audit trail (who imported, when, what changed)
Step 4: Preview Import
──────────────────────
System displays preview table showing:
  - Students to be updated (with existing scores highlighted)
  - New scores to be imported
  - Calculated totals and grades
  - Change summary (X new scores, Y updates)
  ↓
User reviews and can:
  - Confirm import (proceed to Step 5)
  - Cancel (discard import)
  - Download error report (if any warnings)

Step 5: Confirm and Save
─────────────────────────
User clicks "Confirm Import"
  ↓
System:
  - Saves scores to database
  - Calculates weighted scores, totals, grades
  - Updates timestamps and recorded_by
  - Logs import action for audit trail
  ↓
Success message with summary:
  "Successfully imported scores for 45 students:
   - 30 new records created
   - 15 existing records updated"
```

#### D.2 Excel Template Structure

**Sheet 1: Score Entry (Main Sheet)**

```
COURSE NAME: [Course Name]          COURSE CODE: [Code]    SEMESTER: [First/Second]
PROGRAMME: [Program Name]           CLASS: [Class Name]    ACADEMIC YEAR: [2025/2026]
```
COURSE NAME: [Course Name]          COURSE CODE: [Code]    SEMESTER: [First/Second]
PROGRAMME: [Program Name]           CLASS: [Class Name]    ACADEMIC YEAR: [2025/2026]

┌─────┬─────────────┬──────────────────┬──────────────────────────────────────────────────────────────────┐
│ S/N │ INDEX NO    │ STUDENT NAME     │           ASSIGNMENT          │ MID-SEM │  SEM EXAMS │ TOTAL │ GRADE │
│     │             │                  │  100%  │  100%  │  100%  │ 20%  │  100%   │   100%     │ 100%  │ GRADE │
│     │             │                  │        │        │        │      │   20%   │    60%     │       │       │
├─────┼─────────────┼──────────────────┼────────┼────────┼────────┼──────┼─────────┼────────────┼───────┼───────┤
│  1  │ MHIAFRG001  │ RACHEAL OFORI    │  85.0  │  90.0  │  80.0  │ 17.0 │  75.0   │   82.0     │  81.2 │  B+   │
│     │             │                  │        │        │        │ 15.0 │         │   49.2     │       │       │
├─────┼─────────────┼──────────────────┼────────┼────────┼────────┼──────┼─────────┼────────────┼───────┼───────┤
│  2  │ MHIAFRG002  │ HUDA YAHYA       │  92.0  │  88.0  │  95.0  │ 18.3 │  85.0   │   90.0     │  89.0 │   A   │
│     │             │                  │        │        │        │ 17.0 │         │   54.0     │       │       │
├─────┼─────────────┼──────────────────┼────────┼────────┼────────┼──────┼─────────┼────────────┼───────┼───────┤
│  3  │ MHIAFRG003  │ LYDIA LARWEH     │  78.0  │  82.0  │  75.0  │ 15.7 │  70.0   │   68.0     │  70.8 │  B-   │
│     │             │                  │        │        │        │ 14.0 │         │   40.8     │       │       │
└─────┴─────────────┴──────────────────┴────────┴────────┴────────┴──────┴─────────┴────────────┴───────┴───────┘

Weights: Assignments (20%), Mid-Semester (20%), End-of-Semester (60%)
Grading Scale: A (80-100), B+ (75-79), B (70-74), C+ (65-69), C (60-64), D+ (55-59), D (50-54), E (<50)
```

**Excel Import Template (Simplified for data entry):**
```csv
INDEX NO,STUDENT NAME,ASSIGNMENT 1,ASSIGNMENT 2,ASSIGNMENT 3,MID-SEM,END-SEM
MHIAFRG001,RACHEAL OFORI,85,90,80,75,82
MHIAFRG002,HUDA YAHYA,92,88,95,85,90
MHIAFRG003,LYDIA LARWEH,78,82,75,70,68
```

**Notes:**
- System auto-calculates weighted scores, totals, and grades on import
- Empty cells treated as not yet entered (not zero)
- Decimal scores supported (e.g., 85.5)
- Export includes both raw scores and calculated values for transparency

---

## Document Control

**Version:** 1.0  
**Date:** December 27, 2025  
**Author:** Development Team  
**Status:** Draft - Awaiting Review  
**Next Review:** Upon feedback from stakeholders

**Change Log:**
- v1.0 (2025-12-27): Initial specification document created

---

## Approval

**Stakeholders to Review:**
- [ ] Project Manager
- [ ] Academic Officer
- [ ] IT Director
- [ ] Finance Manager
- [ ] Sample Lecturers (3-5)

**Approval Signatures:**
- Project Manager: _________________ Date: _______
- IT Director: _________________ Date: _______
- Academic Officer: _________________ Date: _______

---

*This specification is a living document and may be updated based on stakeholder feedback and technical discoveries during implementation.*
