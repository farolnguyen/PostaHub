<?php

namespace App\Http\Controllers;

use App\Events\CommentLikeChanged;
use App\Models\Comment;
use App\Models\Post;
use App\Notifications\CommentLiked;
use App\Support\ActorUserResolver;
use App\Support\NotificationHelper;
use App\Support\SiteCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentLikeController extends Controller
{
    public function toggle(Request $request, Comment $comment): JsonResponse|RedirectResponse
    {
        $actor = ActorUserResolver::current();
        abort_if($actor === null, 403);

        // Resolve owning post once — dùng cho cả notify lẫn broadcast
        $owningPost = $this->resolveOwningPost($comment);

        $existing = $comment->commentLikes()->where('user_id', $actor->id)->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            $comment->commentLikes()->create(['user_id' => $actor->id]);
            $liked = true;

            // Notify chủ bình luận (trừ khi tự like, và throttle 90s chống spam)
            $commentOwner = $comment->user;
            if ($commentOwner && $commentOwner->id !== $actor->id && $owningPost instanceof Post) {
                $throttleKey = "notif:comment_liked:{$actor->id}:{$comment->id}";
                if (!NotificationHelper::throttled($throttleKey)) {
                    try {
                        $commentOwner->notify(CommentLiked::fromModels($actor, $comment, $owningPost));
                        NotificationHelper::prune($commentOwner);
                    } catch (\Throwable $e) {
                        \Log::warning('CommentLiked notification failed: ' . $e->getMessage());
                    }
                }
            }
        }

        SiteCache::bumpAll();
        $count = $comment->commentLikes()->count();

        // Broadcast like count realtime (ShouldBroadcastNow — đồng bộ, nhẹ)
        if ($owningPost instanceof Post) {
            try {
                broadcast(new CommentLikeChanged((int) $comment->id, $count, (int) $owningPost->id));
            } catch (\Throwable $e) {
                \Log::warning('CommentLikeChanged broadcast failed: ' . $e->getMessage());
            }
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['liked' => $liked, 'count' => $count]);
        }

        return back();
    }

    private function resolveOwningPost(Comment $comment): ?Post
    {
        $owner = $comment->commentable;
        while ($owner instanceof Comment) {
            $owner = $owner->commentable;
        }

        return $owner instanceof Post ? $owner : null;
    }
}
