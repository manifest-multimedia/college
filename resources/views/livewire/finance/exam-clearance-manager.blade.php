<div>
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="card-title">
                    <i class="fas fa-clipboard-check"></i> Exam Clearance Management
                </h1>
            </div>
        </div>
        <div class="card-body">
            <!-- Filters Section -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="academicYearId">Academic Year</label>
                        <select wire:model.live="academicYearId" class="form-control">
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="semesterId">Semester</label>
                        <select wire:model.live="semesterId" class="form-control">
                            @foreach($semesters as $semester)
                                <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="examTypeId">Exam Type</label>
                        <select wire:model.live="examTypeId" class="form-control">
                            @foreach($examTypes as $examType)
                                <option value="{{ $examType->id }}">{{ $examType->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="searchTerm">Search Student</label>
                        <input type="text" wire:model.live.debounce.300ms="searchTerm" class="form-control" placeholder="Name or ID...">
                    </div>
                </div>
            </div>
            
            <!-- Students List -->
            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Class</th>
                            <th>Payment Status</th>
                            <th>Payment %</th>
                            <th>Clearance Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                            @php
                                $feeBill = $student->feeBills->first();
                                $clearance = $student->examClearances->first();
                                $paymentPercentage = $feeBill ? $feeBill->payment_percentage : 0;
                                $examType = $examTypes->where('id', $examTypeId)->first();
                                $requiredPercentage = $examType ? $examType->payment_threshold : 0;
                                $isEligible = $paymentPercentage >= $requiredPercentage;
                            @endphp
                            <tr>
                                <td>{{ $student->student_id }}</td>
                                <td>{{ $student->full_name }}</td>
                                <td>{{ $student->collegeClass->name ?? 'Not Assigned' }}</td>
                                <td>
                                    @if($feeBill)
                                        <span class="badge bg-{{ $feeBill->status === 'paid' ? 'success' : ($feeBill->status === 'partially_paid' ? 'warning' : 'danger') }}">
                                            {{ ucfirst(str_replace('_', ' ', $feeBill->status)) }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">Not Billed</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="progress">
                                        <div class="progress-bar {{ $isEligible ? 'bg-success' : 'bg-danger' }}" role="progressbar"
                                            style="width: {{ $paymentPercentage }}%" aria-valuenow="{{ $paymentPercentage }}" aria-valuemin="0"
                                            aria-valuemax="100">{{ number_format($paymentPercentage, 1) }}%</div>
                                    </div>
                                    <small class="text-muted">Required: {{ $requiredPercentage }}%</small>
                                </td>
                                <td>
                                    @if($clearance)
                                        @if($clearance->is_cleared)
                                            <span class="badge bg-success">Cleared</span>
                                            @if($clearance->is_manual_override)
                                                <span class="badge bg-warning" title="{{ $clearance->override_reason }}">Manual Override</span>
                                            @endif
                                        @else
                                            <span class="badge bg-danger">Not Cleared</span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    @if($clearance && $clearance->is_cleared)
                                        <button wire:click="revokeClearance({{ $clearance->id }})" class="btn btn-sm btn-danger">
                                            <i class="fas fa-ban"></i> Revoke
                                        </button>
                                        <a href="{{ route('finance.exam.tickets', ['clearanceId' => $clearance->id]) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-qrcode"></i> Tickets
                                        </a>
                                    @else
                                        @if($isEligible)
                                            <button wire:click="openClearanceModal({{ $student->id }})" class="btn btn-sm btn-primary">
                                                <i class="fas fa-check-circle"></i> Clear
                                            </button>
                                        @else
                                            <button wire:click="openOverrideModal({{ $student->id }})" class="btn btn-sm btn-warning">
                                                <i class="fas fa-exclamation-triangle"></i> Override
                                            </button>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No students found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                
                <div class="mt-4">
                    {{ $students->links() }}
                </div>
            </div>
            
            <div class="mt-4">
                <h4>Cleared Students for {{ $examTypes->where('id', $examTypeId)->first()->name ?? 'Selected Exam' }}</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Class</th>
                                <th>Clearance Date</th>
                                <th>Method</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($clearedStudents as $clearance)
                                <tr>
                                    <td>{{ $clearance->student->student_id }}</td>
                                    <td>{{ $clearance->student->full_name }}</td>
                                    <td>{{ $clearance->student->collegeClass->name ?? 'Not Assigned' }}</td>
                                    <td>{{ $clearance->cleared_at->format('d M Y, h:i A') }}</td>
                                    <td>
                                        @if($clearance->is_manual_override)
                                            <span class="badge bg-warning">Manual Override</span>
                                        @else
                                            <span class="badge bg-success">Normal</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No cleared students found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <a href="#" onclick="window.print()" class="btn btn-secondary">
                        <i class="fas fa-print"></i> Print Clearance List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Clearance Modal -->
    <div class="modal fade" id="clearanceModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Exam Clearance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($selectedStudentId)
                        <p>Are you sure you want to clear this student for the exam?</p>
                        <p>Student has met the payment requirement of {{ $examTypes->where('id', $examTypeId)->first()->payment_threshold ?? '0' }}%.</p>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" wire:click="clearStudent" class="btn btn-primary">Clear Student</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Manual Override Modal -->
    <div class="modal fade" id="overrideModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Manual Clearance Override</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> Warning: This student has not met the required payment threshold.
                    </div>
                    
                    <div class="form-group">
                        <label for="overrideReason">Reason for Override (Required)</label>
                        <textarea wire:model="overrideReason" class="form-control @error('overrideReason') is-invalid @enderror" rows="3"></textarea>
                        @error('overrideReason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" wire:click="clearStudent" class="btn btn-warning">Override & Clear</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('showClearanceModal', () => {
                new bootstrap.Modal(document.getElementById('clearanceModal')).show();
            });
            
            Livewire.on('showOverrideModal', () => {
                new bootstrap.Modal(document.getElementById('overrideModal')).show();
            });
        });
    </script>
    @endpush
</div>