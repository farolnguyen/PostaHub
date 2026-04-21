<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Admin Edit Comment</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
          integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2"
          crossorigin="anonymous">
</head>
<body class="bg-light">
@include('partials.site-header')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="h4 mb-3">Admin edit comment #{{ $comment->id }}</h1>
            <form action="{{ route('admin.comment.update', $comment) }}" method="post" enctype="multipart/form-data">
                @method('PUT')
                @include('admin.comment._form', ['submitLabel' => 'Lưu cập nhật'])
            </form>

            @if($comment->media->count())
                <hr>
                <h2 class="h6">Media hiện tại</h2>
                <div class="row">
                    @foreach($comment->media as $media)
                        <div class="col-md-3 mb-2">
                            <img src="{{ $media->path }}" alt="comment media" class="img-fluid rounded border" onerror="this.onerror=null;this.src='{{ asset('images/image-fallback.png') }}';">
                        </div>
                    @endforeach
                </div>
            @endif
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
                input.accept = 'image/*';
                return input;
            }

            function refresh() {
                var items = Array.from(container.querySelectorAll('.js-media-item'));
                var inputs = items
                    .map(function (item) { return item.querySelector('input[type="file"]'); })
                    .filter(Boolean);
                var hasEmpty = inputs.some(function (input) { return !input.value; });

                if (!hasEmpty && inputs.length < maxFiles) {
                    buildPreviewItem(createInput());
                    items = Array.from(container.querySelectorAll('.js-media-item'));
                }

                items.forEach(function (item) {
                    var btn = item.querySelector('button');
                    var input = item.querySelector('input[type="file"]');
                    if (!btn) return;
                    var hasValue = !!(input && input.value);
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
