<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Admin Comment</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
          integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2"
          crossorigin="anonymous">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Quan ly comment</h1>
        <div>
            <a href="{{ route('admin.comment.create') }}" class="btn btn-primary">Tao comment</a>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary ml-2">Dashboard</a>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead class="thead-light">
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Noi dung</th>
                    <th>Comment cho</th>
                    <th class="text-right">Thao tac</th>
                </tr>
                </thead>
                <tbody>
                @forelse($comments as $comment)
                    <tr>
                        <td>{{ $comment->id }}</td>
                        <td>{{ $comment->user->name ?? 'N/A' }}</td>
                        <td>{!! \Illuminate\Support\Str::limit(strip_tags($comment->content), 80) !!}</td>
                        <td>
                            @if($comment->commentable_type === \App\Models\Post::class)
                                Post #{{ $comment->commentable_id }}
                            @elseif($comment->commentable_type === \App\Models\Comment::class)
                                Comment #{{ $comment->commentable_id }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.comment.edit', $comment) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            <form action="{{ route('admin.comment.delete', $comment) }}" method="post" class="d-inline" onsubmit="return confirm('Ban chac chan muon xoa comment nay?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Chua co comment.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $comments->links() }}</div>
</div>
</body>
</html>
