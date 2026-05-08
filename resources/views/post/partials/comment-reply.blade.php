@php
    $likeCount       = $reply->comment_likes_count ?? $reply->commentLikes()->count();
    $hasLiked        = isset(($likedCommentIds ?? [])[$reply->id]);
    $likeRoute       = route('like.comment.toggle', $reply);
@endphp

<div
    id="comment-{{ $reply->id }}"
    class="py-2 border-bottom"
    data-comment-id="{{ $reply->id }}"
    data-depth="1"
    data-root-comment-id="{{ $topComment->id }}"
>
    <div class="d-flex justify-content-between align-items-start">
        <div class="flex-grow-1">
            <div class="mb-1">
                <strong>{{ $reply->user->name ?? 'N/A' }}</strong>
                @if($reply->mentionUser ?? null)
                    <span class="text-primary small ms-1">@{{ $reply->mentionUser->name }}</span>
                @endif
                <span class="text-muted small ms-2">{{ $reply->created_at && now()->diffInSeconds($reply->created_at) < 10 ? 'vừa xong' : ($reply->created_at?->diffForHumans() ?? '') }}</span>
            </div>

            <div class="mb-1">{!! $reply->content !!}</div>

            {{-- Media --}}
            @if ($reply->image)
                <div class="mb-1">
                    <img src="{{ $reply->image }}" alt="ảnh reply" class="img-fluid rounded border" style="max-width: 220px;"
                         onerror="this.onerror=null;this.src='{{ asset('images/image-fallback.png') }}';">
                </div>
            @endif
            @if ($reply->relationLoaded('media') && $reply->media->count())
                <div class="row mb-1">
                    @foreach($reply->media as $media)
                        <div class="col-md-3 mb-1">
                            @include('partials.media-preview', ['media' => $media, 'alt' => 'reply media', 'class' => 'img-fluid rounded border'])
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Action bar --}}
            <div class="d-flex align-items-center gap-3 mt-1">
                <button
                    type="button"
                    class="btn btn-sm js-comment-like-btn {{ $hasLiked ? 'btn-primary' : 'btn-outline-secondary' }}"
                    data-comment-id="{{ $reply->id }}"
                    data-liked="{{ $hasLiked ? '1' : '0' }}"
                    data-url="{{ $likeRoute }}"
                    @guest('web') @guest('admin') disabled title="Đăng nhập để thích" @endguest @endguest
                >👍 <span class="js-like-count">{{ $likeCount }}</span></button>

                @if (auth('web')->check() || auth('admin')->check())
                    <button type="button"
                        class="btn btn-sm btn-link p-0 text-decoration-none js-open-reply-form"
                        data-root="{{ $topComment->id }}"
                        data-mention="{{ $reply->user->name ?? '' }}">
                         💬 Trả lời
                    </button>
                @endif
            </div>
        </div>

        {{-- Edit/Delete --}}
        @if (auth('admin')->check() || auth('web')->user()?->can('update', $reply) || auth('web')->user()?->can('delete', $reply))
            <div class="d-flex gap-1 ms-2 flex-shrink-0">
                @if (auth('admin')->check() || auth('web')->user()?->can('update', $reply))
                    <a href="{{ route('comment.edit', $reply) }}" class="btn btn-sm btn-outline-secondary py-0 px-1">Sửa</a>
                @endif
                @if (auth('admin')->check() || auth('web')->user()?->can('delete', $reply))
                    <form action="{{ route('comment.destroy', $reply) }}" method="post" class="d-inline" onsubmit="return confirm('Xóa reply này?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-1">Xóa</button>
                    </form>
                @endif
            </div>
        @endif
    </div>
</div>
