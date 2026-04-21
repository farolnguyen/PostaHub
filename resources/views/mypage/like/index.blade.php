<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Mypage — Bài đã thích</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
          integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2"
          crossorigin="anonymous">
</head>
<body class="bg-light">
@include('partials.site-header')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Bài viết bạn đã thích</h1>
        <a href="{{ route('user.index') }}" class="btn btn-outline-secondary">Quay lại</a>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Tiêu đề</th>
                        <th>Tác giả</th>
                        <th>Tổng lượt thích</th>
                        <th class="text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($likedPosts as $post)
                        <tr>
                            <td>{{ $post->title }}</td>
                            <td>{{ $post->user->name ?? 'N/A' }}</td>
                            <td>{{ $post->likes_count }}</td>
                            <td class="text-right">
                                <a href="{{ route('post.detail', $post->url) }}" class="btn btn-sm btn-outline-primary">Xem chi tiết</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Bạn chưa thích bài viết nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $likedPosts->links() }}</div>
</div>
</body>
</html>
