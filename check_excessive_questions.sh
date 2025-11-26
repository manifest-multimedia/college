#!/bin/bash

# Check for exam sessions with excessive questions
# Usage: ./check_excessive_questions.sh

echo "=========================================="
echo "Checking for Excessive Session Questions"
echo "=========================================="
echo ""

cd /home/johnsonsebire/www/college.local/cis

# Run diagnostic query using artisan tinker
php artisan tinker --execute="
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\ExamSessionQuestion;

echo 'Checking exams with questions_per_session configured...' . PHP_EOL . PHP_EOL;

\$exams = Exam::whereNotNull('questions_per_session')
    ->where('questions_per_session', '>', 0)
    ->get();

if (\$exams->isEmpty()) {
    echo 'No exams found with questions_per_session configured.' . PHP_EOL;
    exit(0);
}

echo 'Found ' . \$exams->count() . ' exam(s) with limits configured.' . PHP_EOL . PHP_EOL;

\$issuesFound = false;

foreach (\$exams as \$exam) {
    echo '📝 Exam: ' . \$exam->name . ' (ID: ' . \$exam->id . ')' . PHP_EOL;
    echo '   Limit: ' . \$exam->questions_per_session . ' questions per session' . PHP_EOL;
    
    \$sessions = ExamSession::where('exam_id', \$exam->id)
        ->whereNull('completed_at')  // Active sessions only
        ->get();
    
    if (\$sessions->isEmpty()) {
        echo '   No active sessions found.' . PHP_EOL . PHP_EOL;
        continue;
    }
    
    echo '   Active sessions: ' . \$sessions->count() . PHP_EOL;
    
    \$excessive = 0;
    foreach (\$sessions as \$session) {
        \$count = ExamSessionQuestion::where('exam_session_id', \$session->id)->count();
        
        if (\$count > \$exam->questions_per_session) {
            \$excessive++;
            \$excess = \$count - \$exam->questions_per_session;
            echo '   ⚠️  Session ' . \$session->id . ' has ' . \$count . ' questions (+' . \$excess . ' excess)' . PHP_EOL;
            \$issuesFound = true;
        }
    }
    
    if (\$excessive === 0) {
        echo '   ✅ All active sessions have correct question counts.' . PHP_EOL;
    } else {
        echo '   ⚠️  Found ' . \$excessive . ' session(s) with excessive questions!' . PHP_EOL;
    }
    
    echo PHP_EOL;
}

if (\$issuesFound) {
    echo '========================================' . PHP_EOL;
    echo '⚠️  ISSUES DETECTED!' . PHP_EOL;
    echo 'Run: php artisan exam:fix-excessive-questions --dry-run --active-only' . PHP_EOL;
    echo 'to see what would be fixed.' . PHP_EOL;
    echo '========================================' . PHP_EOL;
} else {
    echo '✅ No issues found!' . PHP_EOL;
}
"

echo ""
echo "Check complete!"
