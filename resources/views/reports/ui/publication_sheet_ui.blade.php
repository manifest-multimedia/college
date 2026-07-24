<div class="table-responsive">
    <table class="table align-middle table-row-dashed fs-6 gy-5 table-bordered">
        <thead>
            <!-- Year Header -->
            <tr class="text-center text-gray-800 fw-bold fs-7 text-uppercase gs-0 bg-light">
                <th colspan="3" class="border-end border-bottom-0"></th>
                @foreach($report->reportSemesters as $yearLabel => $semesters)
                    <th colspan="{{ count($semesters) }}" class="border-end" style="background-color: #f5f5dc;">
                        {{ strtoupper($yearLabel) }}
                    </th>
                @endforeach
                <th colspan="3" class="border-start-0 border-bottom-0"></th>
            </tr>
            
            <!-- Semester Header -->
            <tr class="text-center text-gray-800 fw-bold fs-7 text-uppercase gs-0 bg-light">
                <th colspan="3" class="border-end border-top-0 border-bottom-0"></th>
                @foreach($report->reportSemesters as $yearLabel => $semesters)
                    @foreach($semesters as $semesterKey => $semesterName)
                        <th class="border-end" style="background-color: #e8f4f8;">
                            {{ strtoupper($semesterName) }}
                        </th>
                    @endforeach
                @endforeach
                <th colspan="3" class="border-start-0 border-top-0 border-bottom-0"></th>
            </tr>
            
            <!-- Column Header -->
            <tr class="text-center text-gray-800 fw-bold fs-7 text-uppercase gs-0" style="background-color: #e0e0e0;">
                <th class="border-end">SERIAL NO</th>
                <th class="border-end text-start">INDEX NUMBER</th>
                <th class="border-end text-start">NAME</th>
                @foreach($report->reportSemesters as $yearLabel => $semesters)
                    @foreach($semesters as $semesterKey => $semesterName)
                        <th class="border-end">GPA</th>
                    @endforeach
                @endforeach
                <th class="border-end" style="background-color: #ffd700;">CGPA</th>
                <th class="border-end" style="background-color: #ffd700;">CLASS DESIGNATION</th>
                <th style="background-color: #ffd700;">REMARKS</th>
            </tr>
        </thead>
        <tbody class="fw-semibold text-gray-600">
            @foreach($data as $index => $student)
                @php
                    $remarkBg = '';
                    $remarkText = 'text-gray-800';
                    if ($student['remarks'] === 'REPEATED') {
                        $remarkBg = 'bg-light-danger';
                        $remarkText = 'text-danger';
                    }
                    if ($student['remarks'] === 'DISMISSED') {
                        $remarkBg = 'bg-light-dark';
                    }
                    if ($student['remarks'] === 'PASS') {
                        $remarkBg = 'bg-light-success';
                        $remarkText = 'text-success';
                    }
                @endphp
                <tr class="text-center">
                    <td class="border-end">{{ $index + 1 }}</td>
                    <td class="border-end text-start">{{ $student['index_number'] }}</td>
                    <td class="border-end text-start">{{ $student['name'] }}</td>
                    
                    @foreach($report->reportSemesters as $yearLabel => $semesters)
                        @foreach($semesters as $semesterKey => $semesterName)
                            <td class="border-end fw-bold text-gray-800">
                                {{ $student[$semesterKey] ?? '-' }}
                            </td>
                        @endforeach
                    @endforeach
                    
                    <td class="border-end fw-bold text-gray-800">{{ $student['cgpa'] }}</td>
                    <td class="border-end fw-bold {{ $remarkBg }} {{ $remarkText }}">{{ $student['class_designation'] }}</td>
                    <td class="fw-bold {{ $remarkBg }} {{ $remarkText }}">{{ $student['remarks'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
