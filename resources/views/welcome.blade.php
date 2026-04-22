<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ config('app.name', 'PostaHub') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
          integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2"
          crossorigin="anonymous">
</head>
<body class="bg-light">
@include('partials.site-header')

<main class="py-4">
    <div class="container">
        <div class="row justify-content-center mb-4">
            <div class="col-lg-8 text-center">
                <h1 class="h3 font-weight-bold text-dark mb-2">Bảng tin bài viết</h1>
                <p class="text-muted mb-0">Danh sách bài đăng mới nhất từ cộng đồng PostaHub.</p>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                @forelse($posts as $post)
                    <div class="card shadow-sm mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h2 class="h5 mb-1">
                                        <a href="{{ route('post.detail', $post->url) }}" class="text-dark text-decoration-none">{{ $post->title }}</a>
                                    </h2>
                                    <div class="small text-muted">
                                        Tác giả: {{ $post->user->name ?? 'N/A' }} · {{ $post->created_at }}
                                    </div>
                                </div>
                            </div>

                            @if($post->thumbnail)
                                <div class="mb-2">
                                    <img src="{{ $post->thumbnail }}" alt="thumbnail {{ $post->title }}" class="img-fluid rounded border" style="max-height: 360px;" onerror="this.onerror=null;this.src='{{ asset('images/image-fallback.png') }}';">
                                </div>
                            @endif

                            @if($post->media->count())
                                <div class="row mb-2">
                                    @foreach($post->media->take(3) as $media)
                                        <div class="col-md-4 mb-2">
                                            @include('partials.media-preview', ['media' => $media, 'alt' => 'post media', 'class' => 'img-fluid rounded border'])
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="text-muted mb-2">
                                {!! \Illuminate\Support\Str::limit(strip_tags($post->content), 220) !!}
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <div class="small text-muted">
                                    👍 {{ $post->likes_count }} · 💬 {{ $post->comments_count }}
                                </div>
                                <a href="{{ route('post.detail', $post->url) }}" class="btn btn-sm btn-outline-primary">Xem chi tiết</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="card shadow-sm">
                        <div class="card-body text-center text-muted py-5">
                            Chưa có bài đăng nào.
                        </div>
                    </div>
                @endforelse

                <div class="mt-3">
                    {{ $posts->links() }}
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"
        integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj"
        crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx"
        crossorigin="anonymous"></script>
</body>
</html>
