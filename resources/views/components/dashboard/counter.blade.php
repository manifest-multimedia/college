@props([
    'title' => 'Counter',
    'value' => '0',
    'icon' => 'fas fa-chart-bar',
    'color' => 'primary' // primary, success, warning, danger, info
])

<div class="card shadow-sm">
    <div class="card-body">
        <div class="d-flex align-items-center">
            <div class="symbol symbol-50px me-5">
                <span class="symbol-label bg-light-{{ $color }}">
                    <i class="{{ $icon }} fs-2x text-{{ $color }}"></i>
                </span>
            </div>
            <div class="d-flex flex-column">
                <h3 class="card-title mb-1">{{ $title }}</h3>
                <span class="fs-2hx fw-bold text-gray-900">{{ $value }}</span>
            </div>
        </div>
    </div>
</div>