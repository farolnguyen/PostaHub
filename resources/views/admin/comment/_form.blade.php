@csrf
<div class="form-group">
    <label for="user_id">User tao comment</label>
    <select name="user_id" id="user_id" class="form-control" required>
        <option value="">-- Chon user --</option>
        @foreach($users as $user)
            <option value="{{ $user->id }}" @selected(old('user_id', $comment->user_id ?? '') == $user->id)>
                #{{ $user->id }} - {{ $user->name }} ({{ $user->email }})
            </option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label for="target_type">Comment cho doi tuong</label>
    <select name="target_type" id="target_type" class="form-control" required>
        <option value="post" @selected(old('target_type', isset($comment) && $comment->commentable_type === \App\Models\Comment::class ? 'comment' : 'post') === 'post')>Post</option>
        <option value="comment" @selected(old('target_type', isset($comment) && $comment->commentable_type === \App\Models\Comment::class ? 'comment' : 'post') === 'comment')>Comment (reply)</option>
    </select>
</div>

<div class="form-group">
    <label for="target_id">Target ID</label>
    <input type="number" id="target_id" name="target_id" class="form-control" value="{{ old('target_id', $comment->commentable_id ?? '') }}" required>
    <small class="text-muted">Chon Post ID hoac Comment ID tu danh sach ben duoi.</small>
</div>

<div class="form-group">
    <label for="content">Noi dung</label>
    <textarea id="content" name="content" rows="6" class="form-control js-comment-editor" required>{{ old('content', $comment->content ?? '') }}</textarea>
</div>

<div class="form-group">
    <label for="image">Image URL (tuy chon)</label>
    <input type="text" id="image" name="image" class="form-control" value="{{ old('image', $comment->image ?? '') }}">
</div>

<div class="form-group">
    <label for="image_file">Hoac upload image cho comment</label>
    <input type="file" id="image_file" name="image_file" class="form-control-file" accept="image/*">
</div>

<div class="form-group">
    <label for="media_images">Media images (nhieu anh)</label>
    <input type="file" id="media_images" name="media_images[]" class="form-control-file" accept="image/*" multiple>
</div>

<button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
<a href="{{ route('admin.comment.index') }}" class="btn btn-outline-secondary ml-2">Quay lai</a>

@if ($errors->any())
    <div class="alert alert-danger mt-3">
        <ul class="mb-0 pl-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
