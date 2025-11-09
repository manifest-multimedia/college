# Manual Testing Guide for Student ID Generation Integration

## Quick Verification Steps

### 1. Check Service Integration
Run this command to verify the service is working:

```bash
php artisan tinker
```

Then in tinker:
```php
use App\Services\StudentIdGenerationService;
$service = new StudentIdGenerationService();
$id = $service->generateStudentId('Test', 'Student', 1, null);
echo $id; // Should output something like: PNMTC/DA/XXX/24/25/001
```

### 2. Test Import Integration

Create a simple test Excel file with these columns:
```
first_name,last_name,email
Alice,Johnson,alice@test.com
Bob,Smith,bob@test.com
```

Upload through the student import interface and verify:
- Import completes successfully
- Results show "2 Student IDs Generated"
- Students in database have generated IDs following format
- IDs are in alphabetical order (Johnson before Smith)

### 3. Verify Mixed Import

Create Excel with both existing and missing IDs:
```
first_name,last_name,email,student_id
Charlie,Brown,charlie@test.com,
David,Wilson,david@test.com,EXISTING/ID/001
Emma,Davis,emma@test.com,
```

Verify:
- Charlie and Emma get generated IDs
- David keeps existing ID
- Statistics show "2 Student IDs Generated"
- Generated IDs follow proper format and ordering

### 4. Check Error Handling

Test with incomplete data:
```
first_name,last_name,email
,Johnson,incomplete@test.com
Frank,,incomplete2@test.com
Grace,Anderson,complete@test.com
```

Verify:
- Only Grace gets a generated ID
- First two students created without IDs
- Statistics show "1 Student IDs Generated"
- No errors in import process

## Expected Behaviors

1. **ID Format**: `PNMTC/DA/[PROGRAM]/[YY]/[YY]/[NNN]`
2. **Alphabetical Order**: Sequence based on last name, then first name
3. **Program Mapping**: Based on selected college class (e.g., RM for Midwifery)
4. **Academic Year**: Current academic year or YY/YY format
5. **Error Recovery**: Import continues even if ID generation fails

## Verification Checklist

- [ ] Service generates valid IDs independently
- [ ] Import interface shows ID generation statistics
- [ ] Generated IDs follow institutional format
- [ ] Alphabetical ordering is maintained  
- [ ] Existing IDs are preserved
- [ ] Missing names handled gracefully
- [ ] Import logs show generation activity
- [ ] UI displays updated statistics properly

## Troubleshooting

If issues occur:

1. Check logs in `storage/logs/laravel.log` for generation errors
2. Verify settings table has `school_name_prefix` entry
3. Ensure current academic year is set in database
4. Confirm college class exists for selected program
5. Check that first_name and last_name are not null/empty

The integration should work seamlessly with existing import flows while adding automatic ID generation capability.