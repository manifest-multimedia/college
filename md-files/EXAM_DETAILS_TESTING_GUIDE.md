# Exam Details Page - Testing Guide

## Overview
This guide covers the testing steps for the new Exam Details page functionality that was implemented to fix the issue where clicking an exam in the Exam Center redirected to the Question Bank instead of showing exam details.

## What Was Fixed

### Problem
- Clicking on an exam name in the Exam Center took users directly to the Question Bank page
- URL was: `http://127.0.0.1:8000/question-bank/{slug}`
- Teachers/staff couldn't view exam details, password, or perform edit/delete actions easily

### Solution
- Created a dedicated Exam Details page
- Exam name now links to: `http://127.0.0.1:8000/exams/{slug}`
- Question Bank is still accessible via dropdown action and from the details page

## Files Changed

### New Files Created
1. **`/cis/app/Livewire/ExamDetail.php`**
   - Livewire component for displaying exam details
   - Handles view, edit, and delete operations
   - Role-based authorization (owner, admin, Super Admin)

2. **`/cis/resources/views/livewire/exam-detail.blade.php`**
   - View template with exam information display
   - Shows: course, duration, password (admin only), status, created by, total questions, question sets
   - Edit and delete modals included

### Modified Files
1. **`/cis/routes/web.php`** (line ~148)
   - Added: `Route::get('/exams/{exam}', \App\Livewire\ExamDetail::class)->name('exams.show');`

2. **`/cis/app/Models/Exam.php`** (bottom of file)
   - Added: `getRouteKeyName()` method to enable slug-based route binding

3. **`/cis/resources/views/components/partials/exam-table-row.blade.php`** (line 12)
   - Changed exam name link from `route('questionbank.with.slug')` to `route('exams.show')`

## Testing Steps

### Prerequisites
- Application must be running (`php artisan serve` or Laravel Valet)
- Login credentials for admin, Super Admin, or System user
- At least one exam must exist in the database

### Test Case 1: Navigate to Exam Details
1. **Login** as admin, Super Admin, or System user
2. **Navigate** to Exam Center (`/exam-center`)
3. **Locate** an exam in the exam list table
4. **Click** on the exam name (e.g., "Introduction to Computing")
5. **Verify**:
   - URL changes to `/exams/{exam-slug}` (not `/question-bank/{exam-slug}`)
   - Page title shows "Exam Details - [Course Name]"
   - Page displays comprehensive exam information

### Test Case 2: Verify Exam Information Display
On the Exam Details page, verify the following information is displayed:

#### Basic Information (Left Column)
- ✅ Course name and code
- ✅ Duration (in minutes)
- ✅ Questions per session
- ✅ Total questions count
- ✅ Passing percentage (if set)
- ✅ **Exam Password** (visible only to admin/Super Admin/System)

#### Schedule & Settings (Right Column)
- ✅ Start date and time
- ✅ End date and time
- ✅ Class information (Class - Year - Semester)
- ✅ Created by (teacher/staff name)
- ✅ Created on (timestamp)

#### Quick Actions
- ✅ "Manage Question Bank" button
- ✅ "Edit Details" button (admin only)
- ✅ "View Results" button (admin only)

#### Question Sets Section
- ✅ Table showing assigned question sets (if any)
- ✅ Shows: set name, total questions, questions to pick, shuffle status, difficulty

### Test Case 3: Verify Password Visibility
1. **Login** as admin, Super Admin, or System user
2. **Navigate** to exam details
3. **Verify**: Exam password is visible in the "Basic Information" section
4. **Logout** and **login** as a regular teacher (exam owner)
5. **Navigate** to the same exam
6. **Verify**: Password should NOT be visible to non-admin users

### Test Case 4: Edit Exam Functionality
1. On the exam details page, **click** "Edit Exam" button
2. **Verify**: Modal opens with editable fields:
   - Duration
   - Questions per session
   - Start date & time
   - End date & time
   - Passing percentage
   - Status (upcoming/active/completed)
3. **Make changes** to one or more fields
4. **Click** "Save Changes"
5. **Verify**:
   - Success message appears
   - Modal closes
   - Updated information reflects on the page

### Test Case 5: Delete Exam Functionality
1. On the exam details page, **click** "Delete" button
2. **Verify**: Confirmation modal appears with warning message
3. **Click** "Cancel"
4. **Verify**: Modal closes, exam remains
5. **Click** "Delete" again, then **click** "Delete Exam" in modal
6. **Verify**:
   - Success message appears
   - Redirects to Exam Center
   - Exam no longer appears in the list

### Test Case 6: Question Bank Access (Primary Flow)
1. On the exam details page, **click** "Manage Question Bank" button
2. **Verify**:
   - Redirects to `/question-bank/{slug}`
   - Question Bank page loads correctly
   - Shows questions for the selected exam

### Test Case 7: Question Bank Access (Dropdown Flow)
1. **Navigate** to Exam Center
2. **Locate** an exam
3. **Click** the "Actions" dropdown button
4. **Click** "Question Bank" from the dropdown menu
5. **Verify**:
   - Redirects to `/question-bank/{slug}`
   - Question Bank page loads correctly

### Test Case 8: Edit Access (Dropdown Flow)
1. **Navigate** to Exam Center
2. **Locate** an exam
3. **Click** the "Actions" dropdown button
4. **Click** "Edit" from the dropdown menu
5. **Verify**:
   - Redirects to exam edit page
   - Edit form loads correctly

### Test Case 9: Authorization Checks
#### As Owner (Teacher who created exam)
- ✅ Can view exam details
- ✅ Can edit exam
- ✅ Can delete exam
- ❌ Cannot see exam password

#### As Admin/Super Admin/System
- ✅ Can view any exam details
- ✅ Can edit any exam
- ✅ Can delete any exam
- ✅ Can see exam password

#### As Other Teacher (not owner, not admin)
- ❌ Should get 403 Unauthorized when trying to access exam details
- Test by accessing: `/exams/{slug-of-exam-they-dont-own}`

### Test Case 10: Responsive Design
1. **Resize** browser window to mobile size (375px width)
2. **Verify**: Layout adjusts properly
3. **Test** on tablet size (768px width)
4. **Verify**: All buttons and information remain accessible

## Expected Outcomes

### ✅ Success Criteria
- Exam name links to exam details page (not question bank)
- Password visible only to authorized users
- Edit and delete functions work correctly
- Question Bank still accessible via two paths (dropdown + details page)
- No console errors or Laravel errors
- All authorization rules enforced

### ❌ Failure Indicators
- Clicking exam name goes to question bank
- Password visible to unauthorized users
- 404 errors when accessing `/exams/{slug}`
- Edit/delete modals don't open
- JavaScript errors in browser console
- Unauthorized users can edit/delete exams

## Troubleshooting

### Issue: 404 Not Found on `/exams/{slug}`
**Solution**: 
- Clear route cache: `php artisan route:clear`
- Verify route exists: `php artisan route:list --name=exams.show`

### Issue: "Exam not found" Error
**Solution**:
- Ensure exam has a slug in database
- Check `getRouteKeyName()` method exists in Exam model
- Verify slug is unique and not null

### Issue: Password Not Visible
**Solution**:
- Check user role: Must be 'admin', 'Super Admin', or 'System'
- Verify condition in view: `@if(Auth::user()->role === 'admin' || Auth::user()->role === 'Super Admin' || Auth::user()->role === 'System')`

### Issue: Edit Modal Doesn't Open
**Solution**:
- Check browser console for JavaScript errors
- Ensure Bootstrap 5 is loaded
- Verify Livewire scripts are present on page

## Database Queries for Testing

### Get Admin User
```sql
SELECT id, name, email, role FROM users WHERE role IN ('admin', 'Super Admin', 'System') LIMIT 1;
```

### Get Exam with Slug
```sql
SELECT id, slug, password, status, course_id, user_id FROM exams WHERE slug IS NOT NULL LIMIT 1;
```

### Verify Route Key
```sql
SELECT id, slug, password FROM exams WHERE slug = 'your-exam-slug';
```

## Automated Testing (Future)

For future automated testing with Playwright:
```javascript
// Navigate to exam center
await page.goto('http://127.0.0.1:8000/exam-center');

// Click first exam name
await page.click('table tbody tr:first-child a.text-hover-primary');

// Verify URL
expect(page.url()).toMatch(/\/exams\/[a-z0-9-]+/);

// Verify password visible (for admin)
await expect(page.locator('text=Exam Password')).toBeVisible();

// Click Manage Question Bank
await page.click('text=Manage Question Bank');

// Verify redirected to question bank
expect(page.url()).toMatch(/\/question-bank\/[a-z0-9-]+/);
```

## Rollback Plan

If issues arise, rollback by reverting these changes:

1. **Remove route**:
   ```php
   // Comment out or remove from web.php
   // Route::get('/exams/{exam}', \App\Livewire\ExamDetail::class)->name('exams.show');
   ```

2. **Revert exam table row link**:
   ```blade
   <!-- Change back to -->
   <a href="{{ route('questionbank.with.slug', $exam->slug ? $exam->slug : $exam->id) }}">
   ```

3. **Remove getRouteKeyName** from Exam model

4. **Delete new files**:
   - `/cis/app/Livewire/ExamDetail.php`
   - `/cis/resources/views/livewire/exam-detail.blade.php`

5. **Clear caches**:
   ```bash
   php artisan route:clear
   php artisan view:clear
   php artisan config:clear
   ```

## Sign-off Checklist

- [ ] All test cases passed
- [ ] No console errors
- [ ] No Laravel errors in logs
- [ ] Password visibility works correctly
- [ ] Authorization rules enforced
- [ ] Question Bank accessible from both paths
- [ ] Edit and delete functions work
- [ ] Responsive design verified
- [ ] Cross-browser tested (Chrome, Firefox, Safari)

---

**Implementation Date**: October 2, 2025  
**Tested By**: _______________  
**Date Tested**: _______________  
**Status**: ⬜ Pass | ⬜ Fail | ⬜ Needs Review
