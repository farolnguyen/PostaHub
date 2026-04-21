<?php

namespace App\Http\Controllers\Mypage;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        $posts = Post::query()
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

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

        $data = $request->validated();
        $data['user_id'] = Auth::id();
        unset($data['media_images'], $data['thumbnail_file']);

        if ($request->hasFile('thumbnail_file')) {
            $data['thumbnail'] = Storage::disk('public')
                ->url($request->file('thumbnail_file')->store('thumbnails', 'public'));
        }

        $post = Post::create($data);
        $this->storePostMedia($post, $request->file('media_images', []));

        return redirect()
            ->route('mypage.post.index')
            ->with('status', 'Tao bai viet thanh cong.');
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
        unset($data['media_images'], $data['thumbnail_file']);

        if ($request->hasFile('thumbnail_file')) {
            $data['thumbnail'] = Storage::disk('public')
                ->url($request->file('thumbnail_file')->store('thumbnails', 'public'));
        }

        $post->update($data);
        $this->storePostMedia($post, $request->file('media_images', []));

        return redirect()
            ->route('mypage.post.index')
            ->with('status', 'Cap nhat bai viet thanh cong.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);

        $post->delete();

        return redirect()
            ->route('mypage.post.index')
            ->with('status', 'Xoa bai viet thanh cong.');
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
