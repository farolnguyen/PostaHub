<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Upload Media</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
          integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2"
          crossorigin="anonymous">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="h4 mb-3">Upload media</h1>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0 pl-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.media.store') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="target_type">Gan vao doi tuong</label>
                    <select name="target_type" id="target_type" class="form-control" required>
                        <option value="post" @selected(old('target_type')==='post')>Post</option>
                        <option value="comment" @selected(old('target_type')==='comment')>Comment</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="target_id">Target ID</label>
                    <input type="number" id="target_id" name="target_id" value="{{ old('target_id') }}" class="form-control" required>
                    <small class="text-muted">Ban co the tham khao danh sach Post/Comment ben duoi.</small>
                </div>

                <div class="form-group">
                    <label for="file">File anh</label>
                    <input type="file" id="file" name="file" class="form-control-file" accept="image/*" required>
                </div>

                <button type="submit" class="btn btn-primary">Upload</button>
                <a href="{{ route('admin.media.index') }}" class="btn btn-outline-secondary ml-2">Quay lai</a>
            </form>

            <hr>
            <h2 class="h6">Post gan day</h2>
            <ul class="small">
                @foreach($posts as $post)
                    <li>#{{ $post->id }} - {{ $post->title }}</li>
                @endforeach
            </ul>

            <h2 class="h6">Comment gan day</h2>
            <ul class="small">
                @foreach($comments as $comment)
                    <li>#{{ $comment->id }} - {{ \Illuminate\Support\Str::limit(strip_tags($comment->content), 60) }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
</body>
</html>
