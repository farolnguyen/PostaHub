<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Admin Media</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
          integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2"
          crossorigin="anonymous">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Quan ly media</h1>
        <div>
            <a href="{{ route('admin.media.upload') }}" class="btn btn-primary">Upload media</a>
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
                        <th>Preview</th>
                        <th>Type</th>
                        <th>Mediable</th>
                        <th>Size</th>
                        <th class="text-right">Thao tac</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($medias as $media)
                    <tr>
                        <td>{{ $media->id }}</td>
                        <td>
                            <img src="{{ $media->path }}" alt="media" style="width:64px;height:64px;object-fit:cover;" class="rounded border">
                        </td>
                        <td>{{ $media->type ?: 'N/A' }}</td>
                        <td>
                            @if($media->mediable_type === \App\Models\Post::class)
                                Post #{{ $media->mediable_id }}
                            @elseif($media->mediable_type === \App\Models\Comment::class)
                                Comment #{{ $media->mediable_id }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td>{{ $media->size ?: 0 }}</td>
                        <td class="text-right">
                            <a href="{{ route('admin.media.detail', $media) }}" class="btn btn-sm btn-outline-info">Detail</a>
                            <a href="{{ route('admin.media.edit', $media) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            <form action="{{ route('admin.media.delete', $media) }}" method="post" class="d-inline" onsubmit="return confirm('Ban chac chan muon xoa media nay?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Chua co media.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $medias->links() }}</div>
</div>
</body>
</html>
