@php
    $left = min($depth * 24, 120);
@endphp

<div class="border rounded p-3 mb-3" style="margin-left: {{ $left }}px;">
    <div class="d-flex justify-content-between">
        <div>
            <strong>{{ $comment->user->name ?? 'N/A' }}</strong>
            <span class="text-muted small ml-2">{{ $comment->created_at }}</span>
        </div>
    </div>

    <div class="mt-2">{!! $comment->content !!}</div>
    @if ($comment->image)
        <div class="mt-2">
            <img src="{{ $comment->image }}" alt="ảnh bình luận" class="img-fluid rounded border" style="max-width: 280px;" onerror="this.onerror=null;this.src='{{ asset('images/image-fallback.png') }}';">
        </div>
    @endif
    @if ($comment->media->count())
        <div class="row mt-2">
            @foreach($comment->media as $media)
                <div class="col-md-3 mb-2">
                    @include('partials.media-preview', ['media' => $media, 'alt' => 'comment media', 'class' => 'img-fluid rounded border'])
                </div>
            @endforeach
        </div>
    @endif

    @if (auth('web')->check() || auth('admin')->check())
        <div class="mt-3">
            @if (auth('admin')->check() || auth('web')->user()?->can('create', \App\Models\Comment::class))
                <details>
                    <summary class="small text-primary">Trả lời bình luận</summary>
                    <form action="{{ route('comment.store.reply', $comment) }}" method="post" class="mt-2" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group mb-2">
                            <textarea name="content" rows="3" class="form-control js-comment-editor"></textarea>
                        </div>
                        <div class="form-group mb-2">
                            <div class="js-media-inputs" data-max-files="5">
                                <input type="file" name="media_images[]" class="form-control-file mb-2" accept="image/*,video/*,audio/*">
                            </div>
                            <small class="text-muted">Tối đa 5 file media.</small>
                        </div>
                        <button type="submit" class="btn btn-sm btn-outline-primary">Gửi trả lời</button>
                    </form>
                </details>
            @endif

            @if (auth('admin')->check() || auth('web')->user()?->can('update', $comment) || auth('web')->user()?->can('delete', $comment))
                <div class="mt-2">
                    @if (auth('admin')->check() || auth('web')->user()?->can('update', $comment))
                        <a href="{{ route('comment.edit', $comment) }}" class="btn btn-sm btn-outline-secondary">Sửa</a>
                    @endif
                    @if (auth('admin')->check() || auth('web')->user()?->can('delete', $comment))
                        <form action="{{ route('comment.destroy', $comment) }}" method="post" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bình luận này?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
                        </form>
                    @endif
                </div>
            @endif
        </div>
    @endif
</div>

@foreach($comment->comments as $child)
    @include('post.partials.comment-item', ['comment' => $child, 'depth' => $depth + 1])
@endforeach

