<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $report->getName() }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
        }
        .header-title {
            font-size: 14px;
            font-weight: bold;
            background-color: #fdf5e6;
            color: #000080;
        }
        .header-subtitle {
            font-size: 12px;
            font-weight: bold;
            background-color: #fdf5e6;
        }
        .header-programme {
            font-size: 12px;
            font-weight: bold;
            background-color: #f5deb3;
        }
        .bg-year { background-color: #f5f5dc; font-weight: bold; }
        .bg-sem { background-color: #e8f4f8; font-weight: bold; }
        .bg-col { background-color: #e0e0e0; font-weight: bold; }
        .bg-summary { background-color: #ffd700; font-weight: bold; }
        
        .empty-cell { border: none; }
        
        .remark-repeated { background-color: #ffe4e1; }
        .remark-dismissed { background-color: #dda0dd; }
        .remark-pass { background-color: #e8f5e9; }
    </style>
</head>
<body>
    @php
        $totalSemesters = 0;
        foreach($report->reportSemesters ?? [] as $semesters) {
            $totalSemesters += count($semesters);
        }
        $colspan = 3 + $totalSemesters + 3; // 3 info cols + semesters + 3 summary cols
    @endphp

    <table>
        <thead>
            <tr>
                <th colspan="{{ $colspan }}" class="header-title">
                    {{ strtoupper(config('branding.institution.name', config('app.name', 'METHODIST HEALTH TRAINING INSTITUTE - AFOSU'))) }}
                </th>
            </tr>
            <tr>
                <th colspan="{{ $colspan }}" class="header-subtitle">
                    CUMMULATIVE GRADE POINT PUBLICATION SHEET
                </th>
            </tr>
            <tr>
                <th colspan="{{ $colspan }}" class="header-programme">
                    PROGRAMME: {{ strtoupper($report->reportProgram ?? 'N/A') }}
                </th>
            </tr>
            <tr>
                <th class="empty-cell"></th>
                <th class="empty-cell"></th>
                <th class="empty-cell"></th>
                @foreach($report->reportSemesters as $yearLabel => $semesters)
                    <th colspan="{{ count($semesters) }}" class="bg-year">{{ strtoupper($yearLabel) }}</th>
                @endforeach
                <th class="empty-cell"></th>
                <th class="empty-cell"></th>
                <th class="empty-cell"></th>
            </tr>
            <tr>
                <th class="empty-cell"></th>
                <th class="empty-cell"></th>
                <th class="empty-cell"></th>
                @foreach($report->reportSemesters as $yearLabel => $semesters)
                    @foreach($semesters as $semesterKey => $semesterName)
                        <th class="bg-sem">{{ strtoupper($semesterName) }}</th>
                    @endforeach
                @endforeach
                <th class="empty-cell"></th>
                <th class="empty-cell"></th>
                <th class="empty-cell"></th>
            </tr>
            <tr>
                <th class="bg-col">SERIAL NO</th>
                <th class="bg-col">INDEX NUMBER</th>
                <th class="bg-col">NAME</th>
                @foreach($report->reportSemesters as $yearLabel => $semesters)
                    @foreach($semesters as $semesterKey => $semesterName)
                        <th class="bg-col">GPA</th>
                    @endforeach
                @endforeach
                <th class="bg-summary">CGPA</th>
                <th class="bg-summary">CLASS DESIGNATION</th>
                <th class="bg-summary">REMARKS</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $student)
                @php
                    $remarkClass = '';
                    if ($student['remarks'] === 'REPEATED') $remarkClass = 'remark-repeated';
                    if ($student['remarks'] === 'DISMISSED') $remarkClass = 'remark-dismissed';
                    if ($student['remarks'] === 'PASS') $remarkClass = 'remark-pass';
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td style="text-align: left;">{{ $student['index_number'] }}</td>
                    <td style="text-align: left;">{{ $student['name'] }}</td>
                    
                    @foreach($report->reportSemesters as $yearLabel => $semesters)
                        @foreach($semesters as $semesterKey => $semesterName)
                            <td style="font-weight: bold;">
                                {{ $student[$semesterKey] ?? '' }}
                            </td>
                        @endforeach
                    @endforeach
                    
                    <td style="font-weight: bold;">{{ $student['cgpa'] }}</td>
                    <td class="{{ $remarkClass }}" style="font-weight: bold;">{{ $student['class_designation'] }}</td>
                    <td class="{{ $remarkClass }}" style="font-weight: bold;">{{ $student['remarks'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
