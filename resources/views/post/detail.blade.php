<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ $post->title }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
          integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2"
          crossorigin="anonymous">
</head>
<body class="bg-light">
<div class="container py-4">
    <a href="{{ route('home') }}" class="btn btn-sm btn-outline-secondary mb-3">Ve trang chu</a>
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="h3">{{ $post->title }}</h1>
            <p class="text-muted mb-2">Tac gia: {{ $post->user->name ?? 'N/A' }}</p>
            <p><strong>URL:</strong> <code>{{ $post->url }}</code></p>
            <p><strong>Tong like:</strong> {{ $post->likes_count }}</p>
            @if($post->thumbnail)
                <p><strong>Thumbnail:</strong> {{ $post->thumbnail }}</p>
            @endif
            @if($post->media->count())
                <div class="row mb-3">
                    @foreach($post->media as $media)
                        <div class="col-md-3 mb-2">
                            <img src="{{ $media->path }}" alt="post media" class="img-fluid rounded border">
                        </div>
                    @endforeach
                </div>
            @endif
            <hr>
            <div>{!! $post->content !!}</div>
            @auth('web')
                <form action="{{ route('like.toggle', $post) }}" method="post" class="mt-3">
                    @csrf
                    <button type="submit" class="btn {{ $hasLiked ? 'btn-outline-danger' : 'btn-outline-primary' }}">
                        {{ $hasLiked ? 'Bo like' : 'Like bai viet' }}
                    </button>
                </form>
            @endauth
        </div>
    </div>

    <div class="card shadow-sm mt-4">
        <div class="card-body">
            <h2 class="h5 mb-3">Thao luan</h2>

            @auth('web')
                @can('create', \App\Models\Comment::class)
                    <form action="{{ route('comment.store.post', $post) }}" method="post" class="mb-4" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="new-comment-content">Noi dung comment</label>
                            <textarea id="new-comment-content" name="content" rows="4" class="form-control js-comment-editor @error('content') is-invalid @enderror" required>{{ old('content') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="new-comment-image">Image URL (tuy chon)</label>
                            <input type="text" id="new-comment-image" name="image" class="form-control @error('image') is-invalid @enderror" value="{{ old('image') }}">
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="new-comment-image-file">Hoac upload image cho comment</label>
                            <input type="file" id="new-comment-image-file" name="image_file" class="form-control-file @error('image_file') is-invalid @enderror" accept="image/*">
                            @error('image_file')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="new-comment-media-images">Media images cho comment (nhieu anh)</label>
                            <input type="file" id="new-comment-media-images" name="media_images[]" class="form-control-file @error('media_images.*') is-invalid @enderror" accept="image/*" multiple>
                            @error('media_images.*')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary">Gui comment</button>
                    </form>
                @else
                    <p class="text-muted mb-4">Tai khoan cua ban khong duoc phep comment (can_comment = false).</p>
                @endcan
            @else
                <p class="text-muted">Ban can <a href="{{ route('user.login.form') }}">dang nhap</a> de comment.</p>
            @endauth

            @forelse($post->comments as $comment)
                @include('post.partials.comment-item', ['comment' => $comment, 'depth' => 0])
            @empty
                <p class="text-muted mb-0">Chua co comment nao.</p>
            @endforelse
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
