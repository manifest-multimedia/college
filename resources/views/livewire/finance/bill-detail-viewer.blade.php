<div>
    <!-- Bill Details Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="card-title mb-0">
                    <i class="fas fa-file-invoice"></i> Student Bill Details
                </h1>
                @if(!$loading && $bill)
                <div>
                    <button wire:click="printBill" class="btn btn-light btn-sm">
                        <i class="fas fa-print"></i> Print Bill
                    </button>
                    <button wire:click="recordPayment" class="btn btn-success btn-sm ms-2">
                        <i class="fas fa-money-bill-wave"></i> Record Payment
                    </button>
                </div>
                @endif
            </div>
        </div>
        <div class="card-body">
            @if(session()->has('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($loading)
                <div class="d-flex justify-content-center align-items-center p-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <span class="ms-2">Loading bill details...</span>
                </div>
            @elseif(!$bill)
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> Bill not found or has been deleted.
                </div>
            @else
                <!-- Student & Bill Information -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2 mb-3">Student Information</h5>
                        <table class="table table-sm">
                            <tr>
                                <th style="width: 150px;">Student ID:</th>
                                <td>{{ $bill->student->student_id }}</td>
                            </tr>
                            <tr>
                                <th>Name:</th>
                                <td>{{ $bill->student->first_name }} {{ $bill->student->last_name }}</td>
                            </tr>
                            <tr>
                                <th>Program:</th>
                                <td>{{ $bill->student->collegeClass->name ?? 'Not Assigned' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2 mb-3">Bill Information</h5>
                        <table class="table table-sm">
                            <tr>
                                <th style="width: 150px;">Bill Number:</th>
                                <td>{{ $bill->bill_reference }}</td>
                            </tr>
                            <tr>
                                <th>Academic Year:</th>
                                <td>{{ $bill->academicYear->name }}</td>
                            </tr>
                            <tr>
                                <th>Semester:</th>
                                <td>{{ $bill->semester->name }}</td>
                            </tr>
                            <tr>
                                <th>Date Generated:</th>
                                <td>{{ $bill->created_at->format('d M, Y') }}</td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    @if($bill->status === 'paid')
                                        <span class="badge bg-success">Paid</span>
                                    @elseif($bill->status === 'partially_paid')
                                        <span class="badge bg-warning">Partially Paid</span>
                                    @else
                                        <span class="badge bg-danger">Unpaid</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Bill Items -->
                <h5 class="border-bottom pb-2 mb-3">Fee Items</h5>
                <div class="table-responsive mb-4">
                    <table class="table table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Fee Type</th>
                                <th>Description</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bill->billItems as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->feeType->name }}</td>
                                    <td>{{ $item->description ?? $item->feeType->description }}</td>
                                    <td class="text-end">GH₵ {{ number_format($item->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No fee items found</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="fw-bold">
                            <tr>
                                <td colspan="3" class="text-end">Total:</td>
                                <td class="text-end">GH₵ {{ number_format($bill->total_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end">Amount Paid:</td>
                                <td class="text-end text-success">GH₵ {{ number_format($totalPaid, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end">Balance:</td>
                                <td class="text-end {{ $balance > 0 ? 'text-danger' : 'text-success' }}">
                                    GH₵ {{ number_format($balance, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Payment History -->
                <h5 class="border-bottom pb-2 mb-3">Payment History</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Receipt No.</th>
                                <th>Date</th>
                                <th>Payment Method</th>
                                <th>Reference</th>
                                <th>Recorded By</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $index => $payment)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $payment->receipt_number }}</td>
                                    <td>{{ $payment->payment_date->format('d M, Y') }}</td>
                                    <td>{{ $payment->payment_method }}</td>
                                    <td>{{ $payment->reference_number }}</td>
                                    <td>{{ $payment->recordedBy->name ?? 'System' }}</td>
                                    <td class="text-end">GH₵ {{ number_format($payment->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No payment records found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
