@props([
    'title' => 'Counter',
    'value' => '0',
    'icon' => 'fas fa-chart-bar',
    'color' => 'primary' // primary, success, warning, danger, info
])

<div class="card shadow-sm h-100">
    <div class="card-body d-flex align-items-center" style="min-height: 100px;">
        <div class="d-flex align-items-center w-100">
            <div class="symbol symbol-50px me-5">
                <span class="symbol-label bg-light-{{ $color }}">
                    <i class="{{ $icon }} fs-2x text-{{ $color }}"></i>
                </span>
            </div>
            <div class="d-flex flex-column">
                <h4 class="card-title mb-1 fs-6" style="font-weight:500">{{ $title }}</h4>
                <span class="fs-2 fw-bold text-gray-900">{{ $value }}</span>
            </div>
        </div>
    </div>
</div>