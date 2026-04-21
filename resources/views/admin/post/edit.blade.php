<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Admin — Cập nhật bài viết</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
          integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2"
          crossorigin="anonymous">
</head>
<body class="bg-light">
@include('partials.site-header')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="h4 mb-3">Admin cập nhật bài viết</h1>
            <form action="{{ route('admin.post.update', $post) }}" method="post" enctype="multipart/form-data">
                @method('PUT')
                @include('admin.post._form', ['submitLabel' => 'Lưu cập nhật'])
            </form>

            @if($post->media->count())
                <hr>
                <h2 class="h6">Media hiện tại</h2>
                <div class="row">
                    @foreach($post->media as $media)
                        <div class="col-md-3 mb-3">
                            <img src="{{ $media->path }}" alt="media" class="img-fluid rounded border" onerror="this.onerror=null;this.src='{{ asset('images/image-fallback.png') }}';">
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
    ClassicEditor
        .create(document.querySelector('#content'))
        .then(function (editor) {
            var form = document.querySelector('#content').closest('form');
            if (form) {
                form.addEventListener('submit', function () {
                    editor.updateSourceElement();
                });
            }
        })
        .catch(function (error) {
            console.error(error);
        });
</script>
</body>
</html>
