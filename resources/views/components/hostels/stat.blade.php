@props(['label', 'value', 'icon' => 'fa-circle', 'color' => 'primary'])

<div class="card h-100">
    <div class="card-body d-flex align-items-center gap-4">
        <span class="d-inline-flex align-items-center justify-content-center rounded bg-light-{{ $color }} text-{{ $color }}" style="width:48px;height:48px;"><i class="fas {{ $icon }} fs-3"></i></span>
        <div>
            <div class="fs-2 fw-bold text-gray-900 lh-1">{{ $value }}</div>
            <div class="text-muted fs-7 mt-1">{{ $label }}</div>
        </div>
    </div>
</div>
