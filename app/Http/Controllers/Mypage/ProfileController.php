<?php

namespace App\Http\Controllers\Mypage;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use App\Support\ActorUserResolver;
use App\Support\SiteCache;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(): View
    {
        $actor = ActorUserResolver::current();
        abort_if($actor === null, 403);
        $userId = $actor->id;
        $commentsPage = max(1, (int) request()->integer('comments_page', 1));
        $likesPage = max(1, (int) request()->integer('likes_page', 1));

        $commentsKey = sprintf('mypage:profile:comments:v%d:u%d:p%d', SiteCache::mypageVersion(), $userId, $commentsPage);
        $commentsPayload = Cache::remember($commentsKey, now()->addMinutes(5), function () use ($userId) {
            $paginator = Comment::query()
                ->where('user_id', $userId)
                ->latest()
                ->paginate(10, ['*'], 'comments_page');

            return [
                'ids' => $paginator->pluck('id')->all(),
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
            ];
        });
        $commentIds = collect($commentsPayload['ids'] ?? [])->map(fn ($id) => (int) $id)->all();
        $commentItems = Comment::query()->whereIn('id', $commentIds)->get()->keyBy('id');
        $orderedComments = collect($commentIds)->map(fn ($id) => $commentItems->get($id))->filter()->values();
        $myComments = new LengthAwarePaginator(
            $orderedComments,
            (int) ($commentsPayload['total'] ?? $orderedComments->count()),
            (int) ($commentsPayload['per_page'] ?? 10),
            (int) ($commentsPayload['current_page'] ?? $commentsPage),
            ['path' => request()->url(), 'pageName' => 'comments_page']
        );

        $likesKey = sprintf('mypage:profile:likes:v%d:u%d:p%d', SiteCache::mypageVersion(), $userId, $likesPage);
        $likesPayload = Cache::remember($likesKey, now()->addMinutes(5), function () use ($userId) {
            $myPostIds = Post::query()
                ->where('user_id', $userId)
                ->pluck('id');

            $paginator = DB::table('likes')
                ->join('users', 'likes.user_id', '=', 'users.id')
                ->whereIn('likes.post_id', $myPostIds)
                ->select('likes.post_id', 'users.id as user_id', 'users.name', 'users.email')
                ->distinct()
                ->orderBy('users.name')
                ->paginate(10, ['*'], 'likes_page');

            return [
                'items' => collect($paginator->items())->map(fn ($row) => (array) $row)->all(),
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
            ];
        });
        $likedByOthers = new LengthAwarePaginator(
            collect($likesPayload['items'] ?? [])->map(fn ($row) => (object) $row),
            (int) ($likesPayload['total'] ?? 0),
            (int) ($likesPayload['per_page'] ?? 10),
            (int) ($likesPayload['current_page'] ?? $likesPage),
            ['path' => request()->url(), 'pageName' => 'likes_page']
        );

        return view('mypage.profile.index', compact('myComments', 'likedByOthers'));
    }
}
