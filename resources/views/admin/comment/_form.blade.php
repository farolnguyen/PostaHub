@csrf
<div class="form-group">
    <label for="user_id">User tạo bình luận</label>
    <select name="user_id" id="user_id" class="form-control" required>
        <option value="">— Chọn user —</option>
        @foreach($users as $user)
            <option value="{{ $user->id }}" @selected(old('user_id', $comment->user_id ?? '') == $user->id)>
                #{{ $user->id }} - {{ $user->name }} ({{ $user->email }})
            </option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label for="target_type">Bình luận cho đối tượng</label>
    <select name="target_type" id="target_type" class="form-control" required>
        <option value="post" @selected(old('target_type', isset($comment) && $comment->commentable_type === \App\Models\Comment::class ? 'comment' : 'post') === 'post')>Post</option>
        <option value="comment" @selected(old('target_type', isset($comment) && $comment->commentable_type === \App\Models\Comment::class ? 'comment' : 'post') === 'comment')>Comment (reply)</option>
    </select>
</div>

<div class="form-group">
    <label for="target_id">Target ID</label>
    <input type="number" id="target_id" name="target_id" class="form-control" value="{{ old('target_id', $comment->commentable_id ?? '') }}" required>
    <small class="text-muted">Chọn Post ID hoặc Comment ID từ danh sách bên dưới.</small>
</div>

<div class="form-group">
    <label for="content">Nội dung</label>
    <textarea id="content" name="content" rows="6" class="form-control js-comment-editor">{{ old('content', isset($comment) ? $comment->content : '') }}</textarea>
</div>

<div class="form-group">
    <label>Tải ảnh lên cho bình luận (tối đa 5 ảnh)</label>
    <div class="js-media-inputs" data-max-files="5">
        <input type="file" name="media_images[]" class="form-control-file mb-2" accept="image/*">
    </div>
    @error('media_images')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
</div>

<button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
<a href="{{ route('admin.comment.index') }}" class="btn btn-outline-secondary ml-2">Quay lại</a>

@if ($errors->any())
    <div class="alert alert-danger mt-3">
        <ul class="mb-0 pl-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
