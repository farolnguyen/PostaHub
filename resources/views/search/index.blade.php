<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Tìm kiếm — {{ config('app.name', 'PostaHub') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
          integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2"
          crossorigin="anonymous">
</head>
<body class="bg-light">
@include('partials.site-header')

<main class="py-4">
    <div class="container">
        <div class="row justify-content-center mb-3">
            <div class="col-lg-10">
                <h1 class="h4 font-weight-bold text-dark mb-3">Tìm kiếm</h1>
                <form action="{{ route('search.index') }}" method="get" class="mb-3" role="search">
                    <div class="input-group">
                        <input type="search" name="q" class="form-control" value="{{ $query }}"
                               placeholder="Nhập câu hoặc từ khóa (tiếng Việt)..." maxlength="500" autofocus>
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">Tìm</button>
                        </div>
                    </div>
                    @if ($showScores)
                        <input type="hidden" name="debug_scores" value="1">
                    @endif
                </form>

                @if ($mode === 'semantic')
                    <p class="small text-muted mb-2">Chế độ: <span class="badge badge-success">Ngữ nghĩa</span></p>
                @elseif ($mode === 'keyword')
                    <p class="small text-muted mb-2">Chế độ: <span class="badge badge-secondary">Từ khóa</span></p>
                @elseif ($mode === 'empty')
                    <p class="text-muted small mb-0">Nhập từ khóa hoặc câu hỏi để bắt đầu.</p>
                @elseif ($mode === 'error')
                    <div class="alert alert-warning">{{ $fallbackReason }}</div>
                @endif

                @if ($fallbackReason && $mode !== 'error')
                    <div class="alert alert-info py-2 small mb-3">{{ $fallbackReason }}</div>
                @endif
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                @forelse ($paginator as $hit)
                    @php
                        /** @var object{post: \App\Models\Post, snippet: string, score: ?float, chunk_index: ?int} $hit */
                        $post = $hit->post;
                    @endphp
                    <div class="card shadow-sm mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h2 class="h5 mb-1">
                                        <a href="{{ route('post.detail', $post->url) }}" class="text-dark text-decoration-none">{{ $post->title }}</a>
                                    </h2>
                                    <div class="small text-muted">
                                        {{ $post->user->name ?? 'N/A' }} · {{ $post->created_at }}
                                        @if ($showScores && $hit->score !== null)
                                            <span class="badge badge-light border ml-1">score {{ number_format($hit->score, 4) }}</span>
                                            @if ($hit->chunk_index !== null)
                                                <span class="badge badge-light border">chunk {{ $hit->chunk_index }}</span>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <p class="text-muted small mb-2">{{ \Illuminate\Support\Str::limit($hit->snippet, 400) }}</p>
                            <a href="{{ route('post.detail', $post->url) }}" class="btn btn-sm btn-outline-primary">Xem bài viết</a>
                        </div>
                    </div>
                @empty
                    @if ($paginator->total() === 0 && $query !== '' && $mode !== 'error' && $mode !== 'empty')
                        <div class="card shadow-sm">
                            <div class="card-body text-center text-muted py-5">
                                Không tìm thấy bài viết phù hợp.
                            </div>
                        </div>
                    @endif
                @endforelse

                @if ($paginator->hasPages())
                    <div class="mt-3">
                        {{ $paginator->links() }}
                    </div>
                @endif
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
