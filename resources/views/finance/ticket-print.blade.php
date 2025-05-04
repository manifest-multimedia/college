<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Entry Ticket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            background-color: #f5f5f5;
            padding: 20px;
        }
        .ticket-container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .ticket-header {
            background-color: #223275;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .ticket-logo {
            max-width: 150px;
            margin-bottom: 10px;
        }
        .ticket-title {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .ticket-subtitle {
            font-size: 18px;
            margin-bottom: 0;
        }
        .ticket-body {
            padding: 20px;
        }
        .student-info {
            margin-bottom: 20px;
        }
        .ticket-image {
            text-align: center;
            margin-bottom: 20px;
        }
        .student-photo {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #223275;
        }
        .qr-code {
            text-align: center;
            margin: 20px 0;
        }
        .qr-code img {
            max-width: 200px;
        }
        .warning-text {
            font-style: italic;
            color: #666;
            text-align: center;
            font-size: 12px;
        }
        .instructions {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .ticket-footer {
            background-color: #223275;
            color: white;
            text-align: center;
            padding: 15px;
            font-size: 14px;
        }
        table.info-table {
            width: 100%;
            margin-bottom: 20px;
        }
        table.info-table td {
            padding: 8px;
            vertical-align: top;
        }
        table.info-table td:first-child {
            font-weight: bold;
            width: 30%;
        }
        .ticket-number {
            font-family: monospace;
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 2px;
        }
        .print-button {
            text-align: center;
            margin-bottom: 20px;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 0;
                background-color: white;
            }
            .ticket-container {
                box-shadow: none;
                margin: 0;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>
    <div class="print-button no-print">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print"></i> Print Ticket
        </button>
        <button onclick="window.history.back()" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </button>
    </div>

    @php
        $ticket = App\Models\ExamEntryTicket::with(['student', 'exam', 'examClearance'])->findOrFail($ticketId);
    @endphp

    <div class="ticket-container">
        <div class="ticket-header">
            <img src="{{ asset('images/school-logo.png') }}" alt="School Logo" class="ticket-logo">
            <h1 class="ticket-title">EXAM ENTRY TICKET</h1>
            <p class="ticket-subtitle">{{ config('app.name') }}</p>
        </div>
        
        <div class="ticket-body">
            <div class="row">
                <div class="col-md-4 ticket-image">
                    @if($ticket->student->photo)
                        <img src="{{ asset('storage/' . $ticket->student->photo) }}" alt="Student Photo" class="student-photo">
                    @else
                        <div class="student-photo d-flex justify-content-center align-items-center bg-secondary text-white">
                            <i class="fas fa-user fa-3x"></i>
                        </div>
                    @endif
                </div>
                <div class="col-md-8 student-info">
                    <h3>{{ $ticket->student->full_name }}</h3>
                    <table class="info-table">
                        <tr>
                            <td>Student ID:</td>
                            <td>{{ $ticket->student->student_id }}</td>
                        </tr>
                        <tr>
                            <td>Program:</td>
                            <td>{{ $ticket->student->program->name ?? 'Not Assigned' }}</td>
                        </tr>
                        <tr>
                            <td>Class:</td>
                            <td>{{ $ticket->student->collegeClass->name ?? 'Not Assigned' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <hr>
            
            <h4 class="text-center mb-4">EXAM DETAILS</h4>
            
            <table class="info-table">
                <tr>
                    <td>Exam Title:</td>
                    <td>{{ $ticket->exam->title }}</td>
                </tr>
                <tr>
                    <td>Exam Date:</td>
                    <td>{{ $ticket->exam->exam_date ? $ticket->exam->exam_date->format('d F, Y') : 'TBD' }}</td>
                </tr>
                <tr>
                    <td>Academic Year:</td>
                    <td>{{ $ticket->examClearance->academicYear->name }}</td>
                </tr>
                <tr>
                    <td>Semester:</td>
                    <td>{{ $ticket->examClearance->semester->name }}</td>
                </tr>
                <tr>
                    <td>Ticket Number:</td>
                    <td class="ticket-number">{{ $ticket->ticket_number }}</td>
                </tr>
                <tr>
                    <td>Valid Until:</td>
                    <td>{{ $ticket->expires_at ? $ticket->expires_at->format('d F, Y h:i A') : 'No Expiry' }}</td>
                </tr>
            </table>
            
            <div class="qr-code">
                {!! QrCode::size(200)->generate($ticket->qr_code) !!}
                <p class="mt-3 warning-text">This QR code is required for exam entry verification.<br>Do not share this ticket with anyone.</p>
            </div>
            
            <div class="instructions">
                <h5>Instructions:</h5>
                <ol>
                    <li>Print this ticket and bring it to the exam venue.</li>
                    <li>You must present this ticket along with your student ID card.</li>
                    <li>Arrive at least 30 minutes before the scheduled exam time.</li>
                    <li>This ticket is only valid for the specified exam.</li>
                    <li>Lost tickets must be reported immediately to the Examination Office.</li>
                </ol>
            </div>
        </div>
        
        <div class="ticket-footer">
            <p>This ticket was issued on {{ $ticket->created_at->format('d F, Y h:i A') }}</p>
            <p>For verification scan the QR code or contact the Examination Office.</p>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>