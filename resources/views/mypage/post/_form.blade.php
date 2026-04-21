@csrf
<div class="form-group">
    <label for="title">Tieu de</label>
    <input type="text" id="title" name="title" value="{{ old('title', $post->title ?? '') }}" class="form-control @error('title') is-invalid @enderror" required>
    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label for="url">URL slug (chi gom chu, so, dau gach ngang)</label>
    <input type="text" id="url" name="url" value="{{ old('url', $post->url ?? '') }}" class="form-control @error('url') is-invalid @enderror" required>
    @error('url')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label for="thumbnail">Thumbnail (duong dan anh)</label>
    <input type="text" id="thumbnail" name="thumbnail" value="{{ old('thumbnail', $post->thumbnail ?? '') }}" class="form-control @error('thumbnail') is-invalid @enderror">
    @error('thumbnail')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label for="thumbnail_file">Hoac upload thumbnail</label>
    <input type="file" id="thumbnail_file" name="thumbnail_file" class="form-control-file @error('thumbnail_file') is-invalid @enderror" accept="image/*">
    @error('thumbnail_file')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label for="media_images">Media images (nhieu anh)</label>
    <input type="file" id="media_images" name="media_images[]" class="form-control-file @error('media_images.*') is-invalid @enderror" accept="image/*" multiple>
    @error('media_images.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label for="content">Noi dung</label>
    <textarea id="content" name="content" rows="8" class="form-control @error('content') is-invalid @enderror" required>{{ old('content', $post->content ?? '') }}</textarea>
    @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
<a href="{{ route('mypage.post.index') }}" class="btn btn-outline-secondary ml-2">Quay lai</a>
