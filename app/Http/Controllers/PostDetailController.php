<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Support\ActorUserResolver;
use App\Support\SiteCache;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PostDetailController extends Controller
{
    public function show(string $url): View
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
                    $query->with([
                        'user',
                        'media',
                        'comments.user',
                        'comments.media',
                        'comments.comments.user',
                        'comments.comments.media',
                    ])->latest();
                },
            ])
            ->withCount('likes')
            ->findOrFail($postId);

        $actor = ActorUserResolver::current();
        $hasLiked = $actor
            ? $post->likes()->where('user_id', $actor->id)->exists()
            : false;

        return view('post.detail', compact('post', 'hasLiked'));
    }
}
