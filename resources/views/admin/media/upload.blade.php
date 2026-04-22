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
@include('partials.site-header')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="h4 mb-3">Tải lên media</h1>

            <x-form.error-alert />

            <form action="{{ route('admin.media.store') }}" method="post" enctype="multipart/form-data">
                @csrf
                <x-form.select name="target_type" label="Gắn vào đối tượng" required>
                    <option value="post" @selected(old('target_type')==='post')>Post</option>
                    <option value="comment" @selected(old('target_type')==='comment')>Comment</option>
                </x-form.select>

                <x-form.input
                    name="target_id"
                    label="Target ID"
                    type="number"
                    :value="old('target_id')"
                    hint="Bạn có thể tham khảo danh sách Post/Comment bên dưới."
                    required
                />

                <x-form.file
                    name="file"
                    label="File media (ảnh/video/âm thanh)"
                    accept="image/*,video/*,audio/*"
                    required
                />

                <button type="submit" class="btn btn-primary">Tải lên</button>
                <a href="{{ route('admin.media.index') }}" class="btn btn-outline-secondary ml-2">Quay lại</a>
            </form>

            <hr>
            <h2 class="h6">Bài viết gần đây</h2>
            <ul class="small">
                @foreach($posts as $post)
                    <li>#{{ $post->id }} - {{ $post->title }}</li>
                @endforeach
            </ul>

            <h2 class="h6">Bình luận gần đây</h2>
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
