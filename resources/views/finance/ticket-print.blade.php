<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Entry Ticket - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            background-color: #f5f5f5;
            padding: 20px;
        }
        .ticket-container {
            max-width: 600px;
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
            max-width: 120px;
            margin-bottom: 10px;
        }
        .ticket-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .ticket-subtitle {
            font-size: 16px;
            margin-bottom: 0;
        }
        .ticket-body {
            padding: 20px;
        }
        .student-info {
            margin-bottom: 20px;
        }
        .qr-code {
            text-align: center;
            margin: 20px 0;
        }
        .qr-code img {
            max-width: 200px;
            height: auto;
        }
        .ticket-footer {
            background-color: #f8f9fa;
            text-align: center;
            padding: 15px;
            font-size: 14px;
            border-top: 1px solid #ddd;
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
        $ticket = App\Models\ExamEntryTicket::with(['student', 'examClearance.academicYear', 'examClearance.semester', 'examClearance.examType'])->findOrFail($ticketId);
    @endphp

    <div class="ticket-container">
        <div class="ticket-header">
            <img src="{{ asset('images/school-logo.png') }}" alt="School Logo" class="ticket-logo">
            <h1 class="ticket-title">EXAM ENTRY TICKET</h1>
            <p class="ticket-subtitle">{{ config('app.name') }}</p>
        </div>
        
        <div class="ticket-body">
            <div class="row">
                <div class="col-md-8 student-info">
                    <h5>STUDENT DETAILS:</h5>
                    <p><strong>Name:</strong> {{ $ticket->student->full_name }}</p>
                    <p><strong>ID:</strong> {{ $ticket->student->student_id }}</p>
                    <p><strong>Program:</strong> {{ $ticket->student->program->name ?? 'Not Assigned' }}</p>
                    <p><strong>Class:</strong> {{ $ticket->student->collegeClass->name ?? 'Not Assigned' }}</p>
                </div>
                <div class="col-md-4 text-end">
                    <p><strong>Ticket #:</strong> {{ $ticket->ticket_number }}</p>
                    <p><strong>Academic Year:</strong> {{ $ticket->examClearance->academicYear->name }}</p>
                    <p><strong>Semester:</strong> {{ $ticket->examClearance->semester->name }}</p>
                    <p><strong>Exam Type:</strong> {{ $ticket->examClearance->examType->name }}</p>
                </div>
            </div>
            
            <div class="qr-code">
                <h5 class="mb-3">SCAN FOR VERIFICATION</h5>
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ $ticket->qr_code }}" alt="QR Code">
                <p class="mt-2 text-muted">Clearance Code: {{ $ticket->examClearance->clearance_code }}</p>
            </div>
            
            <div class="alert alert-info">
                <p class="mb-0"><strong>Important:</strong> This ticket must be presented at the examination hall. You will not be permitted to sit for the exam without valid verification.</p>
            </div>
            
            <div class="alert alert-warning">
                <p class="mb-0"><strong>Expires:</strong> {{ $ticket->expires_at ? $ticket->expires_at->format('d-m-Y H:i') : 'Not set' }}</p>
            </div>
        </div>
        
        <div class="ticket-footer">
            <p>This is an official exam entry ticket. Counterfeiting is strictly prohibited.</p>
            <p>For inquiries, please contact the Examinations Office.</p>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>