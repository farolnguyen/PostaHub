<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Thông báo — PostaHub</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
          integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2"
          crossorigin="anonymous">
</head>
<body class="bg-light">
@include('partials.site-header')
<div class="container py-4" style="max-width: 760px;">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">🔔 Thông báo</h1>
        <a href="{{ route('user.index') }}" class="btn btn-sm btn-outline-secondary">Quay lại</a>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    {{-- Nút đánh dấu tất cả đã đọc --}}
    @if ($notifications->where('read_at', null)->count())
        <form action="{{ route('mypage.notifications.read-all') }}" method="post" class="mb-3">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-primary">
                ✓ Đánh dấu tất cả đã đọc
            </button>
        </form>
    @endif

    @forelse ($notifications as $notification)
        @php
            $data    = $notification->data ?? [];
            $type    = $data['type'] ?? '';
            $isRead  = ! is_null($notification->read_at);
            $link    = route('mypage.notifications.read', $notification->id);

            $icon = match($type) {
                'new_comment_on_post'   => '💬',
                'new_reply_to_comment'  => '↩️',
                'post_liked'            => '👍',
                'comment_liked'         => '👍',
                default                 => '🔔',
            };

            $text = match($type) {
                'new_comment_on_post'  => '<strong>' . e($data['actor_name'] ?? '') . '</strong> đã bình luận bài viết của bạn: <em>' . e($data['post_title'] ?? '') . '</em>',
                'new_reply_to_comment' => '<strong>' . e($data['actor_name'] ?? '') . '</strong> đã trả lời bình luận của bạn trong: <em>' . e($data['post_title'] ?? '') . '</em>',
                'post_liked'           => '<strong>' . e($data['actor_name'] ?? '') . '</strong> đã thích bài viết của bạn: <em>' . e($data['post_title'] ?? '') . '</em>',
                'comment_liked'        => '<strong>' . e($data['actor_name'] ?? '') . '</strong> đã thích bình luận của bạn trong: <em>' . e($data['post_title'] ?? '') . '</em>',
                default                => 'Bạn có một thông báo mới.',
            };
        @endphp

        <div class="card mb-2 {{ $isRead ? '' : 'border-primary' }}" style="{{ $isRead ? '' : 'border-left: 4px solid #007bff !important;' }}">
            <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-start">
                    <span class="mr-3" style="font-size: 1.3rem; line-height: 1.5;">{{ $icon }}</span>
                    <div>
                        <p class="mb-0 small">{!! $text !!}</p>
                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                    </div>
                </div>
                <a href="{{ $link }}" class="btn btn-sm ml-3 flex-shrink-0 {{ $isRead ? 'btn-outline-secondary' : 'btn-primary' }}">
                    {{ $isRead ? 'Xem' : 'Xem →' }}
                </a>
            </div>
        </div>
    @empty
        <div class="text-center text-muted py-5">
            <p style="font-size: 2rem;">🔔</p>
            <p>Bạn chưa có thông báo nào.</p>
        </div>
    @endforelse

    <div class="mt-3">{{ $notifications->links() }}</div>
</div>
</body>
</html>
