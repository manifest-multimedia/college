# AI Sensei Cohort Management Capabilities

## Overview
Extended AI Sensei (MCP Server) with comprehensive cohort management capabilities for student ID generation and bulk operations.

## New MCP Functions Added

### 1. `list_cohorts`
**Purpose**: List all cohorts in the system with filtering options.

**Parameters**:
- `active_only` (optional, boolean): Filter to show only active cohorts

**Response**: Array of cohorts with details including student count

**Example Usage**:
```json
{
  "name": "list_cohorts",
  "arguments": {
    "active_only": true
  }
}
```

### 2. `generate_student_ids_for_cohort`
**Purpose**: Generate student IDs for all students in a specific cohort who don't have IDs.

**Parameters**:
- `cohort_name` (required, string): Name of the cohort to generate IDs for

**Features**:
- Only processes students without existing student IDs (null, empty, or TEMP_ prefixed)
- Uses the existing `StudentIdGenerationService` for consistent ID format
- Maintains alphabetical ordering for proper sequence assignment
- Provides detailed results including all generated IDs
- Database transaction safety

**Example Usage**:
```json
{
  "name": "generate_student_ids_for_cohort",
  "arguments": {
    "cohort_name": "Nursing Class 2024"
  }
}
```

### 3. `delete_cohort_students`
**Purpose**: Delete all students for a specific cohort (destructive action with confirmation).

**Parameters**:
- `cohort_name` (required, string): Name of the cohort to delete students from
- `confirm_deletion` (required, boolean): Must be `true` to confirm deletion

**Safety Features**:
- Requires explicit confirmation (`confirm_deletion: true`)
- Logs all deleted students for audit purposes
- Provides detailed information about what was deleted
- Database transaction safety

**Example Usage**:
```json
{
  "name": "delete_cohort_students",
  "arguments": {
    "cohort_name": "Test Cohort 2024",
    "confirm_deletion": true
  }
}
```

### 4. `get_cohort_student_count`
**Purpose**: Get detailed student count information for a specific cohort.

**Parameters**:
- `cohort_name` (required, string): Name of the cohort

**Response**: Detailed breakdown including:
- Total students
- Students with IDs vs without IDs
- Cohort information

**Example Usage**:
```json
{
  "name": "get_cohort_student_count",
  "arguments": {
    "cohort_name": "Nursing Class 2024"
  }
}
```

## Implementation Details

### Student ID Generation Process
- Integrates with existing `StudentIdGenerationService`
- Supports all configured ID formats (structured, simple, custom)
- Maintains alphabetical ordering when enabled in configuration
- Handles concurrent generation safely with database locking
- Provides comprehensive error handling and logging

### Safety Measures for Bulk Operations
1. **Explicit Confirmation Required**: Destructive operations require `confirm_deletion: true`
2. **Audit Logging**: All operations are logged with user context
3. **Transaction Safety**: Database transactions ensure data consistency
4. **Detailed Feedback**: Operations return comprehensive results for verification

### Error Handling
- All functions include comprehensive exception handling
- Detailed error messages for troubleshooting
- Logging for audit and debugging purposes
- Graceful fallback for edge cases

## Usage Examples

### Generate IDs for a New Cohort
```
AI Sensei, please generate student IDs for cohort "Computer Science 2024"
```

### Check Cohort Status Before ID Generation
```
AI Sensei, how many students are in "Computer Science 2024" and how many need student IDs?
```

### Clean Up Mistaken Import
```
AI Sensei, I need to delete all students from "Test Import 2024" cohort because the data was wrong. Please confirm this destructive action.
```

### List All Available Cohorts
```
AI Sensei, show me all active cohorts in the system
```

## Testing Results
All functions have been tested successfully:

1. ✅ **list_cohorts**: Correctly lists cohorts with student counts
2. ✅ **generate_student_ids_for_cohort**: Successfully generates IDs using proper format (CIS/DEPT/GEN/25/26/001, etc.)
3. ✅ **delete_cohort_students**: Safely deletes with proper confirmation and audit logging
4. ✅ **get_cohort_student_count**: Provides accurate counts and breakdowns

## Security Considerations
- All operations respect user authentication context
- Destructive operations require explicit confirmation
- Comprehensive audit logging for accountability
- Database transaction safety prevents partial operations

## Integration
These functions are fully integrated into the existing MCP exam management server at:
- **Service**: `App\Services\Communication\Chat\MCP\ExamManagementMCPService`
- **Configuration**: `mcp-exam-server.json`
- **Command**: `php artisan mcp:serve`

The AI Sensei can now handle cohort management requests naturally through conversation, making student administration much more efficient and safer.