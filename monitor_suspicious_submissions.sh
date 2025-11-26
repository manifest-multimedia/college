#!/bin/bash

# Monitor for suspicious exam submissions (low question count)
# Usage: ./monitor_suspicious_submissions.sh [log_file]

LOG_FILE=${1:-"storage/logs/laravel.log"}

echo "=========================================="
echo "Monitoring Suspicious Exam Submissions"
echo "=========================================="
echo ""
echo "Looking for submissions with 10 or fewer answered questions..."
echo ""

# Check for submissions with low answered counts
echo "=== Low Answer Count Submissions ==="
grep "Exam submission initiated" "$LOG_FILE" | grep -E "answered_count\":(10|[0-9])," | tail -20

echo ""
echo "=== Auto-Submitted Exams (Today) ==="
grep "$(date +%Y-%m-%d)" "$LOG_FILE" | grep "auto_submitted.*true" | tail -10

echo ""
echo "=== Recent Manual Submissions ==="
grep "Exam manually submitted successfully" "$LOG_FILE" | tail -10

echo ""
echo "=== Response Saving Pattern (Last 50) ==="
grep "Saving exam response" "$LOG_FILE" | tail -50 | grep -o "current_answered_count\":[0-9]*" | sort | uniq -c

echo ""
echo "=========================================="
echo "Monitoring Complete"
echo "=========================================="
