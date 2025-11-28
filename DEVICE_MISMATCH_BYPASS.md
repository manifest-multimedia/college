# Device Mismatch Bypass - Admin Command Guide

## Overview
When a student encounters a **Device Mismatch Detected** error, an administrator can use the `exam:allow-device-mismatch` command to allow them to continue with their exam without being blocked by the device validation check.

## Usage

### Interactive Mode (With Confirmation)
```bash
php artisan exam:allow-device-mismatch <exam_session_id>
```

Example:
```bash
php artisan exam:allow-device-mismatch 42
```

This will display the exam session details and ask for confirmation before proceeding.

### Force Mode (Skip Confirmation)
```bash
php artisan exam:allow-device-mismatch <exam_session_id> --force
```

Example:
```bash
php artisan exam:allow-device-mismatch 42 --force
```

This will bypass the confirmation prompt and immediately enable the bypass.

## What It Does

1. **Validates the exam session** - Ensures the session ID exists
2. **Displays session details** including:
   - Student name and ID
   - Exam title
   - Current status (Active/Completed)
   - Start time
   - Device information
3. **Requires confirmation** - Asks admin to confirm before proceeding (unless `--force` is used)
4. **Enables device bypass** by:
   - Setting `device_mismatch_bypassed = true`
   - Recording the bypass timestamp
   - Recording who bypassed it (Admin ID or 'console')

## Example Output

### Interactive Mode
```
=== Exam Session Details ===
Student: John Doe (TEST001)
Exam: Biology Exam
Status: Active
Started: 2025-11-28 14:30:00

Device Info: {"browser":"Chrome","os":"Windows","device":"Desktop"}

Allow this student to continue despite device mismatch? (yes/no) [no]:
 > yes

✓ Device mismatch bypass enabled for exam session 42
Student can now continue with their exam.
```

### When Already Bypassed
```
Device mismatch bypass is already enabled for this session.
Bypassed at: 2025-11-28 14:35:22
Bypassed by: 1
```

## Finding the Exam Session ID

You can find the exam session ID from:
1. The exam sessions table in the database
2. The Extra Time Manager interface (if you have admin access to view sessions)
3. By querying: `SELECT id, student_id, exam_id FROM exam_sessions WHERE device_mismatch_detected = true;`

## Important Notes

- ✅ The bypass is **permanent** for that exam session
- ✅ The student can now **continue taking the exam** without seeing the device mismatch alert
- ✅ All actions are **logged** with who bypassed it and when
- ✅ If the exam is **already completed**, the student can still review it
- ✅ Works for both **active** and **completed** exams

## Database Fields Added

The following fields were added to `exam_sessions` table:
- `device_mismatch_bypassed` (boolean, default: false)
- `device_mismatch_bypassed_at` (timestamp, nullable)
- `device_mismatch_bypassed_by` (string, nullable) - stores admin ID or 'console'
