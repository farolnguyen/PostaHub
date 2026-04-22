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
@include('partials.site-header')
<div class="container py-4">
    <a href="{{ route('home') }}" class="btn btn-sm btn-outline-secondary mb-3">Về trang chủ</a>
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="h3">{{ $post->title }}</h1>
            <p class="text-muted mb-2">Tác giả: {{ $post->user->name ?? 'N/A' }}</p>
            <p><strong>URL:</strong> <code>{{ $post->url }}</code></p>
            <p><strong>Tổng lượt thích:</strong> {{ $post->likes_count }}</p>
            @if($post->thumbnail)
                <div class="mb-3">
                    <p class="mb-2"><strong>Ảnh đại diện (thumbnail):</strong></p>
                    <img src="{{ $post->thumbnail }}" alt="thumbnail {{ $post->title }}" class="img-fluid rounded border" style="max-width: 280px;" onerror="this.onerror=null;this.src='{{ asset('images/image-fallback.png') }}';">
                </div>
            @endif
            @if($post->media->count())
                <div class="row mb-3">
                    @foreach($post->media as $media)
                        <div class="col-md-3 mb-2">
                            @include('partials.media-preview', ['media' => $media, 'alt' => 'post media', 'class' => 'img-fluid rounded border'])
                        </div>
                    @endforeach
                </div>
            @endif
            <hr>
            <div>{!! $post->content !!}</div>
            @if (auth('web')->check() || auth('admin')->check())
                <form action="{{ route('like.toggle', $post) }}" method="post" class="mt-3">
                    @csrf
                    <button type="submit" class="btn {{ $hasLiked ? 'btn-outline-danger' : 'btn-outline-primary' }}">
                        {{ $hasLiked ? 'Bỏ thích' : 'Thích bài viết' }}
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="card shadow-sm mt-4">
        <div class="card-body">
            <h2 class="h5 mb-3">Thảo luận</h2>

            @if (auth('web')->check() || auth('admin')->check())
                @if (auth('admin')->check() || auth('web')->user()?->can('create', \App\Models\Comment::class))
                    <form action="{{ route('comment.store.post', $post) }}" method="post" class="mb-4" enctype="multipart/form-data">
                        @csrf
                        <x-form.textarea
                            id="new-comment-content"
                            name="content"
                            label="Nội dung bình luận"
                            rows="4"
                            class="js-comment-editor"
                        />
                        <div class="form-group">
                            <label>Tải media (ảnh/video/âm thanh) cho bình luận (tối đa 5 file)</label>
                            <div class="js-media-inputs" data-max-files="5">
                                <input type="file" name="media_images[]" class="form-control-file @error('media_images.*') is-invalid @enderror mb-2" accept="image/*,video/*,audio/*">
                            </div>
                            @error('media_images.*')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            @error('media_images')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary">Gửi bình luận</button>
                    </form>
                @elseif(auth('web')->check())
                    <p class="text-muted mb-4">Tài khoản của bạn không được phép bình luận (can_comment = false).</p>
                @endif
            @else
                <p class="text-muted">Bạn cần <a href="{{ route('user.login.form') }}">đăng nhập</a> để bình luận.</p>
            @endif

            @forelse($post->comments as $comment)
                @include('post.partials.comment-item', ['comment' => $comment, 'depth' => 0])
            @empty
                <p class="text-muted mb-0">Chưa có bình luận nào.</p>
            @endforelse
        </div>
    </div>
</div>
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
    function initDynamicMediaInputs(root) {
        root.querySelectorAll('.js-media-inputs').forEach(function (container) {
            var maxFiles = parseInt(container.dataset.maxFiles || '5', 10);
            var baseClass = 'form-control-file';

            function buildPreviewItem(input) {
                if (input.dataset.enhanced === '1') {
                    return;
                }

                var item = document.createElement('div');
                item.className = 'd-flex align-items-start mb-2 js-media-item';

                var left = document.createElement('div');
                left.className = 'mr-2';
                input.classList.remove('mb-2');
                input.classList.add(baseClass);
                left.appendChild(input);

                var preview = document.createElement('img');
                preview.alt = 'preview';
                preview.style.maxWidth = '64px';
                preview.style.maxHeight = '64px';
                preview.className = 'rounded border d-none';
                left.appendChild(preview);

                var removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'btn btn-sm btn-outline-danger';
                removeBtn.textContent = 'Xóa';
                removeBtn.addEventListener('click', function () {
                    item.remove();
                    refresh();
                });

                input.addEventListener('change', function () {
                    if (input.files && input.files[0]) {
                        preview.src = URL.createObjectURL(input.files[0]);
                        preview.classList.remove('d-none');
                    } else {
                        preview.removeAttribute('src');
                        preview.classList.add('d-none');
                    }
                    refresh();
                });

                item.appendChild(left);
                item.appendChild(removeBtn);
                container.appendChild(item);
                input.dataset.enhanced = '1';
            }

            function createInput() {
                var input = document.createElement('input');
                input.type = 'file';
                input.name = 'media_images[]';
                input.accept = 'image/*,video/*,audio/*';
                return input;
            }

            function refresh() {
                var items = Array.from(container.querySelectorAll('.js-media-item'));
                var inputs = items
                    .map(function (item) { return item.querySelector('input[type="file"]'); })
                    .filter(Boolean);
                var hasEmpty = inputs.some(function (input) { return !(input.files && input.files.length); });

                if (!hasEmpty && inputs.length < maxFiles) {
                    buildPreviewItem(createInput());
                    items = Array.from(container.querySelectorAll('.js-media-item'));
                    inputs = items
                        .map(function (item) { return item.querySelector('input[type="file"]'); })
                        .filter(Boolean);
                }

                items.forEach(function (item) {
                    var btn = item.querySelector('button');
                    var input = item.querySelector('input[type="file"]');
                    if (!btn) return;
                    var hasValue = !!(input && input.files && input.files.length);
                    btn.disabled = !hasValue;
                    btn.classList.toggle('invisible', !hasValue);
                });
            }

            var existingInputs = Array.from(container.querySelectorAll('input[type="file"]'));
            container.innerHTML = '';
            if (existingInputs.length === 0) {
                existingInputs = [createInput()];
            }
            existingInputs.forEach(buildPreviewItem);
            refresh();
        });
    }

    initDynamicMediaInputs(document);

    document.querySelectorAll('.js-comment-editor').forEach(function (element) {
        ClassicEditor.create(element)
            .then(function (editor) {
                var form = element.closest('form');
                if (form) {
                    form.addEventListener('submit', function () {
                        editor.updateSourceElement();
                    });
                }
            })
            .catch(function (error) {
                console.error(error);
            });
    });
</script>
</body>
</html>
