<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Admin — Tạo bài viết</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
          integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2"
          crossorigin="anonymous">
</head>
<body class="bg-light">
@include('partials.site-header')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="h4 mb-3">Admin tạo bài viết mới</h1>
            <form action="{{ route('admin.post.store') }}" method="post" enctype="multipart/form-data">
                @include('admin.post._form', ['submitLabel' => 'Tạo bài viết'])
            </form>
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
