<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Admin Post</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
          integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2"
          crossorigin="anonymous">
</head>
<body class="bg-light">
@include('partials.site-header')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Quản lý bài viết (Admin)</h1>
        <div>
            <a href="{{ route('admin.post.create') }}" class="btn btn-primary">Tạo bài viết</a>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary ml-2">Quay lại</a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if ($errors->has('semantic'))
        <div class="alert alert-danger">{{ $errors->first('semantic') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th>Tiêu đề</th>
                        <th>Tác giả</th>
                        <th>Slug</th>
                        <th>Semantic</th>
                        <th class="text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($posts as $post)
                    <tr>
                        <td>{{ $post->id }}</td>
                        <td>{{ $post->title }}</td>
                        <td>#{{ $post->user_id }} - {{ $post->user->name ?? 'N/A' }}</td>
                        <td><code>{{ $post->url }}</code></td>
                        <td>
                            @if($post->semanticIndexTrackingApplicable())
                                @php $b = $post->semanticIndexBadge(); @endphp
                                @if($b)
                                    <span class="badge {{ $b['class'] }}">{{ $b['label'] }}</span>
                                @endif
                                <form action="{{ route('admin.post.semantic-reindex', $post) }}" method="post" class="mt-1">
                                    @csrf
                                    <input type="hidden" name="redirect_to" value="index">
                                    <button type="submit" class="btn btn-sm btn-link p-0 small">Reindex</button>
                                </form>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.post.detail', $post) }}" class="btn btn-sm btn-outline-info">Detail</a>
                            <a href="{{ route('admin.post.edit', $post) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            <form action="{{ route('admin.post.destroy', $post) }}" method="post" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài này?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Chưa có bài viết nào.</td>
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
