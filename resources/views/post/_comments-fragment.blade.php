<div id="js-comments-root" data-post-id="{{ $post->id }}">
    @forelse($post->comments as $comment)
        @include('post.partials.comment-item', ['comment' => $comment, 'depth' => 0])
    @empty
        <p class="text-muted mb-0 js-no-comments">Chưa có bình luận nào.</p>
    @endforelse
</div>

