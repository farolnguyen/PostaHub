<?php

namespace App\Http\Controllers\Mypage;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LikeController extends Controller
{
    public function index(): View
    {
        $likedPosts = Post::query()
            ->with('user')
            ->whereHas('likes', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->withCount('likes')
            ->latest()
            ->paginate(10);

        return view('mypage.like.index', compact('likedPosts'));
    }
}
