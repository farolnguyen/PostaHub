<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Media;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function index(): View
    {
        $medias = Media::query()
            ->with('mediable')
            ->latest()
            ->paginate(12);

        return view('admin.media.index', compact('medias'));
    }

    public function create(): View
    {
        $posts = Post::query()->select('id', 'title')->latest()->limit(30)->get();
        $comments = Comment::query()->select('id', 'content')->latest()->limit(30)->get();

        return view('admin.media.upload', compact('posts', 'comments'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'target_type' => ['required', 'in:post,comment'],
            'target_id' => ['required', 'integer'],
            'file' => ['required', 'image', 'max:4096'],
        ]);

        $target = $this->resolveTarget($validated['target_type'], (int) $validated['target_id']);

        if (! $target) {
            return back()->withErrors(['target_id' => 'Đối tượng gắn media không tồn tại.'])->withInput();
        }

        $file = $request->file('file');
        $storedPath = $file->store('media/admin', 'public');

        $target->media()->create([
            'path' => Storage::disk('public')->url($storedPath),
            'type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);

        return redirect()->route('admin.media.index')->with('status', 'Tải lên media thành công.');
    }

    public function show(Media $media): View
    {
        $media->load('mediable');

        return view('admin.media.detail', compact('media'));
    }

    public function edit(Media $media): View
    {
        $posts = Post::query()->select('id', 'title')->latest()->limit(30)->get();
        $comments = Comment::query()->select('id', 'content')->latest()->limit(30)->get();

        return view('admin.media.edit', compact('media', 'posts', 'comments'));
    }

    public function update(Request $request, Media $media): RedirectResponse
    {
        $validated = $request->validate([
            'target_type' => ['required', 'in:post,comment'],
            'target_id' => ['required', 'integer'],
            'type' => ['nullable', 'string', 'max:255'],
            'file' => ['nullable', 'image', 'max:4096'],
        ]);

        $target = $this->resolveTarget($validated['target_type'], (int) $validated['target_id']);

        if (! $target) {
            return back()->withErrors(['target_id' => 'Đối tượng gắn media không tồn tại.'])->withInput();
        }

        $data = [
            'mediable_type' => $target::class,
            'mediable_id' => $target->id,
            'type' => $validated['type'] ?? $media->type,
        ];

        if ($request->hasFile('file')) {
            $this->deletePhysicalFile($media->path);

            $file = $request->file('file');
            $storedPath = $file->store('media/admin', 'public');

            $data['path'] = Storage::disk('public')->url($storedPath);
            $data['size'] = $file->getSize();
            $data['type'] = $file->getClientMimeType();
        }

        $media->update($data);

        return redirect()->route('admin.media.detail', $media)->with('status', 'Cập nhật media thành công.');
    }

    public function destroy(Media $media): RedirectResponse
    {
        $this->deletePhysicalFile($media->path);
        $media->delete();

        return redirect()->route('admin.media.index')->with('status', 'Xóa media thành công.');
    }

    private function resolveTarget(string $type, int $id): Post|Comment|null
    {
        return match ($type) {
            'post' => Post::query()->find($id),
            'comment' => Comment::query()->find($id),
            default => null,
        };
    }

    private function deletePhysicalFile(?string $urlPath): void
    {
        if (! $urlPath) {
            return;
        }

        $path = parse_url($urlPath, PHP_URL_PATH) ?: '';

        if (str_starts_with($path, '/storage/')) {
            $relative = substr($path, strlen('/storage/'));
            if ($relative) {
                Storage::disk('public')->delete($relative);
            }
        }
    }
}
