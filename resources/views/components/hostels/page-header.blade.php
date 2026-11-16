@props(['title', 'subtitle' => null, 'icon' => 'fa-building', 'back' => true, 'backUrl' => null, 'backLabel' => 'Hostel Management'])

<div class="d-flex flex-wrap justify-content-between align-items-start gap-4 mb-6">
    <div>
        @if($back)
            <a href="{{ $backUrl ?? route('hostels.index') }}" class="text-muted fs-7 d-inline-block mb-2"><i class="fas fa-arrow-left me-1"></i>{{ $backLabel }}</a>
        @endif
        <h1 class="fs-2 fw-bold text-gray-900 mb-1"><i class="fas {{ $icon }} me-2 text-primary"></i>{{ $title }}</h1>
        @if($subtitle)<p class="text-muted mb-0">{{ $subtitle }}</p>@endif
    </div>
    @if(! $slot->isEmpty())
        <div class="d-flex flex-wrap gap-2">{{ $slot }}</div>
    @endif
</div>
