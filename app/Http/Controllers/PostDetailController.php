<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\CommentLike;
use App\Models\Post;
use App\Support\ActorUserResolver;
use App\Support\SiteCache;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PostDetailController extends Controller
{
    public function show(Request $request, string $url): View|Response
    {
        $cacheKey = sprintf('post:detail:v%d:%s', SiteCache::postDetailVersion(), $url);
        $postId = (int) Cache::remember($cacheKey, now()->addMinutes(5), function () use ($url) {
            return (int) Post::query()->where('url', $url)->value('id');
        });
        abort_if($postId <= 0, 404);

        $post = Post::query()
            ->with([
                'user',
                'media',
                'comments' => function ($query) {
                    $query->withCount('commentLikes')->with([
                        'user',
                        'media',
                        'comments' => function ($q) {
                            $q->withCount('commentLikes')->with([
                                'user',
                                'media',
                                'comments' => function ($q2) {
                                    $q2->withCount('commentLikes')->with([
                                        'user',
                                        'media',
                                        'comments' => function ($q3) {
                                            $q3->withCount('commentLikes')->with([
                                                'user',
                                                'media',
                                                'comments' => function ($q4) {
                                                    $q4->withCount('commentLikes')->with([
                                                        'user',
                                                        'media',
                                                    ])->latest();
                                                },
                                            ])->latest();
                                        },
                                    ])->latest();
                                },
                            ])->latest();
                        },
                    ])->latest();
                },
            ])
            ->withCount('likes')
            ->findOrFail($postId);

        $actor = ActorUserResolver::current();
        $hasLiked = $actor
            ? $post->likes()->where('user_id', $actor->id)->exists()
            : false;

        // Set comment IDs mà user hiện tại đã like (dùng trong view)
        $likedCommentIds = $actor
            ? CommentLike::where('user_id', $actor->id)->pluck('comment_id')->flip()->all()
            : [];

        // Xây dựng flat replies (Facebook-style) cho mỗi top-level comment
        foreach ($post->comments as $topComment) {
            $topComment->flatReplies = $this->buildFlatReplies($topComment);
        }

        if ($request->boolean('comments_only')) {
            return response()->view('post._comments-fragment', compact('post', 'likedCommentIds'));
        }

        return view('post.detail', compact('post', 'hasLiked', 'likedCommentIds'));
    }

    /**
     * Gom tất cả reply (mọi độ sâu) của một top-level comment thành 1 flat collection,
     * sắp xếp theo created_at. Mỗi reply có thêm $reply->mentionUser (user được tag).
     */
    private function buildFlatReplies(Comment $topComment): Collection
    {
        $flat = collect();
        $this->traverseChildren($topComment->comments, $topComment->id, $flat);
        return $flat->sortBy('created_at')->values();
    }

    private function traverseChildren(Collection $children, int $topId, Collection &$flat, ?Comment $directParent = null): void
    {
        foreach ($children as $reply) {
            // @mention chỉ hiện khi reply không phải trả lời trực tiếp root
            $reply->mentionUser = ($directParent && $directParent->id !== $topId)
                ? $directParent->user
                : null;
            $flat->push($reply);
            if ($reply->comments->isNotEmpty()) {
                $this->traverseChildren($reply->comments, $topId, $flat, $reply);
            }
        }
    }
}
