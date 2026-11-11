@props(['items' => []])
@foreach($items as $media)
    <figure class="mb-3">
        @if($media['type'] === 'image')
            <img src="{{ $media['url'] }}" alt="{{ $media['filename'] ?? 'Question image' }}"
                 class="img-fluid rounded" style="max-height: 400px; object-fit: contain;" loading="lazy">
        @elseif($media['type'] === 'video')
            <video controls preload="none" playsinline class="w-100 rounded" style="max-height: 400px;"
                   aria-label="{{ $media['filename'] ?? 'Question video' }}">
                <source src="{{ $media['url'] }}" type="{{ $media['mime_type'] }}">
                Your browser cannot play this video.
            </video>
        @elseif($media['type'] === 'audio')
            <audio controls preload="none" class="w-100" aria-label="{{ $media['filename'] ?? 'Question audio' }}">
                <source src="{{ $media['url'] }}" type="{{ $media['mime_type'] }}">
                Your browser cannot play this audio.
            </audio>
        @endif
        <figcaption class="small text-muted">{{ $media['filename'] ?? '' }}</figcaption>
    </figure>
@endforeach
