# Student ID Reassignment Feature - AI Sensei Guide

## Overview

AI Sensei can now help institutions reassign student IDs to match their configured ID format. This feature allows safe bulk updates of existing student IDs while maintaining data integrity and providing reversion capabilities.

## Use Cases

1. **Format Migration**: Convert from simple format (MHIAFRGN220003) to structured format (MHIAF/RGN/22/23/003) or vice versa
2. **Pattern Standardization**: Update all IDs to match a new institutional pattern
3. **Adding Separators**: Introduce separators to existing IDs without separators
4. **Sequence Reordering**: Reorder sequence numbers based on alphabetical sorting

## Available Commands

### 1. Get Current Configuration
```
What is my current student ID configuration?
```

**Function**: `get_student_id_configuration`

Returns:
- Current format (simple, structured, or custom)
- Institution prefixes
- Alphabetical ordering settings
- Academic year settings

### 2. Analyze ID Format Statistics
```
Show me statistics about student ID formats in the system
```

**Function**: `get_student_id_statistics`

Returns:
- Total student count
- Distribution by format (simple vs structured)
- Sample IDs for each format

### 3. Parse a Specific ID
```
Parse the student ID MHIAFRGN220003 for me
```

**Function**: `parse_student_id`

Returns:
- Format type
- Institution component
- Program component
- Year component
- Sequence number

### 4. Preview Reassignment Changes
```
Preview what would happen if I update student IDs to structured format
```

**Function**: `preview_student_id_reassignment`

Parameters:
- `program_id` (optional): Filter by specific program
- `cohort_id` (optional): Filter by cohort
- `year` (optional): Filter by year (e.g., "22" for 2022)
- `current_format` (optional): Filter by current format type

Returns:
- Total students affected
- Preview of changes (old ID → new ID)
- No actual changes made

### 5. Execute ID Reassignment
```
Update all student IDs from simple format MHIAFRGN220003 to structured format MHIAF/RGN/22/0003
```

**Function**: `reassign_student_ids`

Parameters:
- `program_id` (optional): Filter by program
- `cohort_id` (optional): Filter by cohort
- `year` (optional): Filter by year
- `current_format` (optional): Filter by current format
- `target_format` (optional): Desired format (simple/structured/custom)
- `custom_pattern` (optional): Custom pattern string

Returns:
- Total processed
- Successful updates
- Failed updates
- Detailed update log

**Important**: Creates backup records for each change to enable reversion.

### 6. Revert Changes
```
Revert student ID changes for student IDs [1, 2, 3, 4, 5]
```

**Function**: `revert_student_ids`

Parameters:
- `student_ids` (required): Array of student database IDs (not student_id strings)

Returns:
- Total reverted
- Success/failure counts
- Detailed reversion log

## Example Conversations

### Example 1: Basic Format Change

**User**: "We currently have student IDs like MHIAFRGN220003. We want to add slashes to make them MHIAF/RGN/22/0003. Can you help?"

**AI Sensei Response Flow**:
1. Parse sample ID to understand current format
2. Show current configuration
3. Get statistics to see how many students affected
4. Preview the changes
5. Ask for confirmation
6. Execute reassignment if approved
7. Show results

### Example 2: Filtered Update

**User**: "Update student IDs for the RGN program only"

**AI Sensei Response Flow**:
1. Get program ID for RGN
2. Preview changes filtered by program
3. Show affected students
4. Execute if approved
5. Provide summary

### Example 3: Reverting Changes

**User**: "We need to undo the student ID changes we just made"

**AI Sensei Response Flow**:
1. Get list of recently changed students
2. Explain reversion process
3. Execute reversion if approved
4. Confirm restoration

## Custom Patterns

The system supports custom patterns using placeholders:

### Available Placeholders
- `{INSTITUTION}` - Full institution prefix (e.g., "COLLEGE/DEPT")
- `{INSTITUTION_SIMPLE}` - Simple institution code (e.g., "MHIAF")
- `{PROGRAM}` - Program code (e.g., "RGN", "RM")
- `{PROGRAM_SIMPLE}` - Simplified program code
- `{YEAR_FULL}` - Full academic year (e.g., "22/23")
- `{YEAR_SIMPLE}` - Single year (e.g., "22")
- `{SEQUENCE_3}` - 3-digit sequence number (001-999)
- `{SEQUENCE_4}` - 4-digit sequence number (0001-9999)
- `{FIRST_NAME}` - First 2 letters of first name
- `{LAST_NAME}` - First 2 letters of last name

### Example Custom Patterns
```
{INSTITUTION}/{PROGRAM}/{YEAR_SIMPLE}/{SEQUENCE_4}
→ MHIAF/RGN/23/0003

{INSTITUTION_SIMPLE}{PROGRAM}{YEAR_SIMPLE}{SEQUENCE_4}
→ MHIAFRGN230003

{PROGRAM}-{YEAR_SIMPLE}-{SEQUENCE_3}
→ RGN-23-003
```

## Safety Features

### 1. Backup System
- Every ID change is backed up in `student_id_changes` table
- Stores old ID, new ID, timestamp, and user who made change
- Enables full reversion

### 2. Validation
- Checks for dependent records (course registrations, payments, etc.)
- Warns about potential impacts
- Validates uniqueness before applying

### 3. Dry Run Mode
- Preview mode shows exactly what will change
- No database modifications
- Safe to run multiple times

### 4. Transaction Safety
- Uses database transactions
- Atomic operations (all or nothing)
- Rollback on any error

### 5. Permission Checks
- Requires administrator or registrar role
- Logs all operations
- Audit trail maintained

## Technical Details

### Database Schema

**student_id_changes** table:
- `student_id`: Reference to student
- `old_student_id`: Original ID
- `new_student_id`: New ID
- `changed_by`: User who made the change
- `status`: active, reverted, or superseded
- `notes`: Optional notes
- `timestamps`: Created/updated timestamps

### Alphabetical Ordering

When enabled, students are assigned sequence numbers based on alphabetical order of surnames. This means:
- Adams, John → 001
- Brown, Mary → 002
- Chen, Wei → 003

If a new student "Baker, Tom" is added later, existing sequences are preserved but the new student gets the next available number, not inserted alphabetically.

For true alphabetical reordering, use the reassignment feature.

## Best Practices

### 1. Always Preview First
```
Preview student ID changes before executing
```

### 2. Start with Small Batches
```
Update student IDs for cohort 2023 only
```

### 3. Backup Before Large Operations
```
Create database backup before updating all student IDs
```

### 4. Test on Non-Production First
```
Test ID reassignment in development environment first
```

### 5. Communicate Changes
Inform relevant departments:
- Registrar's office
- IT department
- Finance (fee payments reference student IDs)
- Examination office

## Troubleshooting

### Issue: "AI processing failed" (but IDs were updated successfully)
**Cause**: This happens when the operation completes successfully, but OpenAI's API hits rate limits when trying to send the response back to you.

**What Actually Happened**: 
- ✅ Your student IDs **were successfully updated**
- ❌ The AI couldn't send you a confirmation due to API rate limits

**Solution**: 
1. Wait 10-15 seconds before sending another message
2. Check the logs to confirm the update was successful: `storage/logs/laravel.log`
3. Query the database or use AI Sensei to check current statistics
4. The operation is complete - you don't need to retry the update

**Prevention for Production**:
- Upgrade to OpenAI API tier with higher rate limits
- Process updates in smaller batches (by program or cohort)
- Use preview mode first to understand scope before executing
- Schedule large updates during off-peak hours

### Issue: "Student ID already exists"
**Solution**: Check for duplicate IDs. The system ensures uniqueness but may fail if target format creates duplicates.

### Issue: "Permission denied"
**Solution**: Requires Administrator or Registrar role. Contact system administrator.

### Issue: "Cannot revert - no backup found"
**Solution**: Reversion only works for IDs changed through this system. Manual changes cannot be reverted.

### Issue: "Format validation failed"
**Solution**: Check that custom pattern uses valid placeholders and creates properly formatted IDs.

## API Functions Reference

### preview_student_id_reassignment
```json
{
  "program_id": 1,
  "cohort_id": 2,
  "year": "23",
  "current_format": "simple"
}
```

### reassign_student_ids
```json
{
  "program_id": 1,
  "target_format": "structured",
  "custom_pattern": "{INSTITUTION}/{PROGRAM}/{YEAR_SIMPLE}/{SEQUENCE_4}"
}
```

### revert_student_ids
```json
{
  "student_ids": [1, 2, 3, 4, 5]
}
```

### parse_student_id
```json
{
  "student_id": "MHIAFRGN220003"
}
```

## Configuration

Update `.env` file:
```env
STUDENT_ID_FORMAT=structured  # simple, structured, or custom
STUDENT_ID_CUSTOM_PATTERN={INSTITUTION}/{PROGRAM}/{YEAR_SIMPLE}/{SEQUENCE_4}
STUDENT_ID_INSTITUTION_PREFIX=COLLEGE/DEPT
STUDENT_ID_INSTITUTION_SIMPLE=MHIAF
STUDENT_ID_ALPHABETICAL_ORDERING=true
STUDENT_ID_USE_ACADEMIC_YEAR=true
```

## Production Deployment Considerations

### OpenAI API Rate Limits

**Understanding the Issue**:
- OpenAI's GPT-4 has rate limits measured in Tokens Per Minute (TPM)
- Large batch operations (like updating 173 students) generate detailed responses
- The response can exceed the remaining token quota, causing a rate limit error
- **Important**: Your operation completes successfully; only the AI's response is delayed

**Rate Limit Tiers** (as of 2024):
- Tier 1 (Free): 30,000 TPM
- Tier 2: 450,000 TPM  
- Tier 3: 10,000,000 TPM
- Tier 4+: Higher limits available

**Production Best Practices**:

1. **Upgrade OpenAI Tier**: 
   - Upgrade to at least Tier 2 for production use
   - Monitor usage in OpenAI dashboard

2. **Batch Processing Strategy**:
   ```
   # Instead of: "Update all student IDs"
   # Use: "Update student IDs for RGN program"
   # Then: "Update student IDs for RM program"
   ```

3. **Off-Peak Scheduling**:
   - Schedule large updates during low-traffic periods
   - Use cron jobs for automated large-scale updates

4. **Response Optimization**:
   - The system now returns only 5 sample updates instead of all
   - This significantly reduces token usage in responses

5. **User Communication**:
   - Inform users that "AI processing failed" doesn't mean operation failed
   - Direct them to check logs or re-query statistics to confirm success
   - Train administrators to wait 10-15 seconds between large operations

### Execution Time

**Current Performance**:
- 173 students updated in ~6.7 seconds ✅
- No execution time issues observed
- Database transactions complete quickly

**No Changes Needed**:
- The 6.7 second execution time is well within PHP's default 30-second limit
- No need to extend `max_execution_time` for this feature
- Transaction safety is maintained with proper rollback on errors

## Support

For issues or questions:
1. Check logs: `storage/logs/laravel.log`
2. Review permission settings
3. Contact system administrator
4. Refer to full documentation: `STUDENT_ID_GENERATION_INTEGRATION.md`
