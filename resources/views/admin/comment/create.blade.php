<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Admin Tao Comment</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
          integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2"
          crossorigin="anonymous">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="h4 mb-3">Admin tao comment</h1>
            <form action="{{ route('admin.comment.store') }}" method="post" enctype="multipart/form-data">
                @include('admin.comment._form', ['submitLabel' => 'Tao comment'])
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
                @foreach($comments as $c)
                    <li>#{{ $c->id }} - {{ \Illuminate\Support\Str::limit(strip_tags($c->content), 60) }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
    document.querySelectorAll('.js-comment-editor').forEach((element) => {
        ClassicEditor.create(element).catch((error) => console.error(error));
    });
</script>
</body>
</html>
