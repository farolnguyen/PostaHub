@php
    $mime = strtolower((string) ($media->type ?? ''));
    $isImage = str_starts_with($mime, 'image/');
    $isVideo = str_starts_with($mime, 'video/');
    $isAudio = str_starts_with($mime, 'audio/');
@endphp

@if ($isImage)
    <img src="{{ $media->path }}" alt="{{ $alt ?? 'media' }}" class="{{ $class ?? 'img-fluid rounded border' }}" @if(!empty($style)) style="{{ $style }}" @endif onerror="this.onerror=null;this.src='{{ asset('images/image-fallback.png') }}';">
@elseif ($isVideo)
    <video controls class="{{ $class ?? 'img-fluid rounded border' }}" @if(!empty($style)) style="{{ $style }}" @endif>
        <source src="{{ $media->path }}" type="{{ $media->type }}">
        Trình duyệt không hỗ trợ video.
    </video>
@elseif ($isAudio)
    <audio controls class="{{ $class ?? 'w-100' }}">
        <source src="{{ $media->path }}" type="{{ $media->type }}">
        Trình duyệt không hỗ trợ âm thanh.
    </audio>
@else
    <a href="{{ $media->path }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">Mở file media</a>
@endif

