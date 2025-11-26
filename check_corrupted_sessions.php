<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ExamSession;
use Carbon\Carbon;

echo "Checking for exam sessions with corrupted completed_at dates...\n\n";

$corrupted = ExamSession::whereNotNull('completed_at')
    ->where('auto_submitted', 0) // Focus on non-auto-submitted sessions
    ->get()
    ->filter(function($session) {
        if (!$session->started_at || !$session->completed_at) return false;
        $diff = $session->started_at->diffInHours($session->completed_at, false);
        // Flag if completed_at is more than 24 hours after started_at
        return $diff > 24;
    });

echo "Found {$corrupted->count()} sessions with potentially corrupted completed_at dates\n\n";

if ($corrupted->count() > 0) {
    echo "Corrupted Sessions Details:\n";
    echo str_repeat("=", 100) . "\n";
    printf("%-6s %-25s %-20s %-20s %-15s\n", "ID", "Student", "Started At", "Completed At", "Diff (hours)");
    echo str_repeat("=", 100) . "\n";
    
    foreach ($corrupted->take(20) as $session) {
        printf(
            "%-6d %-25s %-20s %-20s %-15.1f\n",
            $session->id,
            substr($session->student->name ?? 'Unknown', 0, 24),
            $session->started_at->format('Y-m-d H:i'),
            $session->completed_at->format('Y-m-d H:i'),
            $session->started_at->diffInHours($session->completed_at, false)
        );
    }
    
    echo "\n\nTo fix these records, you can run:\n";
    echo "UPDATE exam_sessions SET completed_at = NULL WHERE id IN (" . $corrupted->pluck('id')->take(20)->implode(', ') . ");\n";
}

echo "\nCheck complete!\n";
