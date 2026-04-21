<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Media Detail</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
          integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2"
          crossorigin="anonymous">
</head>
<body class="bg-light">
<div class="container py-4">
    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="h4">Media #{{ $media->id }}</h1>
            <p><strong>Path:</strong> {{ $media->path }}</p>
            <p><strong>Type:</strong> {{ $media->type ?: 'N/A' }}</p>
            <p><strong>Size:</strong> {{ $media->size ?: 0 }}</p>
            <p><strong>Mediable:</strong>
                @if($media->mediable_type === \App\Models\Post::class)
                    Post #{{ $media->mediable_id }}
                @elseif($media->mediable_type === \App\Models\Comment::class)
                    Comment #{{ $media->mediable_id }}
                @else
                    N/A
                @endif
            </p>

            <img src="{{ $media->path }}" alt="media" class="img-fluid rounded border" style="max-width:320px;">

            <hr>
            <a href="{{ route('admin.media.edit', $media) }}" class="btn btn-primary">Edit</a>
            <form action="{{ route('admin.media.delete', $media) }}" method="post" class="d-inline" onsubmit="return confirm('Ban chac chan muon xoa media nay?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger ml-2">Delete</button>
            </form>
            <a href="{{ route('admin.media.index') }}" class="btn btn-outline-secondary ml-2">Danh sach media</a>
        </div>
    </div>
</div>
</body>
</html>
