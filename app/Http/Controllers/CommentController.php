<?php

namespace App\Http\Controllers;

use App\Events\CommentChanged;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Models\Comment;
use App\Models\Post;
use App\Notifications\NewCommentOnPost;
use App\Notifications\NewReplyToComment;
use App\Support\ActorUserResolver;
use App\Support\HtmlSanitizer;
use App\Support\NotificationHelper;
use App\Support\SiteCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CommentController extends Controller
{
    public function storeForPost(StoreCommentRequest $request, Post $post): RedirectResponse
    {
        $this->authorize('create', Comment::class);
        $actor = ActorUserResolver::current();
        abort_if($actor === null, 403);

        $comment = $post->comments()->create([
            'user_id' => $actor->id,
            'content' => HtmlSanitizer::clean($this->embedImageUrls($request->validated('content') ?? '')),
            'image' => null,
        ]);
        $this->storeCommentMedia($comment, $request->file('media_images', []));
        SiteCache::bumpAll();
        broadcast(new CommentChanged(
            CommentChanged::payloadFromComment($comment, 'created', (int) $post->id, null)
        ));

        // Notify tác giả bài viết (trừ khi tự comment bài của mình)
        $postOwner = $post->user;
        if ($postOwner && $postOwner->id !== $actor->id) {
            try {
                $postOwner->notify(NewCommentOnPost::fromModels($comment, $post));
                NotificationHelper::prune($postOwner);
            } catch (\Throwable $e) {
                \Log::warning('NewCommentOnPost notification failed: ' . $e->getMessage());
            }
        }

        return back()->with('status', 'Đã thêm bình luận cho bài viết.');
    }

    public function storeReply(StoreCommentRequest $request, Comment $comment): RedirectResponse
    {
        $this->authorize('create', Comment::class);
        $actor = ActorUserResolver::current();
        abort_if($actor === null, 403);

        // Facebook-style: mọi reply đều lưu thẳng vào root (top-level) comment,
        // tránh nesting sâu, giữ thread phẳng.
        $mentionedUser = $comment->user; // user được reply đến (để hiển thị @mention)
        $rootComment   = $comment;
        while ($rootComment->commentable_type === \App\Models\Comment::class) {
            $rootComment = $rootComment->commentable; // leo lên ancestor
        }

        $reply = $rootComment->comments()->create([
            'user_id' => $actor->id,
            'content' => HtmlSanitizer::clean($this->embedImageUrls($request->validated('content') ?? '')),
            'image'   => null,
        ]);
        $this->storeCommentMedia($reply, $request->file('media_images', []));
        SiteCache::bumpAll();

        $post = $this->resolveOwningPost($reply);
        if ($post instanceof Post) {
            // @mention user name chỉ cần khi reply không trực tiếp vào root
            $mentionName = ($comment->id !== $rootComment->id) ? ($mentionedUser->name ?? null) : null;
            broadcast(new CommentChanged(
                CommentChanged::payloadFromComment(
                    $reply, 'created', (int) $post->id,
                    (int) $rootComment->id, $mentionName
                )
            ));
        }

        // Notify chủ bình luận được reply (trừ tự reply)
        $commentOwner = $mentionedUser;
        if ($commentOwner && $commentOwner->id !== $actor->id && $post instanceof Post) {
            try {
                $commentOwner->notify(NewReplyToComment::fromModels($reply, $post));
                NotificationHelper::prune($commentOwner);
            } catch (\Throwable $e) {
                \Log::warning('NewReplyToComment notification failed: ' . $e->getMessage());
            }
        }

        return back()->with('status', 'Đã trả lời bình luận.');
    }

    public function edit(Comment $comment)
    {
        $this->authorize('update', $comment);

        return view('comment.edit', compact('comment'));
    }

    public function update(UpdateCommentRequest $request, Comment $comment): RedirectResponse
    {
        $this->authorize('update', $comment);

        $data = $request->validated();
        $data['content'] = HtmlSanitizer::clean($this->embedImageUrls($data['content'] ?? ''));
        unset($data['media_images']);

        $comment->update($data);
        $this->storeCommentMedia($comment, $request->file('media_images', []));
        SiteCache::bumpAll();
        $post = $this->resolveOwningPost($comment);
        if ($post instanceof Post) {
            $parentCommentId = $comment->commentable_type === Comment::class ? (int) $comment->commentable_id : null;
            broadcast(new CommentChanged(
                CommentChanged::payloadFromComment($comment, 'updated', (int) $post->id, $parentCommentId)
            ));
        }

        return $this->redirectToCommentSource($comment)
            ->with('status', 'Đã cập nhật bình luận.');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $this->authorize('delete', $comment);

        $redirect = $this->redirectToCommentSource($comment);
        $post = $this->resolveOwningPost($comment);
        $commentId = (int) $comment->id;
        $parentCommentId = $comment->commentable_type === Comment::class ? (int) $comment->commentable_id : null;
        $comment->delete();
        SiteCache::bumpAll();
        if ($post instanceof Post) {
            broadcast(new CommentChanged(
                CommentChanged::payloadForDelete((int) $post->id, $commentId, $parentCommentId)
            ));
        }

        return $redirect->with('status', 'Đã xóa bình luận.');
    }

    private function redirectToCommentSource(Comment $comment): RedirectResponse
    {
        $owner = $this->resolveOwningPost($comment);
        if ($owner instanceof Post) {
            return redirect()->route('post.detail', $owner->url);
        }

        return redirect()->route('home');
    }

    private function resolveOwningPost(Comment $comment): ?Post
    {
        $owner = $comment->commentable;
        while ($owner instanceof Comment) {
            $owner = $owner->commentable;
        }

        return $owner instanceof Post ? $owner : null;
    }

    private function embedImageUrls(string $content): string
    {
        $pattern = '/(?<!["\'=])(https?:\/\/[^\s<>"\']+\.(?:png|jpe?g|gif|webp|bmp|svg)(?:\?[^\s<>"\']*)?)/iu';

        return preg_replace_callback($pattern, function (array $matches): string {
            $url = $matches[1];
            $safe = e($url);

            return sprintf(
                '<img src="%s" alt="embedded image" class="img-fluid rounded border my-2" style="max-width: 280px;" onerror="this.onerror=null;this.src=\'%s\';">',
                $safe,
                e(asset('images/image-fallback.png'))
            );
        }, $content) ?? $content;
    }

    private function storeCommentMedia(Comment $comment, array $files): void
    {
        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $storedPath = $file->store('media/comments', 'public');

            $comment->media()->create([
                'path' => Storage::disk('public')->url($storedPath),
                'type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        }
    }
}
