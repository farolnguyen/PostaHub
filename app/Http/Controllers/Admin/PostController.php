<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Models\Post;
use App\Models\User;
use App\Support\HtmlSanitizer;
use App\Support\SiteCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        $posts = Post::query()->with('user')->latest()->paginate(12);

        return view('admin.post.index', compact('posts'));
    }

    public function show(Post $post): View
    {
        $post->load(['user', 'media']);

        return view('admin.post.detail', compact('post'));
    }

    public function create(): View
    {
        $users = User::query()->select('id', 'name', 'email')->orderBy('name')->get();

        return view('admin.post.create', compact('users'));
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['content'] = HtmlSanitizer::clean($data['content']);
        unset($data['media_images'], $data['thumbnail_file']);
        $data['user_id'] = (int) $request->integer('user_id');
        $data['url'] = Post::makeUniqueUrl($data['title']);

        if (! User::query()->whereKey($data['user_id'])->exists()) {
            return back()->withErrors(['user_id' => 'User không tồn tại.'])->withInput();
        }

        if ($request->hasFile('thumbnail_file')) {
            $data['thumbnail'] = Storage::disk('public')
                ->url($request->file('thumbnail_file')->store('thumbnails', 'public'));
        }

        $post = Post::create($data);
        $this->storePostMedia($post, $request->file('media_images', []));
        SiteCache::bumpAll();

        return redirect()
            ->route('admin.post.index')
            ->with('status', 'Admin đã tạo bài viết thành công.');
    }

    public function edit(Post $post): View
    {
        $users = User::query()->select('id', 'name', 'email')->orderBy('name')->get();

        return view('admin.post.edit', compact('post', 'users'));
    }

    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        $data = $request->validated();
        $data['content'] = HtmlSanitizer::clean($data['content']);
        unset($data['media_images'], $data['thumbnail_file']);
        $data['user_id'] = (int) $request->integer('user_id');

        if (! User::query()->whereKey($data['user_id'])->exists()) {
            return back()->withErrors(['user_id' => 'User không tồn tại.'])->withInput();
        }

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
            ->route('admin.post.index')
            ->with('status', 'Admin đã cập nhật bài viết thành công.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();
        SiteCache::bumpAll();

        return redirect()
            ->route('admin.post.index')
            ->with('status', 'Admin đã xóa bài viết thành công.');
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
