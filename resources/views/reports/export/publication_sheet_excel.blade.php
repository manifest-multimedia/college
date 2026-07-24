@php
    $colspan = 3 + count($data['semesters']) + 3; // 3 info cols + semesters + 3 summary cols
@endphp
<table>
    <thead>
    <tr>
        <th colspan="{{ $colspan }}" style="text-align: center; font-weight: bold; font-size: 14px; background-color: #fdf5e6; color: #000080;">
            {{ strtoupper(config('branding.institution.name', config('app.name', 'METHODIST HEALTH TRAINING INSTITUTE - AFOSU'))) }}
        </th>
    </tr>
    <tr>
        <th colspan="{{ $colspan }}" style="text-align: center; font-weight: bold; font-size: 12px; background-color: #fdf5e6;">
            CUMMULATIVE GRADE POINT PUBLICATION SHEET
        </th>
    </tr>
    <tr>
        <th colspan="{{ $colspan }}" style="text-align: center; font-weight: bold; font-size: 12px; background-color: #f5deb3;">
            PROGRAMME: {{ strtoupper($data['program'] ?? 'N/A') }}
        </th>
    </tr>
    <tr>
        <th></th>
        <th></th>
        <th></th>
        @foreach($data['semesters'] as $semesterId => $semesterName)
            <th style="font-weight: bold; text-align: center; background-color: #f5f5dc;">{{ strtoupper($semesterName) }}</th>
        @endforeach
        <th></th>
        <th></th>
        <th></th>
    </tr>
    <tr>
        <th style="font-weight: bold; border: 1px solid #000000; background-color: #e0e0e0;">SERIAL NO</th>
        <th style="font-weight: bold; border: 1px solid #000000; background-color: #e0e0e0;">INDEX NUMBER</th>
        <th style="font-weight: bold; border: 1px solid #000000; background-color: #e0e0e0;">NAME</th>
        @foreach($data['semesters'] as $semesterId => $semesterName)
            <th style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #e0e0e0;">GPA</th>
        @endforeach
        <th style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #ffd700;">CGPA</th>
        <th style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #ffd700;">CLASS DESIGNATION</th>
        <th style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #ffd700;">REMARKS</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data['students'] as $index => $student)
        @php
            $remarkColor = '#ffffff';
            if ($student['remarks'] === 'REPEATED') $remarkColor = '#ffe4e1';
            if ($student['remarks'] === 'DISMISSED') $remarkColor = '#dda0dd';
            if ($student['remarks'] === 'PASS') $remarkColor = '#e8f5e9';
        @endphp
        <tr>
            <td style="text-align: center; border: 1px solid #000000;">{{ $index + 1 }}</td>
            <td style="border: 1px solid #000000;">{{ $student['index_number'] }}</td>
            <td style="border: 1px solid #000000;">{{ $student['name'] }}</td>
            
            @foreach($data['semesters'] as $semesterId => $semesterName)
                @php
                    $gpa = isset($student['semester_gpas'][$semesterId]) ? $student['semester_gpas'][$semesterId]['gpa'] : '';
                @endphp
                <td style="text-align: center; border: 1px solid #000000; font-weight: bold;">
                    {{ $gpa }}
                </td>
            @endforeach
            
            <td style="text-align: center; border: 1px solid #000000; font-weight: bold;">{{ $student['cgpa'] }}</td>
            <td style="text-align: center; border: 1px solid #000000; font-weight: bold; background-color: {{ $remarkColor }};">{{ $student['class_designation'] }}</td>
            <td style="text-align: center; border: 1px solid #000000; font-weight: bold; background-color: {{ $remarkColor }};">{{ $student['remarks'] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
