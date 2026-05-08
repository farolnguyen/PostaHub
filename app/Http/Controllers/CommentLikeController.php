<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Notifications\CommentLiked;
use App\Support\ActorUserResolver;
use App\Support\NotificationHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentLikeController extends Controller
{
    public function toggle(Request $request, Comment $comment): JsonResponse|RedirectResponse
    {
        $actor = ActorUserResolver::current();
        abort_if($actor === null, 403);

        $existing = $comment->commentLikes()->where('user_id', $actor->id)->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            $comment->commentLikes()->create(['user_id' => $actor->id]);
            $liked = true;

            // Notify chủ bình luận (trừ khi tự like, và throttle 90s chống spam)
            $commentOwner = $comment->user;
            if ($commentOwner && $commentOwner->id !== $actor->id) {
                $post = $this->resolveOwningPost($comment);
                if ($post instanceof Post) {
                    $throttleKey = "notif:comment_liked:{$actor->id}:{$comment->id}";
                    if (!NotificationHelper::throttled($throttleKey)) {
                        $commentOwner->notify(CommentLiked::fromModels($actor, $comment, $post));
                        NotificationHelper::prune($commentOwner);
                    }
                }
            }
        }

        $count = $comment->commentLikes()->count();

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
