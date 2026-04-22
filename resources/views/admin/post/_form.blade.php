@csrf
<div class="form-group">
    <label for="user_id">Tác giả (User)</label>
    <select id="user_id" name="user_id" class="form-control @error('user_id') is-invalid @enderror" required>
        <option value="">— Chọn user —</option>
        @foreach($users as $user)
            <option value="{{ $user->id }}" @selected(old('user_id', $post->user_id ?? '') == $user->id)>
                #{{ $user->id }} - {{ $user->name }} ({{ $user->email }})
            </option>
        @endforeach
    </select>
    @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<x-form.input
    name="title"
    label="Tiêu đề"
    :value="$post->title ?? ''"
    required
/>

@php
    $slugPreviewSource = old('title', isset($post) ? $post->title : '');
    $slugPreviewValue = $slugPreviewSource !== '' ? (\Illuminate\Support\Str::slug($slugPreviewSource, '-', 'vi') ?: 'bai-viet') : '';
@endphp
<div class="form-group">
    <label for="post-url-slug-preview">URL slug (xem trước — tự động khi lưu)</label>
    <input type="text" id="post-url-slug-preview" class="form-control bg-light" readonly tabindex="-1" autocomplete="off" value="{{ $slugPreviewValue }}">
    
</div>

<x-form.input
    name="thumbnail"
    label="Thumbnail"
    :value="$post->thumbnail ?? ''"
/>


<div class="form-group">
    <label>Tải media đính kèm cho bài viết (tối đa 5 file ảnh/video/âm thanh)</label>
    <div class="js-media-inputs" data-max-files="5">
        <input type="file" name="media_images[]" class="form-control-file @error('media_images.*') is-invalid @enderror mb-2" accept="image/*,video/*,audio/*">
    </div>
    @error('media_images.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    @error('media_images')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label for="content">Nội dung</label>
    <textarea id="content" name="content" rows="8" class="form-control @error('content') is-invalid @enderror">{{ old('content', isset($post) ? $post->content : '') }}</textarea>
    @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
<a href="{{ route('admin.post.index') }}" class="btn btn-outline-secondary ml-2">Quay lại</a>

<script>
(function () {
    var titleEl = document.getElementById('title');
    var slugEl = document.getElementById('post-url-slug-preview');
    if (!titleEl || !slugEl) {
        return;
    }
    var previewUrl = @json(route('post.slug-preview'));
    var timer;
    function refreshSlugPreview() {
        var qs = new URLSearchParams({ title: titleEl.value }).toString();
        fetch(previewUrl + '?' + qs, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                slugEl.value = data.slug != null ? data.slug : '';
            })
            .catch(function () {});
    }
    titleEl.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(refreshSlugPreview, 120);
    });
    refreshSlugPreview();
})();

(function () {
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
})();
</script>
