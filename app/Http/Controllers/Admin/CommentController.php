<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Support\HtmlSanitizer;
use App\Support\SiteCache;
use App\Support\UploadErrorLogger;
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
        UploadErrorLogger::logFromPhpFiles(['media_images'], __METHOD__);
        $validated = $this->validateRequest($request, true);

        $target = $this->resolveTarget($validated['target_type'], (int) $validated['target_id']);
        if (! $target) {
            return back()->withErrors(['target_id' => 'Đối tượng bình luận không tồn tại.'])->withInput();
        }

        $comment = $target->comments()->create([
            'user_id' => (int) $validated['user_id'],
            'content' => HtmlSanitizer::clean($this->embedImageUrls($validated['content'])),
            'image' => null,
        ]);

        $this->storeCommentMedia($comment, $request->file('media_images', []));
        SiteCache::bumpAll();

        return redirect()->route('admin.comment.index')->with('status', 'Admin đã tạo bình luận thành công.');
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
        UploadErrorLogger::logFromPhpFiles(['media_images'], __METHOD__);
        $validated = $this->validateRequest($request, false);

        $target = $this->resolveTarget($validated['target_type'], (int) $validated['target_id']);
        if (! $target) {
            return back()->withErrors(['target_id' => 'Đối tượng bình luận không tồn tại.'])->withInput();
        }

        $comment->update([
            'user_id' => (int) $validated['user_id'],
            'content' => HtmlSanitizer::clean($this->embedImageUrls($validated['content'])),
            'image' => $comment->image,
            'commentable_type' => $target::class,
            'commentable_id' => $target->id,
        ]);

        $this->storeCommentMedia($comment, $request->file('media_images', []));
        SiteCache::bumpAll();

        return redirect()->route('admin.comment.index')->with('status', 'Admin đã cập nhật bình luận thành công.');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $this->deletePhysicalFile($comment->image);

        foreach ($comment->media as $media) {
            $this->deletePhysicalFile($media->path);
            $media->delete();
        }

        $comment->delete();
        SiteCache::bumpAll();

        return redirect()->route('admin.comment.index')->with('status', 'Admin đã xóa bình luận.');
    }

    private function validateRequest(Request $request, bool $isCreate): array
    {
        return $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'target_type' => ['required', 'in:post,comment'],
            'target_id' => ['required', 'integer'],
            'content' => ['required', 'string', 'min:2'],
            'media_images' => ['nullable', 'array', 'max:5'],
            'media_images.*' => ['file', 'mimetypes:image/jpeg,image/png,image/gif,image/webp,image/bmp,image/svg+xml,video/mp4,video/webm,video/ogg,audio/mpeg,audio/mp3,audio/wav,audio/ogg,audio/webm,audio/mp4,audio/x-m4a', 'max:102400'],
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

    private function embedImageUrls(string $content): string
    {
        $pattern = '/(?<!["\'=])(https?:\/\/[^\s<>"\']+\.(?:png|jpe?g|gif|webp|bmp|svg)(?:\?[^\s<>"\']*)?)/iu';

        return preg_replace_callback($pattern, function (array $matches): string {
            $url = $matches[1];
            $safe = e($url);

            return sprintf(
                '<img src="%s" alt="embedded image" class="img-fluid rounded border my-2" style="max-width: 280px;" onerror="this.onerror=null;this.src=\'%s\';">',
                $safe,
                e(asset('images/image-fallback.png'))
            );
        }, $content) ?? $content;
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
