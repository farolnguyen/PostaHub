<?php

namespace App\Http\Controllers\Mypage;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Models\Post;
use App\Support\ActorUserResolver;
use App\Support\HtmlSanitizer;
use App\Support\SiteCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        $actor = ActorUserResolver::current();
        abort_if($actor === null, 403);
        $userId = (int) $actor->id;
        $page = max(1, (int) request()->integer('page', 1));
        $cacheKey = sprintf('mypage:posts:v%d:u%d:p%d', SiteCache::mypageVersion(), $userId, $page);
        $payload = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($userId) {
            $paginator = Post::query()
                ->where('user_id', $userId)
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
        $items = Post::query()->whereIn('id', $ids)->latest()->get()->keyBy('id');
        $ordered = collect($ids)->map(fn ($id) => $items->get($id))->filter()->values();
        $posts = new LengthAwarePaginator(
            $ordered,
            (int) ($payload['total'] ?? $ordered->count()),
            (int) ($payload['per_page'] ?? 10),
            (int) ($payload['current_page'] ?? $page),
            ['path' => request()->url(), 'pageName' => 'page']
        );

        return view('mypage.post.index', compact('posts'));
    }

    public function create(): View
    {
        $this->authorize('create', Post::class);

        return view('mypage.post.create');
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        $this->authorize('create', Post::class);
        $actor = ActorUserResolver::current();
        abort_if($actor === null, 403);

        $data = $request->validated();
        $data['user_id'] = $actor->id;
        $data['url'] = Post::makeUniqueUrl($data['title']);
        $data['content'] = HtmlSanitizer::clean($data['content']);
        unset($data['media_images'], $data['thumbnail_file']);

        if ($request->hasFile('thumbnail_file')) {
            $data['thumbnail'] = Storage::disk('public')
                ->url($request->file('thumbnail_file')->store('thumbnails', 'public'));
        }

        $post = Post::create($data);
        $this->storePostMedia($post, $request->file('media_images', []));
        SiteCache::bumpAll();

        return redirect()
            ->route('mypage.post.index')
            ->with('status', 'Tạo bài viết thành công.');
    }

    public function edit(Post $post): View
    {
        $this->authorize('update', $post);

        return view('mypage.post.edit', compact('post'));
    }

    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        $this->authorize('update', $post);

        $data = $request->validated();
        $data['content'] = HtmlSanitizer::clean($data['content']);
        unset($data['media_images'], $data['thumbnail_file']);

        if ($post->title !== $data['title']) {
            $data['url'] = Post::makeUniqueUrl($data['title'], $post->id);
        }

        if ($request->hasFile('thumbnail_file')) {
            $data['thumbnail'] = Storage::disk('public')
                ->url($request->file('thumbnail_file')->store('thumbnails', 'public'));
        }

        $post->update($data);
        $this->storePostMedia($post, $request->file('media_images', []));
        SiteCache::bumpAll();

        return redirect()
            ->route('mypage.post.index')
            ->with('status', 'Cập nhật bài viết thành công.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);

        $post->delete();
        SiteCache::bumpAll();

        return redirect()
            ->route('mypage.post.index')
            ->with('status', 'Xóa bài viết thành công.');
    }

    private function storePostMedia(Post $post, array $files): void
    {
        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $storedPath = $file->store('media/posts', 'public');

            $post->media()->create([
                'path' => Storage::disk('public')->url($storedPath),
                'type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        }
    }
}
