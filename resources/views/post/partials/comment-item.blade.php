@php
    $likeCount       = $comment->comment_likes_count ?? $comment->commentLikes()->count();
    $hasLikedComment = isset(($likedCommentIds ?? [])[$comment->id]);
    $likeRoute       = route('like.comment.toggle', $comment);
    $flatReplies     = $comment->flatReplies ?? collect();
    $replyCount      = $flatReplies->count();
@endphp

<div
    id="comment-{{ $comment->id }}"
    class="card mb-3"
    data-comment-id="{{ $comment->id }}"
    data-depth="0"
>
    <div class="card-body">
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
                <strong>{{ $comment->user->name ?? 'N/A' }}</strong>
                <span class="text-muted small ms-2">{{ $comment->created_at && now()->diffInSeconds($comment->created_at) < 10 ? 'vừa xong' : ($comment->created_at?->diffForHumans() ?? '') }}</span>
            </div>
            @if (auth('admin')->check() || auth('web')->user()?->can('update', $comment) || auth('web')->user()?->can('delete', $comment))
                <div class="d-flex gap-1">
                    @if (auth('admin')->check() || auth('web')->user()?->can('update', $comment))
                        <a href="{{ route('comment.edit', $comment) }}" class="btn btn-sm btn-outline-secondary py-0 px-1">Sửa</a>
                    @endif
                    @if (auth('admin')->check() || auth('web')->user()?->can('delete', $comment))
                        <form action="{{ route('comment.destroy', $comment) }}" method="post" class="d-inline" onsubmit="return confirm('Xóa bình luận này?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-1">Xóa</button>
                        </form>
                    @endif
                </div>
            @endif
        </div>

        {{-- Nội dung --}}
        <div class="mb-2">{!! $comment->content !!}</div>

        {{-- Media --}}
        @if ($comment->image)
            <div class="mb-2">
                <img src="{{ $comment->image }}" alt="ảnh bình luận" class="img-fluid rounded border" style="max-width: 280px;"
                     onerror="this.onerror=null;this.src='{{ asset('images/image-fallback.png') }}';">
            </div>
        @endif
        @if ($comment->media->count())
            <div class="row mb-2">
                @foreach($comment->media as $media)
                    <div class="col-md-3 mb-2">
                        @include('partials.media-preview', ['media' => $media, 'alt' => 'comment media', 'class' => 'img-fluid rounded border'])
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Action bar --}}
        <div class="d-flex align-items-center gap-3">
            {{-- Like button --}}
            <button
                type="button"
                class="btn btn-sm js-comment-like-btn {{ $hasLikedComment ? 'btn-primary' : 'btn-outline-secondary' }}"
                data-comment-id="{{ $comment->id }}"
                data-liked="{{ $hasLikedComment ? '1' : '0' }}"
                data-url="{{ $likeRoute }}"
                @guest('web') @guest('admin') disabled title="Đăng nhập để thích" @endguest @endguest
            >👍 <span class="js-like-count">{{ $likeCount }}</span></button>

            {{-- Reply button --}}
            @if (auth('web')->check() || auth('admin')->check())
                <button type="button"
                    class="btn btn-sm btn-link p-0 text-decoration-none js-open-reply-form"
                    data-root="{{ $comment->id }}"
                    data-mention="">
                    💬 Trả lời
                </button>
            @endif
        </div>
    </div>

    {{-- ── Replies section ── --}}
    <div class="js-replies-wrapper" id="replies-wrapper-{{ $comment->id }}">

        {{-- Toggle nút (chỉ hiện khi có ít nhất 1 reply) --}}
        @if($replyCount > 0)
        <div class="px-3 pb-1">
            <button type="button"
                class="btn btn-sm btn-link p-0 text-decoration-none js-toggle-replies"
                data-root="{{ $comment->id }}"
                data-count="{{ $replyCount }}"
                data-open="0">
                Xem {{ $replyCount }} trả lời ▾
            </button>
        </div>
        @endif

        {{-- Replies list (ẩn mặc định nếu có reply, hiện nếu chưa có) --}}
        <div id="replies-list-{{ $comment->id }}"
             class="js-replies-list border-top mx-3 pt-2 pb-1"
             style="{{ $replyCount > 0 ? 'display:none;' : '' }}">

            @foreach($flatReplies as $reply)
                @include('post.partials.comment-reply', [
                    'reply'          => $reply,
                    'topComment'     => $comment,
                    'likedCommentIds'=> $likedCommentIds ?? [],
                ])
            @endforeach
        </div>

        {{-- Reply form (ẩn mặc định) --}}
        @if (auth('web')->check() || auth('admin')->check())
        <div id="reply-form-{{ $comment->id }}"
             class="px-3 pb-3 border-top"
             style="display:none;">
            <form action="{{ route('comment.store.reply', $comment) }}" method="post" enctype="multipart/form-data" class="mt-2">
                @csrf
                <div id="reply-mention-label-{{ $comment->id }}" class="small text-primary mb-1" style="display:none;"></div>
                <textarea name="content" rows="2"
                    class="form-control mb-2"
                    id="reply-textarea-{{ $comment->id }}"
                    placeholder="Viết trả lời…"></textarea>
                <div class="form-group mb-2">
                    <div class="js-media-inputs" data-max-files="3">
                        <input type="file" name="media_images[]" class="form-control-file mb-1" accept="image/*,video/*,audio/*">
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary">Gửi</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary js-cancel-reply" data-root="{{ $comment->id }}">Hủy</button>
                </div>
            </form>
        </div>
        @endif
    </div>
</div>
