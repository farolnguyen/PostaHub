<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Admin — Chi tiết bài viết</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
          integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2"
          crossorigin="anonymous">
</head>
<body class="bg-light">
@include('partials.site-header')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="h4">{{ $post->title }}</h1>
            <p class="text-muted mb-2">Tác giả: #{{ $post->user_id }} - {{ $post->user->name ?? 'N/A' }}</p>
            <p><strong>Slug:</strong> <code>{{ $post->url }}</code></p>
            <p><strong>Thumbnail:</strong></p>
            @if($post->thumbnail)
                <img src="{{ $post->thumbnail }}" alt="thumbnail {{ $post->title }}" class="img-fluid rounded border mb-3" style="max-width: 280px;" onerror="this.onerror=null;this.src='{{ asset('images/image-fallback.png') }}';">
            @else
                <p class="text-muted">N/A</p>
            @endif
            @if($post->media->count())
                <div class="row mb-3">
                    @foreach($post->media as $media)
                        <div class="col-md-3 mb-2">
                            @include('partials.media-preview', ['media' => $media, 'alt' => 'post media', 'class' => 'img-fluid rounded border'])
                        </div>
                    @endforeach
                </div>
            @endif
            <hr>
            <div>{!! $post->content !!}</div>
            <hr>
            <a href="{{ route('post.detail', $post->url) }}" class="btn btn-outline-info">Xem trang công khai</a>
            <a href="{{ route('admin.post.edit', $post) }}" class="btn btn-primary ml-2">Sửa</a>
            <a href="{{ route('admin.post.index') }}" class="btn btn-outline-secondary ml-2">Quay lại</a>
        </div>
    </div>
</div>
</body>
</html>
