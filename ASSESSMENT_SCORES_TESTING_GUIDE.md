# Assessment Scores Module - Testing Guide

## Overview
This guide provides step-by-step instructions for testing all features of the Assessment Scores module across different user roles.

---

## Prerequisites
- Access to the system with different role accounts:
  - Administrator
  - Academic Officer
  - Finance Manager
  - System
  - Student (to verify no access)
- A test course with enrolled students
- Valid academic year and semester data

---

## Test 1: Role-Based Access Control

### Test 1.1: Authorized Roles Can Access
**Expected Roles**: Administrator, Super Admin, Academic Officer, System, Finance Manager

**Steps**:
1. Log in as **Administrator**
2. Navigate to Assessment Scores module via sidebar menu
3. Verify page loads successfully ✓

**Repeat for**:
- Academic Officer ✓
- Finance Manager ✓
- System ✓

**Expected Result**: All authorized roles can access the module without errors.

---

### Test 1.2: Student Role Cannot Access
**Steps**:
1. Log in as **Student**
2. Check sidebar menu
3. Try to access `/assessment-scores` directly via URL

**Expected Result**: 
- Menu item not visible in sidebar
- Direct URL access returns 403 Forbidden or redirects

---

## Test 2: Load Scoresheet Functionality

### Test 2.1: Load Scoresheet with Valid Filters
**Steps**:
1. Log in as Administrator
2. Navigate to Assessment Scores
3. Select:
   - Course: Any valid course
   - Class: Any valid class
   - Academic Year: Current academic year
   - Semester: Current semester
4. Click "Load Scoresheet"

**Expected Result**:
- Success message: "X students loaded successfully"
- Student list appears in table
- All students from selected class are shown
- Columns visible: Index No, Student Name, Assign 1-3, Mid-Sem, End-Sem, Total, Grade

---

### Test 2.2: Load Scoresheet with Existing Scores
**Steps**:
1. Load scoresheet for a course where scores already exist
2. Verify existing scores are pre-filled in the table

**Expected Result**:
- Existing scores appear in correct columns
- Total and Grade columns show calculated values
- "Existing record" indicator present (existing_id populated)

---

### Test 2.3: Validation on Load
**Steps**:
1. Try to load scoresheet without selecting Course
2. Try without selecting Class
3. Try without Academic Year
4. Try without Semester

**Expected Result**: Each attempt shows appropriate error message requiring the missing field.

---

## Test 3: Score Entry and Auto-Calculation

### Test 3.1: Enter Scores with Auto-Calculation
**Steps**:
1. Load a scoresheet
2. Enter scores for one student:
   - Assignment 1: 85
   - Assignment 2: 90
   - Assignment 3: 80
   - Mid-Semester: 75
   - End-Semester: 82
3. Press Tab or click outside each field

**Expected Result**:
- No field resets while typing
- After saving, Total: 81.2 (approximately)
- Grade: B+
- Calculations use default weights (20-20-60)

---

### Test 3.2: Handle Empty and Zero Scores
**Steps**:
1. Enter scores for another student:
   - Assignment 1: 0 (failed)
   - Assignment 2: (leave empty)
   - Assignment 3: 85
   - Mid-Semester: 70
   - End-Semester: 68
2. Save scores

**Expected Result**:
- Empty Assignment 2 excluded from average
- Assignment average: (0 + 85) / 2 = 42.5
- Zero score included in calculation
- No SQL errors on save

---

### Test 3.3: Decimal Scores
**Steps**:
1. Enter decimal scores:
   - Assignment 1: 85.5
   - Assignment 2: 90.75
   - Assignment 3: 88.25

**Expected Result**: 
- Decimal values accepted
- Calculations handle decimals correctly
- No rounding errors

---

### Test 3.4: Validation Limits
**Steps**:
1. Try entering Assignment 1: 101
2. Try entering Mid-Semester: -5
3. Try entering End-Semester: "ABC"

**Expected Result**: 
- Values > 100 rejected
- Negative values rejected
- Non-numeric values rejected
- Appropriate error messages shown

---

## Test 4: Weight Configuration

### Test 4.1: Configure Custom Weights
**Steps**:
1. Load scoresheet
2. Click "Configure Weights" button
3. Change weights to:
   - Assignment: 30%
   - Mid-Semester: 20%
   - End-Semester: 50%
4. Click "Save Configuration"

**Expected Result**:
- Weight modal closes
- Badge displays updated weights
- All scores recalculate with new weights
- Success message appears

---

### Test 4.2: Weight Validation
**Steps**:
1. Click "Configure Weights"
2. Try weights that don't sum to 100%:
   - Assignment: 25%
   - Mid-Semester: 25%
   - End-Semester: 60%
3. Click "Save Configuration"

**Expected Result**: 
- Error message: "Weights must sum to 100%. Current total: 110%"
- Configuration not saved
- Modal remains open

---

## Test 5: Dynamic Assignment Columns

### Test 5.1: Add Assignment Column
**Steps**:
1. Load scoresheet (default: 3 assignments)
2. Click "+" button to add assignment column
3. Verify Assignment 4 column appears
4. Click "+" again
5. Verify Assignment 5 column appears

**Expected Result**:
- Assignment 4 column added with input fields
- Assignment 5 column added with input fields
- Badge shows "Assignments (4)" then "Assignments (5)"
- "+" button disabled at 5 assignments
- Info message: "Assignment X column added"

---

### Test 5.2: Remove Assignment Column
**Steps**:
1. With 5 assignments visible, click "-" button
2. Verify Assignment 5 column removed
3. Click "-" again
4. Verify Assignment 4 column removed

**Expected Result**:
- Columns hidden correctly
- Badge shows updated count
- Scores recalculated excluding removed assignments
- "-" button disabled at 3 assignments
- Info message: "Assignment column removed and scores recalculated"

---

### Test 5.3: Enter Scores for 5 Assignments
**Steps**:
1. Add 2 assignment columns (total 5)
2. Enter scores for all 5 assignments:
   - Assignment 1: 85
   - Assignment 2: 90
   - Assignment 3: 80
   - Assignment 4: 88
   - Assignment 5: 92
3. Enter Mid-Sem: 75, End-Sem: 82
4. Save

**Expected Result**:
- All 5 assignments saved correctly
- Assignment average: (85+90+80+88+92)/5 = 87
- Total calculated correctly
- assignment_count = 5 in database

---

## Test 6: Bulk Save Operation

### Test 6.1: Save Multiple Students
**Steps**:
1. Load scoresheet with 10+ students
2. Enter scores for 5 students (various columns)
3. Click "Save All Scores"

**Expected Result**:
- Success message: "X new records saved, Y records updated"
- Page doesn't reload/reset
- Totals and grades update immediately
- No loss of unsaved data for other students

---

### Test 6.2: Update Existing Scores
**Steps**:
1. Load scoresheet with existing scores
2. Modify scores for 2-3 students
3. Save

**Expected Result**:
- Success message indicates updates
- Modified scores saved correctly
- Unmodified scores remain unchanged
- Total and grade recalculated for modified students

---

## Test 7: Excel Template Download

### Test 7.1: Download Template
**Steps**:
1. Select Course, Class, Academic Year, Semester
2. Click "Download Excel Template"
3. Open downloaded file

**Expected Result**:
- File downloads as: `assessment_scores_template_[CourseName]_[Date].xlsx`
- Sheet 1 "Score Entry" contains:
  - Header with course info
  - Pre-populated student list (Index No, Name)
  - Empty score columns (Assign 1-3, Mid-Sem, End-Sem)
  - Data validation on score columns (0-100)
  - Locked student identification columns
- Sheet 2 "Instructions" contains:
  - Grading scale
  - Weight configuration
  - Calculation examples
  - Troubleshooting tips

---

### Test 7.2: Template Data Protection
**Steps**:
1. Open downloaded template
2. Try to edit Index No column
3. Try to edit Student Name column

**Expected Result**:
- Index No and Student Name columns are locked (read-only)
- Score columns are editable
- Sheet protection active (no password required)

---

## Test 8: Excel Import with Preview

### Test 8.1: Import Valid Data
**Steps**:
1. Download template
2. Fill in scores for 10 students in Excel
3. Save Excel file
4. Go back to Assessment Scores module
5. Click "Upload Excel File" and select your file
6. Click "Preview Import"

**Expected Result**:
- Import preview modal appears
- Summary shows:
  - Total rows processed
  - Valid records
  - New records count
  - Records to update count
- Preview table shows all data with action badges (NEW/UPDATE)
- No errors

---

### Test 8.2: Import with Validation Errors
**Steps**:
1. Create Excel with errors:
   - Row 5: Assignment 1 = 150 (exceeds 100)
   - Row 8: INDEX NO = "INVALID001" (non-existent student)
   - Row 12: Mid-Sem = "ABC" (non-numeric)
2. Upload and preview

**Expected Result**:
- Error alert appears listing all errors:
  - "Row 5: Assignment 1 cannot exceed 100"
  - "Row 8: Student with INDEX NO 'INVALID001' not found"
  - "Row 12: Mid-Sem must be a number"
- Preview modal does NOT appear
- No data imported

---

### Test 8.3: Confirm Import
**Steps**:
1. Import valid Excel file
2. Review preview
3. Click "Confirm & Save Import"

**Expected Result**:
- Success message: "Import completed: X new records, Y updated records"
- Scoresheet reloads with imported data
- Totals and grades calculated correctly
- Import preview closes

---

### Test 8.4: Cancel Import
**Steps**:
1. Upload and preview valid file
2. Click "Cancel" in preview modal

**Expected Result**:
- Preview modal closes
- No data imported
- No changes to existing scores

---

## Test 9: Excel Export

### Test 9.1: Export Scoresheet
**Steps**:
1. Load scoresheet with scores entered
2. Click "Export" button
3. Open downloaded file

**Expected Result**:
- File downloads as: `assessment_scores_[CourseName]_[Date].xlsx`
- Sheet 1 contains:
  - Course/Class/Semester info in header
  - All students with scores
  - Raw scores AND weighted scores
  - Assignment average column
  - Total and Grade columns
  - Professional formatting with borders
- Sheet 2 "Instructions" contains grading information

---

### Test 9.2: Export with Partial Data
**Steps**:
1. Load scoresheet
2. Enter scores for only 3 out of 20 students
3. Export

**Expected Result**:
- All 20 students appear in export
- Students with scores show values
- Students without scores show empty cells
- No errors

---

## Test 10: Statistics Display

### Test 10.1: Verify Statistics Cards
**Steps**:
1. Load scoresheet with various scores
2. Observe statistics cards at top

**Expected Result**:
- Total Students: Shows count of loaded students
- Passing Students: Shows count where Total ≥ 50
- Failing Students: Shows count where Total < 50
- Class Average: Shows average of all total scores

---

### Test 10.2: Statistics Update on Score Entry
**Steps**:
1. Load scoresheet
2. Enter scores that result in Total = 45 (failing)
3. Save
4. Check statistics

**Expected Result**: Failing Students count increments

---

## Test 11: Transcript Integration

### Test 11.1: Verify Scores Appear on Transcript
**Steps**:
1. Enter and save assessment scores for a student
2. Navigate to student's transcript
3. Generate transcript

**Expected Result**:
- Assessment score appears as "Continuous Assessment"
- Total score (weighted) displayed
- Grade letter shown correctly
- Integrated with online exam scores if present

---

### Test 11.2: Backward Compatibility
**Steps**:
1. Check transcript for student with old offline exam scores (before migration)
2. Verify transcript still shows those scores

**Expected Result**:
- Old offline exam scores still accessible
- No errors in transcript generation
- System falls back to offline_exam_scores if no assessment_scores found

---

## Test 12: Edge Cases and Error Handling

### Test 12.1: Large Class Size
**Steps**:
1. Load scoresheet for class with 100+ students
2. Scroll through table
3. Enter scores for students at various positions

**Expected Result**:
- Table renders smoothly
- Scrolling works without lag
- Save operation completes in < 10 seconds
- No memory issues

---

### Test 12.2: Concurrent User Edits
**Steps**:
1. User A loads scoresheet and enters scores
2. User B loads same scoresheet and enters different scores
3. User A saves first
4. User B saves second

**Expected Result**:
- Both saves successful
- User B's save overwrites User A's data (last write wins)
- No data corruption
- Consider: Should implement conflict detection in future

---

### Test 12.3: Session Timeout
**Steps**:
1. Load scoresheet
2. Wait for session to timeout (or clear cookies)
3. Try to save scores

**Expected Result**:
- Redirect to login page
- Alert: "Your session expired"
- No data saved

---

## Test 13: Performance Testing

### Test 13.1: Page Load Time
**Measure**: Time to load scoresheet

**Acceptable**: < 3 seconds for 50 students

**Steps**:
1. Load scoresheet with 50 students
2. Time from button click to table render

---

### Test 13.2: Save Operation Time
**Measure**: Time to save all scores

**Acceptable**: < 5 seconds for 100 students

**Steps**:
1. Enter scores for 100 students
2. Click "Save All Scores"
3. Time until success message

---

### Test 13.3: Import Time
**Measure**: Time to import and preview

**Acceptable**: < 10 seconds for 200 students

**Steps**:
1. Create Excel with 200 students
2. Upload and preview
3. Time until preview modal appears

---

## Test 14: UI/UX Testing

### Test 14.1: Responsive Design
**Steps**:
1. Access module on different screen sizes:
   - Desktop (1920x1080)
   - Laptop (1366x768)
   - Tablet (768x1024)
   - Mobile (375x667)

**Expected Result**:
- Table scrolls horizontally on small screens
- Buttons remain accessible
- No overlapping elements
- Readable text on all devices

---

### Test 14.2: Keyboard Navigation
**Steps**:
1. Load scoresheet
2. Use Tab key to navigate between assignment fields
3. Use Shift+Tab to navigate backward
4. Use Enter key after typing score

**Expected Result**:
- Tab moves to next assignment field (left to right)
- Tab from last field moves to next student's first field
- Enter/Tab triggers auto-calculation
- Smooth navigation without skipping fields

---

### Test 14.3: Visual Feedback
**Steps**:
1. Observe loading states during operations:
   - Load scoresheet
   - Save scores
   - Import preview
   - Export download

**Expected Result**:
- Spinner icons during operations
- Buttons disabled during processing
- "Loading..." or "Saving..." text
- No double-submission possible

---

## Test 15: Data Integrity

### Test 15.1: Database Validation
**Steps**:
1. Save scores for several students
2. Check database directly:
   ```sql
   SELECT * FROM assessment_scores WHERE course_id = X;
   ```

**Expected Result**:
- Records created with correct foreign keys
- Null values for empty assignments
- Weights saved correctly
- assignment_count matches UI
- recorded_by contains user ID
- Timestamps populated

---

### Test 15.2: Audit Trail
**Steps**:
1. Save scores as User A
2. Load and modify scores as User B
3. Check recorded_by field in database

**Expected Result**:
- recorded_by updated to User B on modification
- Timestamps show when last updated
- Consider: Implement full audit log table for history

---

## Test Checklist Summary

### Critical Tests (Must Pass)
- [ ] All authorized roles can access module
- [ ] Student role cannot access module
- [ ] Load scoresheet with valid data
- [ ] Enter scores without field resets
- [ ] Save scores successfully
- [ ] Auto-calculation correct for all scenarios
- [ ] Weight configuration works and recalculates
- [ ] Add/remove assignment columns
- [ ] Excel template downloads correctly
- [ ] Excel import with validation
- [ ] Excel export with all data
- [ ] Transcript integration working
- [ ] No SQL errors on empty fields

### Important Tests (Should Pass)
- [ ] Statistics cards accurate
- [ ] Validation prevents invalid data
- [ ] Large class sizes performant
- [ ] Responsive design on mobile
- [ ] Keyboard navigation smooth
- [ ] Concurrent edits handled
- [ ] Session timeout handled gracefully

### Optional Tests (Nice to Have)
- [ ] Decimal scores handled correctly
- [ ] Import error reporting clear
- [ ] Export formatting professional
- [ ] UI feedback during operations
- [ ] Database integrity maintained

---

## Reporting Issues

When reporting bugs or issues, include:
1. **Role**: Which user role were you logged in as?
2. **Steps**: Exact steps to reproduce
3. **Expected**: What should have happened?
4. **Actual**: What actually happened?
5. **Browser**: Chrome, Firefox, Safari, etc.
6. **Screenshots**: If applicable
7. **Console Errors**: Browser console (F12) errors if any

---

## Success Criteria

The module is ready for production when:
- ✅ All Critical Tests pass
- ✅ 90%+ Important Tests pass
- ✅ No data loss scenarios
- ✅ No security vulnerabilities
- ✅ Performance acceptable for largest class size
- ✅ User feedback positive

---

## Next Steps After Testing

1. **Document Findings**: Create issue list with severity levels
2. **Prioritize Fixes**: Critical > Important > Optional
3. **User Training**: Prepare training materials for lecturers
4. **Rollout Plan**: Phased rollout by department/course
5. **Monitor Usage**: Track adoption and support tickets
6. **Iterate**: Gather feedback and improve

---

**Happy Testing! 🎉**
