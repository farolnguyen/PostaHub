<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Support\ActorUserResolver;
use App\Support\SiteCache;
use Illuminate\Http\RedirectResponse;

class LikeController extends Controller
{
    public function toggle(Post $post): RedirectResponse
    {
        $actor = ActorUserResolver::current();
        abort_if($actor === null, 403);
        $userId = $actor->id;

        $existingLike = $post->likes()
            ->where('user_id', $userId)
            ->first();

        if ($existingLike) {
            $existingLike->delete();
            SiteCache::bumpAll();

            return back()->with('status', 'Bạn đã bỏ thích bài viết.');
        }

        $post->likes()->create([
            'user_id' => $userId,
        ]);
        SiteCache::bumpAll();

        return back()->with('status', 'Bạn đã thích bài viết.');
    }
}
