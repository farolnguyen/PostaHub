<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Cap nhat bai viet</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
          integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2"
          crossorigin="anonymous">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="h4 mb-3">Cap nhat bai viet</h1>
            <form action="{{ route('mypage.post.update', $post) }}" method="post" enctype="multipart/form-data">
                @method('PUT')
                @include('mypage.post._form', ['submitLabel' => 'Luu cap nhat'])
            </form>

            @if($post->media->count())
                <hr>
                <h2 class="h6">Media hien tai</h2>
                <div class="row">
                    @foreach($post->media as $media)
                        <div class="col-md-3 mb-3">
                            <img src="{{ $media->path }}" alt="media" class="img-fluid rounded border">
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
        .catch(error => {
            console.error(error);
        });
</script>
</body>
</html>
