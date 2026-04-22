<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Edit Media</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
          integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2"
          crossorigin="anonymous">
</head>
<body class="bg-light">
@include('partials.site-header')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="h4 mb-3">Edit media #{{ $media->id }}</h1>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0 pl-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-3">
                @include('partials.media-preview', ['media' => $media, 'alt' => 'media', 'class' => 'img-fluid rounded border', 'style' => 'max-width:220px;'])
            </div>

            <form action="{{ route('admin.media.update', $media) }}" method="post" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="target_type">Gắn vào đối tượng</label>
                    <select name="target_type" id="target_type" class="form-control" required>
                        <option value="post" @selected(old('target_type', $media->mediable_type === \App\Models\Post::class ? 'post' : 'comment')==='post')>Post</option>
                        <option value="comment" @selected(old('target_type', $media->mediable_type === \App\Models\Comment::class ? 'comment' : 'post')==='comment')>Comment</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="target_id">Target ID</label>
                    <input type="number" id="target_id" name="target_id" value="{{ old('target_id', $media->mediable_id) }}" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="type">Mime type (tùy chọn)</label>
                    <input type="text" id="type" name="type" value="{{ old('type', $media->type) }}" class="form-control">
                </div>

                <div class="form-group">
                    <label for="file">Đổi file media (ảnh/video/âm thanh) (tùy chọn)</label>
                    <input type="file" id="file" name="file" class="form-control-file" accept="image/*,video/*,audio/*">
                </div>

                <button type="submit" class="btn btn-primary">Lưu cập nhật</button>
                <a href="{{ route('admin.media.detail', $media) }}" class="btn btn-outline-secondary ml-2">Quay lại</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
