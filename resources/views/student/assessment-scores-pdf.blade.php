<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Student Results</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
        }
        
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 50px;
            color: rgba(200, 200, 200, 0.2);
            font-weight: bold;
            z-index: -1;
            text-align: center;
            line-height: 1.2;
            width: 100%;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #333;
        }
        
        .header .logo {
            max-width: 120px;
            max-height: 80px;
            margin-bottom: 10px;
        }
        
        .header h1 {
            font-size: 20px;
            margin-bottom: 5px;
            color: #000;
        }
        
        .header p {
            font-size: 10px;
            color: #666;
            margin: 2px 0;
        }
        
        .student-info {
            margin-bottom: 20px;
            background-color: #f8f9fa;
            padding: 12px;
            border-radius: 4px;
        }
        
        .student-info table {
            width: 100%;
        }
        
        .student-info td {
            padding: 4px;
            font-size: 10px;
        }
        
        .student-info td:first-child {
            font-weight: bold;
            width: 30%;
        }
        
        .notice {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-left: 4px solid #ffc107;
            padding: 10px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
            font-size: 11px;
            color: #856404;
        }
        
        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .results-table thead th {
            background-color: #343a40;
            color: white;
            padding: 8px;
            text-align: center;
            font-size: 10px;
            border: 1px solid #dee2e6;
        }
        
        .results-table tbody td {
            padding: 7px;
            border: 1px solid #dee2e6;
            font-size: 10px;
        }
        
        .results-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .text-center {
            text-align: center;
        }
        
        .grade-badge {
            display: inline-block;
            padding: 3px 8px;
            font-weight: bold;
            font-size: 11px;
            color: #000;
            border: 1px solid #dee2e6;
            border-radius: 3px;
        }
        
        .status-pass {
            color: #28a745;
            font-weight: bold;
        }
        
        .status-resit {
            color: #ffc107;
            font-weight: bold;
        }
        
        .status-carryover, .status-fail {
            color: #dc3545;
            font-weight: bold;
        }
        
        .summary-section {
            margin-top: 25px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        
        .summary-section h3 {
            font-size: 14px;
            margin-bottom: 12px;
            color: #000;
            border-bottom: 2px solid #333;
            padding-bottom: 5px;
        }
        
        .summary-grid {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        
        .summary-item {
            display: table-cell;
            text-align: center;
            padding: 10px;
            background-color: white;
            border: 1px solid #dee2e6;
            vertical-align: middle;
        }
        
        .summary-label {
            font-size: 9px;
            color: #666;
            text-transform: uppercase;
        }
        
        .summary-value {
            font-size: 18px;
            font-weight: bold;
            color: #000;
            margin-top: 3px;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #666;
            padding: 10px;
            border-top: 1px solid #dee2e6;
        }
        
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <!-- Watermark -->
    <div class="watermark">
        NOT AN<br>
        OFFICIAL<br>
        TRANSCRIPT
    </div>
    
    <!-- Header -->
    <div class="header">
        @if(config('branding.logo.primary'))
            <img src="{{ public_path(config('branding.logo.primary')) }}" alt="Logo" class="logo">
        @endif
        <h1>{{ config('school.name', 'College Management System') }}</h1>
        <p>{{ config('school.address', '') }}</p>
        <p style="margin-top: 8px; font-size: 14px; font-weight: bold;">STUDENT ACADEMIC RESULTS</p>
    </div>
    
    <!-- Notice -->
    <div class="notice">
        ⚠ THIS IS NOT AN OFFICIAL TRANSCRIPT ⚠
    </div>
    
    <!-- Student Information -->
    <div class="student-info">
        <table>
            <tr>
                <td>Student Name:</td>
                <td>{{ $student->full_name ?? $student->name }}</td>
                <td>Student ID:</td>
                <td>{{ $student->student_id }}</td>
            </tr>
            <tr>
                <td>Email:</td>
                <td>{{ $student->email }}</td>
                <td>Program:</td>
                <td>{{ $student->collegeClass->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Generated Date:</td>
                <td>{{ $generated_date }}</td>
                <td>Cohort:</td>
                <td>{{ $student->cohort->name ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>
    
    <!-- Results Table -->
    <table class="results-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 12%;">Course Code</th>
                <th style="width: 35%;">Course Name</th>
                <th style="width: 10%;">Credit Hrs</th>
                <th style="width: 10%;">Grade</th>
                <th style="width: 10%;">Grade Pts</th>
                <th style="width: 18%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($scores as $index => $score)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center"><strong>{{ $score['course_code'] }}</strong></td>
                <td>{{ $score['course_name'] }}</td>
                <td class="text-center">{{ $score['credit_hours'] }}</td>
                <td class="text-center">
                    <span class="grade-badge">
                        {{ $score['grade_letter'] }}
                    </span>
                </td>
                <td class="text-center"><strong>{{ number_format($score['grade_points'], 1) }}</strong></td>
                <td class="text-center status-{{ strtolower($score['status']) }}">{{ $score['status'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <!-- Summary Section -->
    <div class="summary-section">
        <h3>Academic Summary</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Total Credits</div>
                <div class="summary-value">{{ $summary['total_credits'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">CGPA</div>
                <div class="summary-value" style="color: {{ $summary['cgpa'] >= 3.0 ? '#28a745' : ($summary['cgpa'] >= 2.0 ? '#ffc107' : '#dc3545') }}">
                    {{ number_format($summary['cgpa'], 2) }}
                </div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Overall Remark</div>
                <div class="summary-value" style="font-size: 14px;">{{ $summary['overall_remark'] }}</div>
            </div>
        </div>
        <div style="margin-top: 10px; font-size: 10px; text-align: center;">
            Passed: <strong>{{ $summary['passed_courses'] }}</strong> | 
            Failed: <strong>{{ $summary['failed_courses'] }}</strong>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <p><strong>IMPORTANT NOTICE:</strong> This document is for informational purposes only and does not constitute an official academic transcript.</p>
        <p>For official transcripts, please contact the Academic Registry.</p>
        <p>Generated: {{ $generated_date }}</p>
    </div>
</body>
</html>
