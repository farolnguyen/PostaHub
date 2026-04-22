<?php

namespace App\Http\Controllers\Mypage;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Support\ActorUserResolver;
use App\Support\SiteCache;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class LikeController extends Controller
{
    public function index(): View
    {
        $actor = ActorUserResolver::current();
        abort_if($actor === null, 403);
        $userId = (int) $actor->id;
        $page = max(1, (int) request()->integer('page', 1));
        $cacheKey = sprintf('mypage:likes:v%d:u%d:p%d', SiteCache::mypageVersion(), $userId, $page);
        $payload = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($userId) {
            $paginator = Post::query()
                ->whereHas('likes', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->latest()
                ->paginate(10);

            return [
                'ids' => $paginator->pluck('id')->all(),
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
            ];
        });
        $ids = collect($payload['ids'] ?? [])->map(fn ($id) => (int) $id)->all();
        $items = Post::query()
            ->with('user')
            ->withCount('likes')
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');
        $ordered = collect($ids)->map(fn ($id) => $items->get($id))->filter()->values();
        $likedPosts = new LengthAwarePaginator(
            $ordered,
            (int) ($payload['total'] ?? $ordered->count()),
            (int) ($payload['per_page'] ?? 10),
            (int) ($payload['current_page'] ?? $page),
            ['path' => request()->url(), 'pageName' => 'page']
        );

        return view('mypage.like.index', compact('likedPosts'));
    }
}
