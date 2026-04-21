<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Mypage — Bài viết của tôi</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
          integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2"
          crossorigin="anonymous">
</head>
<body class="bg-light">
@include('partials.site-header')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Bài viết của tôi</h1>
        <div>
            @can('create', \App\Models\Post::class)
                <a href="{{ route('mypage.post.create') }}" class="btn btn-primary">Tạo bài viết</a>
            @endcan
            <a href="{{ route('user.index') }}" class="btn btn-outline-secondary ml-2">Quay lại</a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Tiêu đề</th>
                        <th>Slug</th>
                        <th>Cập nhật</th>
                        <th class="text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($posts as $post)
                    <tr>
                        <td>{{ $post->title }}</td>
                        <td><code>{{ $post->url }}</code></td>
                        <td>{{ $post->updated_at }}</td>
                        <td class="text-right">
                            <a href="{{ route('post.detail', $post->url) }}" class="btn btn-sm btn-outline-info">Detail</a>
                            <a href="{{ route('mypage.post.edit', $post) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            <form action="{{ route('mypage.post.destroy', $post) }}" method="post" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài này?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">Bạn chưa có bài viết nào.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $posts->links() }}</div>
</div>
</body>
</html>
