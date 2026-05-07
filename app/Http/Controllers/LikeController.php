<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Notifications\PostLiked;
use App\Support\ActorUserResolver;
use App\Support\SiteCache;
use Illuminate\Http\RedirectResponse;

class LikeController extends Controller
{
    public function toggle(Post $post): RedirectResponse
    {
        $actor = ActorUserResolver::current();
        abort_if($actor === null, 403);

        $existingLike = $post->likes()->where('user_id', $actor->id)->first();

        if ($existingLike) {
            $existingLike->delete();
            SiteCache::bumpAll();

            return back()->with('status', 'Bạn đã bỏ thích bài viết.');
        }

        $post->likes()->create(['user_id' => $actor->id]);
        SiteCache::bumpAll();

        // Notify tác giả bài viết (trừ khi tự like bài của mình)
        $postOwner = $post->user;
        if ($postOwner && $postOwner->id !== $actor->id) {
            $postOwner->notify(PostLiked::fromModels($actor, $post));
        }

        return back()->with('status', 'Bạn đã thích bài viết.');
    }
}
