<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Students Export</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin-bottom: 5px;
        }
        .subheader {
            text-align: center;
            margin-bottom: 20px;
            font-style: italic;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .badge {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 10px;
        }
        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }
        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        .badge-secondary {
            background-color: #e2e3e5;
            color: #383d41;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Student Information Report</h1>
        <p>Export Date: {{ date('F j, Y, g:i a') }}</p>
    </div>
    
    <div class="subheader">
        <p>Total Records: {{ count($students) }}</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Student Name</th>
                <th>Email</th>
                <th>Program</th>
                <th>Cohort</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($students as $student)
                <tr>
                    <td>{{ $student->student_id }}</td>
                    <td>{{ $student->last_name }} {{ $student->first_name }} {{ $student->other_name }}</td>
                    <td>{{ $student->email }}</td>
                    <td>{{ $student->collegeClass->name ?? 'N/A' }}</td>
                    <td>{{ $student->cohort->name ?? 'N/A' }}</td>
                    <td>
                        @if($student->status == 'Active')
                            <span class="badge badge-success">{{ $student->status ?? 'Active' }}</span>
                        @elseif($student->status == 'Inactive')
                            <span class="badge badge-danger">{{ $student->status }}</span>
                        @elseif($student->status == 'Pending')
                            <span class="badge badge-warning">{{ $student->status }}</span>
                        @else
                            <span class="badge badge-secondary">{{ $student->status ?? 'Unknown' }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">No students found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="footer">
        <p>This is an automatically generated report. Please contact the administration for any questions.</p>
    </div>
</body>
</html>