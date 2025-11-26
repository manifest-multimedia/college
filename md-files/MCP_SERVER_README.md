# MCP Exam Management Server

This MCP (Model Context Protocol) server enables AI assistants to manage exams, question sets, and questions within the College Information System.

## Quick Start

### Start the MCP Server
```bash
cd /home/johnsonsebire/www/college.local/cis
php artisan mcp:serve
```

The server will start on `localhost:3000` by default. You can customize the host and port:
```bash
php artisan mcp:serve --host=0.0.0.0 --port=8080
```

### Configuration for AI Assistants

Add this configuration to your MCP client configuration file:

```json
{
    "mcpServers": {
        "exam-management": {
            "command": "php",
            "args": [
                "/home/johnsonsebire/www/college.local/cis/artisan",
                "mcp:serve"
            ],
            "env": {
                "APP_ENV": "local"
            }
        }
    }
}
```

## Available Tools

### 1. Create Question Set
Create a new question set for a specific course.

**Parameters:**
- `name` (required): Name of the question set
- `description` (optional): Description of the question set  
- `course_code` (required): Course code (e.g., "CS101", "MATH201")
- `difficulty_level` (required): "easy", "medium", or "hard"

**Example:**
```json
{
    "name": "Database Fundamentals Quiz 1",
    "description": "Basic database concepts and SQL",
    "course_code": "CS301", 
    "difficulty_level": "medium"
}
```

### 2. Add Question to Set
Add a multiple choice question to an existing question set.

**Parameters:**
- `question_set_id` (required): ID of the question set
- `question_text` (required): The question text/prompt
- `options` (required): Array of answer options with `text` and `is_correct` fields
- `explanation` (optional): Explanation for the correct answer
- `marks` (optional): Points for the question (default: 1)
- `difficulty_level` (optional): Difficulty level for this specific question

**Example:**
```json
{
    "question_set_id": 1,
    "question_text": "What does SQL stand for?",
    "options": [
        {"text": "Structured Query Language", "is_correct": true},
        {"text": "Simple Query Language", "is_correct": false},
        {"text": "System Query Language", "is_correct": false},
        {"text": "Standard Query Language", "is_correct": false}
    ],
    "explanation": "SQL stands for Structured Query Language, which is used to manage relational databases.",
    "marks": 2,
    "difficulty_level": "easy"
}
```

### 3. Create Exam
Create a new exam with question sets.

**Parameters:**
- `course_code` (required): Course code for the exam
- `type` (required): "quiz", "midterm", "final", "assignment", or "test"
- `duration` (required): Exam duration in minutes
- `start_date` (required): Start date/time (ISO format)
- `end_date` (required): End date/time (ISO format)
- `passing_percentage` (optional): Minimum percentage to pass (default: 50)
- `question_sets` (optional): Array of question set configurations

**Example:**
```json
{
    "course_code": "CS301",
    "type": "midterm", 
    "duration": 90,
    "passing_percentage": 60,
    "start_date": "2024-03-15T09:00:00Z",
    "end_date": "2024-03-15T10:30:00Z",
    "question_sets": [
        {
            "question_set_id": 1,
            "questions_to_pick": 10,
            "shuffle_questions": true
        }
    ]
}
```

### 4. List Question Sets
List all question sets with optional filtering.

**Parameters:**
- `course_code` (optional): Filter by course code
- `difficulty_level` (optional): Filter by difficulty level

### 5. List Courses
Get all available courses/subjects in the system.

### 6. Get Question Set Details
Get detailed information about a specific question set including all its questions.

**Parameters:**
- `question_set_id` (required): ID of the question set

## API Endpoints

When the server is running, you can also access these HTTP endpoints:

- `GET /capabilities` - Get server capabilities and available tools
- `GET /health` - Health check endpoint
- `POST /` - MCP protocol requests

## Error Handling

All tool responses include a `success` field:
- `success: true` - Operation completed successfully, check `data` field
- `success: false` - Operation failed, check `error` field for details

## Security

The MCP server uses the current Laravel application's authentication and authorization. Make sure:
1. The Laravel application is properly configured
2. Database connections are working
3. User permissions are set up correctly

## Troubleshooting

### Command Not Found
If `php artisan mcp:serve` shows "Command not found":
1. Ensure you're in the correct directory (`/home/johnsonsebire/www/college.local/cis`)
2. Run `composer install` to install dependencies
3. Check that the command is registered in `app/Console/Commands/ServeMCPCommand.php`

### Database Errors
If you get database-related errors:
1. Check your `.env` file database configuration
2. Ensure the database exists and is accessible
3. Run migrations: `php artisan migrate`

### Permission Errors
If you get permission-related errors:
1. Ensure the web server user has write access to storage directories
2. Run: `php artisan storage:link`
3. Check file permissions: `chmod -R 755 storage bootstrap/cache`

## Development

To extend the MCP server with additional tools:

1. Add new methods to `ExamManagementMCPService`
2. Update the `getTools()` method with new tool definitions
3. Add case handlers in `handleToolCall()`
4. Test your changes with the MCP client

## Log Files

MCP server errors are logged to Laravel's standard log files:
- `storage/logs/laravel.log`

Use `tail -f storage/logs/laravel.log` to monitor real-time logs during development.