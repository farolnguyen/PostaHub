<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Sua comment</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
          integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2"
          crossorigin="anonymous">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="h4 mb-3">Sua comment</h1>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0 pl-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('comment.update', $comment) }}" method="post" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="content">Noi dung</label>
                    <textarea id="content" name="content" rows="6" class="form-control" required>{{ old('content', $comment->content) }}</textarea>
                </div>

                <div class="form-group">
                    <label for="image">Image URL (tuy chon)</label>
                    <input type="text" id="image" name="image" class="form-control" value="{{ old('image', $comment->image) }}">
                </div>

                <div class="form-group">
                    <label for="image_file">Hoac upload image cho comment</label>
                    <input type="file" id="image_file" name="image_file" class="form-control-file" accept="image/*">
                </div>

                <div class="form-group">
                    <label for="media_images">Media images cho comment (nhieu anh)</label>
                    <input type="file" id="media_images" name="media_images[]" class="form-control-file" accept="image/*" multiple>
                </div>

                <button type="submit" class="btn btn-primary">Luu cap nhat</button>
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary ml-2">Quay lai</a>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
    ClassicEditor.create(document.querySelector('#content')).catch((error) => console.error(error));
</script>
</body>
</html>

