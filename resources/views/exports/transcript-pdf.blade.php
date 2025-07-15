<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Official Transcript - {{ $student->student_id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        
        .header h1 {
            font-size: 20px;
            margin: 0 0 10px 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .header .institution {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .header .subtitle {
            font-size: 14px;
            color: #666;
        }
        
        .student-info {
            display: table;
            width: 100%;
            margin-bottom: 25px;
        }
        
        .student-info .left,
        .student-info .right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        
        .info-row {
            margin-bottom: 8px;
        }
        
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 140px;
        }
        
        .courses-section h2 {
            font-size: 16px;
            background-color: #f5f5f5;
            padding: 10px;
            margin: 20px 0 10px 0;
            border-left: 4px solid #333;
        }
        
        .courses-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .courses-table th,
        .courses-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        .courses-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            font-size: 11px;
        }
        
        .courses-table td {
            font-size: 11px;
        }
        
        .courses-table .numeric {
            text-align: center;
        }
        
        .grade-a { background-color: #d4edda; }
        .grade-b { background-color: #d1ecf1; }
        .grade-c { background-color: #fff3cd; }
        .grade-d { background-color: #f8d7da; }
        .grade-f { background-color: #f5c6cb; }
        
        .status-pass { 
            color: #155724; 
            font-weight: bold; 
        }
        
        .status-fail { 
            color: #721c24; 
            font-weight: bold; 
        }
        
        .summary {
            background-color: #f8f9fa;
            padding: 15px;
            border: 2px solid #333;
            margin-top: 25px;
        }
        
        .summary h3 {
            margin: 0 0 15px 0;
            font-size: 16px;
            text-align: center;
        }
        
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .summary-table td {
            padding: 6px 10px;
            border-bottom: 1px solid #ddd;
        }
        
        .summary-table .label {
            font-weight: bold;
            width: 70%;
        }
        
        .summary-table .value {
            text-align: right;
            font-weight: bold;
            width: 30%;
        }
        
        .gpa-highlight {
            font-size: 14px;
            color: #0056b3;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 72px;
            color: rgba(0, 0, 0, 0.05);
            z-index: -1;
            pointer-events: none;
        }
        
        @media print {
            body { margin: 0; }
            .header { page-break-after: avoid; }
            .courses-section { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="watermark">OFFICIAL</div>
    
    <div class="header">
        <div class="institution">{{ config('app.name', 'Educational Institution') }}</div>
        <h1>Official Academic Transcript</h1>
        <div class="subtitle">Confidential Document - Not Valid Without Official Seal</div>
    </div>
    
    <div class="student-info">
        <div class="left">
            <div class="info-row">
                <span class="info-label">Student ID:</span>
                <span>{{ $student->student_id }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Student Name:</span>
                <span>{{ $student->full_name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Program:</span>
                <span>{{ $student->collegeClass->name ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Cohort:</span>
                <span>{{ $student->cohort->name ?? 'N/A' }}</span>
            </div>
        </div>
        <div class="right">
            <div class="info-row">
                <span class="info-label">Academic Year:</span>
                <span>{{ $academic_year->name ?? 'All Years' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Semester:</span>
                <span>{{ $semester->name ?? 'All Semesters' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Date Generated:</span>
                <span>{{ $generated_at->format('F j, Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Time Generated:</span>
                <span>{{ $generated_at->format('g:i A') }}</span>
            </div>
        </div>
    </div>
    
    <div class="courses-section">
        <h2>Academic Record</h2>
        
        @if(count($transcript_entries) > 0)
            <table class="courses-table">
                <thead>
                    <tr>
                        <th style="width: 12%">Course Code</th>
                        <th style="width: 28%">Course Title</th>
                        <th style="width: 10%" class="numeric">Credit Hrs</th>
                        <th style="width: 12%" class="numeric">Online Score</th>
                        <th style="width: 12%" class="numeric">Offline Score</th>
                        <th style="width: 12%" class="numeric">Final Score</th>
                        <th style="width: 8%" class="numeric">Grade</th>
                        <th style="width: 8%" class="numeric">Grade Pts</th>
                        <th style="width: 8%" class="numeric">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transcript_entries as $entry)
                        <tr>
                            <td>{{ $entry['subject_code'] }}</td>
                            <td>{{ $entry['subject_name'] }}</td>
                            <td class="numeric">{{ $entry['credit_hours'] }}</td>
                            <td class="numeric">
                                @if($entry['online_score'])
                                    {{ number_format($entry['online_score'], 1) }}%
                                @else
                                    <span style="color: #999;">N/A</span>
                                @endif
                            </td>
                            <td class="numeric">
                                @if($entry['offline_score'])
                                    {{ number_format($entry['offline_score'], 1) }}%
                                @else
                                    <span style="color: #999;">N/A</span>
                                @endif
                            </td>
                            <td class="numeric">{{ number_format($entry['final_score'], 1) }}%</td>
                            <td class="numeric grade-{{ strtolower($entry['letter_grade']) }}">
                                {{ $entry['letter_grade'] }}
                            </td>
                            <td class="numeric">{{ number_format($entry['grade_points'], 1) }}</td>
                            <td class="numeric status-{{ strtolower($entry['status']) }}">
                                {{ $entry['status'] }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div style="text-align: center; padding: 20px; color: #666;">
                No academic records found for the specified criteria.
            </div>
        @endif
    </div>
    
    <div class="summary">
        <h3>Academic Summary</h3>
        <table class="summary-table">
            <tr>
                <td class="label">Total Credit Hours Attempted:</td>
                <td class="value">{{ $summary['total_credit_hours_attempted'] }}</td>
            </tr>
            <tr>
                <td class="label">Total Credit Hours Earned:</td>
                <td class="value">{{ $summary['total_credit_hours_earned'] }}</td>
            </tr>
            <tr>
                <td class="label">Total Grade Points Earned:</td>
                <td class="value">{{ number_format($summary['total_grade_points'], 2) }}</td>
            </tr>
            <tr>
                <td class="label">Semester GPA:</td>
                <td class="value gpa-highlight">{{ number_format($summary['semester_gpa'], 2) }}</td>
            </tr>
            <tr style="border-top: 2px solid #333;">
                <td class="label" style="font-size: 14px;">Cumulative GPA:</td>
                <td class="value gpa-highlight" style="font-size: 16px;">{{ number_format($summary['cumulative_gpa'], 2) }}</td>
            </tr>
        </table>
    </div>
    
    <div class="footer">
        <p><strong>Grading Scale:</strong> A = 90-100% (5.0 pts) | B = 80-89% (4.0 pts) | C = 70-79% (3.0 pts) | D = 60-69% (2.0 pts) | E = 50-59% (1.0 pts) | F = Below 50% (0.0 pts)</p>
        <p><strong>Note:</strong> This transcript is valid only when bearing the official seal and signature of the Registrar.</p>
        <p>Generated on {{ $generated_at->format('F j, Y \a\t g:i A') }} | Document ID: TR-{{ $student->student_id }}-{{ $generated_at->format('YmdHis') }}</p>
    </div>
</body>
</html>
