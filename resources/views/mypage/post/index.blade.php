<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Mypage Post</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
          integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2"
          crossorigin="anonymous">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Bai viet cua toi</h1>
        <div>
            @can('create', \App\Models\Post::class)
                <a href="{{ route('mypage.post.create') }}" class="btn btn-primary">Tao bai viet</a>
            @endcan
            <a href="{{ route('user.index') }}" class="btn btn-outline-secondary ml-2">Mypage</a>
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
                        <th>Tieu de</th>
                        <th>Slug</th>
                        <th>Cap nhat</th>
                        <th class="text-right">Thao tac</th>
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
                            <form action="{{ route('mypage.post.destroy', $post) }}" method="post" class="d-inline" onsubmit="return confirm('Ban chac chan muon xoa bai nay?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">Ban chua co bai viet nao.</td>
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
