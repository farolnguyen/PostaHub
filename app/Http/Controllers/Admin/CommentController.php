<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CommentController extends Controller
{
    public function index(): View
    {
        $comments = Comment::query()
            ->with(['user', 'commentable'])
            ->latest()
            ->paginate(15);

        return view('admin.comment.index', compact('comments'));
    }

    public function create(): View
    {
        $users = User::query()->select('id', 'name', 'email')->orderBy('name')->get();
        $posts = Post::query()->select('id', 'title')->latest()->limit(50)->get();
        $comments = Comment::query()->select('id', 'content')->latest()->limit(50)->get();

        return view('admin.comment.create', compact('users', 'posts', 'comments'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRequest($request, true);

        $target = $this->resolveTarget($validated['target_type'], (int) $validated['target_id']);
        if (! $target) {
            return back()->withErrors(['target_id' => 'Doi tuong comment khong ton tai.'])->withInput();
        }

        $comment = $target->comments()->create([
            'user_id' => (int) $validated['user_id'],
            'content' => $validated['content'],
            'image' => $this->storeImageUrl($request, 'image_file', $validated['image'] ?? null),
        ]);

        $this->storeCommentMedia($comment, $request->file('media_images', []));

        return redirect()->route('admin.comment.index')->with('status', 'Admin tao comment thanh cong.');
    }

    public function edit(Comment $comment): View
    {
        $comment->load('media');
        $users = User::query()->select('id', 'name', 'email')->orderBy('name')->get();
        $posts = Post::query()->select('id', 'title')->latest()->limit(50)->get();
        $comments = Comment::query()->select('id', 'content')->where('id', '!=', $comment->id)->latest()->limit(50)->get();

        return view('admin.comment.edit', compact('comment', 'users', 'posts', 'comments'));
    }

    public function update(Request $request, Comment $comment): RedirectResponse
    {
        $validated = $this->validateRequest($request, false);

        $target = $this->resolveTarget($validated['target_type'], (int) $validated['target_id']);
        if (! $target) {
            return back()->withErrors(['target_id' => 'Doi tuong comment khong ton tai.'])->withInput();
        }

        $imagePath = $this->storeImageUrl($request, 'image_file', $validated['image'] ?? $comment->image, $comment->image);

        $comment->update([
            'user_id' => (int) $validated['user_id'],
            'content' => $validated['content'],
            'image' => $imagePath,
            'commentable_type' => $target::class,
            'commentable_id' => $target->id,
        ]);

        $this->storeCommentMedia($comment, $request->file('media_images', []));

        return redirect()->route('admin.comment.index')->with('status', 'Admin cap nhat comment thanh cong.');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $this->deletePhysicalFile($comment->image);

        foreach ($comment->media as $media) {
            $this->deletePhysicalFile($media->path);
            $media->delete();
        }

        $comment->delete();

        return redirect()->route('admin.comment.index')->with('status', 'Admin da xoa comment.');
    }

    private function validateRequest(Request $request, bool $isCreate): array
    {
        return $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'target_type' => ['required', 'in:post,comment'],
            'target_id' => ['required', 'integer'],
            'content' => ['required', 'string', 'min:2'],
            'image' => ['nullable', 'string', 'max:2048'],
            'image_file' => ['nullable', 'image', 'max:4096'],
            'media_images' => ['nullable', 'array'],
            'media_images.*' => ['image', 'max:4096'],
        ]);
    }

    private function resolveTarget(string $type, int $id): Post|Comment|null
    {
        return match ($type) {
            'post' => Post::query()->find($id),
            'comment' => Comment::query()->find($id),
            default => null,
        };
    }

    private function storeImageUrl(Request $request, string $fileKey, ?string $fallback = null, ?string $oldPath = null): ?string
    {
        if ($request->hasFile($fileKey)) {
            if ($oldPath) {
                $this->deletePhysicalFile($oldPath);
            }

            $storedPath = $request->file($fileKey)->store('media/comments', 'public');

            return Storage::disk('public')->url($storedPath);
        }

        return $fallback;
    }

    private function storeCommentMedia(Comment $comment, array $files): void
    {
        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $storedPath = $file->store('media/comments', 'public');

            $comment->media()->create([
                'path' => Storage::disk('public')->url($storedPath),
                'type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        }
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
