<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PostDetailController extends Controller
{
    public function show(string $url): View
    {
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
            ->where('url', $url)
            ->firstOrFail();

        $hasLiked = false;
        if (Auth::guard('web')->check()) {
            $hasLiked = $post->likes()
                ->where('user_id', Auth::id())
                ->exists();
        }

        return view('post.detail', compact('post', 'hasLiked'));
    }
}
