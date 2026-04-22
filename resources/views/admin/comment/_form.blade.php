@csrf
<x-form.select name="user_id" label="User tạo bình luận" required>
        <option value="">— Chọn user —</option>
        @foreach($users as $user)
            <option value="{{ $user->id }}" @selected(old('user_id', $comment->user_id ?? '') == $user->id)>
                #{{ $user->id }} - {{ $user->name }} ({{ $user->email }})
            </option>
        @endforeach
</x-form.select>

<x-form.select name="target_type" label="Bình luận cho đối tượng" required>
        <option value="post" @selected(old('target_type', isset($comment) && $comment->commentable_type === \App\Models\Comment::class ? 'comment' : 'post') === 'post')>Post</option>
        <option value="comment" @selected(old('target_type', isset($comment) && $comment->commentable_type === \App\Models\Comment::class ? 'comment' : 'post') === 'comment')>Comment (reply)</option>
</x-form.select>

<x-form.input
    name="target_id"
    label="Target ID"
    type="number"
    :value="$comment->commentable_id ?? ''"
    hint="Chọn Post ID hoặc Comment ID từ danh sách bên dưới."
    required
/>

<x-form.textarea
    name="content"
    label="Nội dung"
    :value="isset($comment) ? $comment->content : ''"
    rows="6"
    class="js-comment-editor"
/>

<div class="form-group">
    <label>Tải media cho bình luận (tối đa 5 file ảnh/video/âm thanh)</label>
    <div class="js-media-inputs" data-max-files="5">
        <input type="file" name="media_images[]" class="form-control-file mb-2" accept="image/*,video/*,audio/*">
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
