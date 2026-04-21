<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Mypage — Hồ sơ</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
          integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2"
          crossorigin="anonymous">
</head>
<body class="bg-light">
@include('partials.site-header')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Hồ sơ của tôi</h1>
        <a href="{{ route('user.index') }}" class="btn btn-outline-secondary">Quay lại</a>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h5">Danh sách bình luận của bạn</h2>
                    <hr>
                    @forelse($myComments as $comment)
                        <div class="mb-3 pb-3 border-bottom">
                            <div class="small text-muted">{{ $comment->created_at }}</div>
                            <div>{!! $comment->content !!}</div>
                            @if($comment->image)
                                <div class="mt-2">
                                    <img src="{{ $comment->image }}" alt="ảnh bình luận" class="img-fluid rounded border" style="max-width: 220px;" onerror="this.onerror=null;this.src='{{ asset('images/image-fallback.png') }}';">
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted mb-0">Bạn chưa có bình luận nào.</p>
                    @endforelse
                    <div>{{ $myComments->links() }}</div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h5">Ai đã thích bài viết của bạn</h2>
                    <hr>
                    @forelse($likedByOthers as $row)
                        <div class="mb-3 pb-3 border-bottom">
                            <div><strong>{{ $row->name }}</strong> ({{ $row->email }})</div>
                            <div class="small text-muted">Đã thích bài #{{ $row->post_id }}</div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Chưa có ai thích bài viết của bạn.</p>
                    @endforelse
                    <div>{{ $likedByOthers->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
