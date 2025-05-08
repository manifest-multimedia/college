@props([
    'examSession',
    'hours',
    'minutes',
    'seconds'
])

<div {{ $attributes->merge(['class' => 'exam-timer-container']) }}>
    <div class="card shadow-sm">
        <div class="card-header bg-primary">
            <div class="card-title">
                <h5 class="text-white mb-0">
                    <i class="ki-duotone ki-timer fs-2 me-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    Exam Timer
                </h5>
            </div>
        </div>
        
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="timer-display p-3 text-center">
                        <h1 id="exam-timer" class="display-4 fw-bold mb-0">
                            {{ sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds) }}
                        </h1>
                        
                        @if($examSession->has_extra_time)
                            <div class="d-flex align-items-center justify-content-center mt-2">
                                <span class="badge bg-success py-2 px-3">
                                    <i class="ki-duotone ki-plus-square fs-5 me-1">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    Extra time added: {{ $examSession->extra_time_minutes }} minutes
                                </span>
                            </div>
                        @endif
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-6">
                            <div class="timer-info">
                                <span class="text-muted fs-7 d-block">Started:</span>
                                <span class="fs-6 fw-semibold">{{ $examSession->started_at->format('h:i A') }}</span>
                            </div>
                        </div>
                        <div class="col-6 text-end">
                            <div class="timer-info">
                                <span class="text-muted fs-7 d-block">Ends:</span>
                                <span class="fs-6 fw-semibold">{{ $examSession->adjustedCompletionTime->format('h:i A') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>