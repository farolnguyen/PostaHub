<?php

namespace App\Http\Controllers\Mypage;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(): View
    {
        $userId = Auth::id();

        $myComments = Comment::query()
            ->where('user_id', $userId)
            ->latest()
            ->paginate(10, ['*'], 'comments_page');

        $myPostIds = Post::query()
            ->where('user_id', $userId)
            ->pluck('id');

        $likedByOthers = DB::table('likes')
            ->join('users', 'likes.user_id', '=', 'users.id')
            ->whereIn('likes.post_id', $myPostIds)
            ->select('likes.post_id', 'users.id as user_id', 'users.name', 'users.email')
            ->distinct()
            ->orderBy('users.name')
            ->paginate(10, ['*'], 'likes_page');

        return view('mypage.profile.index', compact('myComments', 'likedByOthers'));
    }
}
