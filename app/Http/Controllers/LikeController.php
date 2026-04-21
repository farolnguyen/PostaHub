<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    public function toggle(Post $post): RedirectResponse
    {
        $userId = Auth::id();

        $existingLike = $post->likes()
            ->where('user_id', $userId)
            ->first();

        if ($existingLike) {
            $existingLike->delete();

            return back()->with('status', 'Ban da bo like bai viet.');
        }

        $post->likes()->create([
            'user_id' => $userId,
        ]);

        return back()->with('status', 'Ban da like bai viet.');
    }
}

