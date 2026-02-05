<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Invoice - {{ config('branding.institution.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            background-color: #f5f5f5;
            padding: 20px;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .invoice-header {
            background-color: #223275;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .invoice-logo {
            max-width: 150px;
            margin-bottom: 10px;
        }
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .invoice-subtitle {
            font-size: 18px;
            margin-bottom: 0;
        }
        .invoice-body {
            padding: 20px;
        }
        .student-info {
            margin-bottom: 20px;
        }
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .invoice-table th,
        .invoice-table td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        .invoice-table th {
            background-color: #f2f2f2;
            text-align: left;
        }
        .invoice-total {
            text-align: right;
            font-weight: bold;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #ddd;
        }
        .invoice-footer {
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
            .invoice-container {
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
            <i class="fas fa-print"></i> Print Invoice
        </button>
        <button onclick="window.history.back()" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </button>
    </div>

    @php
        $bill = App\Models\StudentFeeBill::with(['student', 'academicYear', 'semester', 'billItems.feeType'])->findOrFail($billId);
    @endphp

    <div class="invoice-container">
        <div class="invoice-header">
            @if(config('branding.logo.primary'))
                <img src="{{ asset(config('branding.logo.primary')) }}" alt="{{ config('branding.institution.name') }} Logo" class="invoice-logo">
            @endif
            <h1 class="invoice-title">FEE INVOICE</h1>
            <p class="invoice-subtitle">{{ config('branding.institution.name') }}</p>
        </div>
        
        <div class="invoice-body">
            <div class="row">
                <div class="col-md-6 student-info">
                    <h5>BILL TO:</h5>
                    <p><strong>Name:</strong> {{ $bill->student->full_name }}</p>
                    <p><strong>ID:</strong> {{ $bill->student->student_id }}</p>
                    <p><strong>Program:</strong> {{ $bill->student->collegeClass->name ?? 'Not Assigned' }}</p>
                </div>
                <div class="col-md-6 text-end">
                    <p><strong>Invoice #:</strong> {{ $bill->bill_reference }}</p>
                    <p><strong>Date:</strong> {{ $bill->billing_date->format('d-m-Y') }}</p>
                    <p><strong>Academic Year:</strong> {{ $bill->academicYear->name }}</p>
                    <p><strong>Semester:</strong> {{ $bill->semester->name }}</p>
                    <p><strong>Status:</strong> 
                        @if($bill->status == 'paid')
                            <span class="badge bg-success">Paid</span>
                        @elseif($bill->status == 'partially_paid')
                            <span class="badge bg-warning">Partially Paid</span>
                        @else
                            <span class="badge bg-danger">Pending</span>
                        @endif
                    </p>
                </div>
            </div>
            
            <table class="invoice-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Fee Type</th>
                        <th>Description</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bill->billItems as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->feeType->name }}</td>
                            <td>{{ $item->feeType->description }}</td>
                            <td class="text-end">GH₵ {{ number_format($item->amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end"><strong>Total Amount:</strong></td>
                        <td class="text-end"><strong>GH₵ {{ number_format($bill->total_amount, 2) }}</strong></td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-end"><strong>Amount Paid:</strong></td>
                        <td class="text-end"><strong>GH₵ {{ number_format($bill->amount_paid, 2) }}</strong></td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-end"><strong>Balance:</strong></td>
                        <td class="text-end"><strong>GH₵ {{ number_format($bill->balance, 2) }}</strong></td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-end"><strong>Payment Percentage:</strong></td>
                        <td class="text-end"><strong>{{ number_format($bill->payment_percentage, 2) }}%</strong></td>
                    </tr>
                </tfoot>
            </table>
            
            <div class="invoice-total">
                <p>Payment Status: 
                    @if($bill->status == 'paid')
                        <span class="text-success">PAID IN FULL</span>
                    @elseif($bill->status == 'partially_paid')
                        <span class="text-warning">PARTIALLY PAID</span>
                    @else
                        <span class="text-danger">UNPAID</span>
                    @endif
                </p>
            </div>
            
            <div class="mt-5">
                <h5>PAYMENT HISTORY</h5>
                @if($bill->payments->count() > 0)
                    <table class="invoice-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Method</th>
                                <th>Receipt No.</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bill->payments as $index => $payment)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $payment->payment_date->format('d-m-Y') }}</td>
                                    <td>{{ ucfirst($payment->payment_method) }}</td>
                                    <td>{{ $payment->receipt_number ?? 'N/A' }}</td>
                                    <td class="text-end">GH₵ {{ number_format($payment->amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-center">No payment records found.</p>
                @endif
            </div>
        </div>
        
        <div class="invoice-footer">
            <p>This is a computer-generated invoice and requires no signature.</p>
            <p>For inquiries, please contact the Finance Department.</p>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>