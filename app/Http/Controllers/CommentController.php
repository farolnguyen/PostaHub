<?php

namespace App\Http\Controllers;

use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CommentController extends Controller
{
    public function storeForPost(StoreCommentRequest $request, Post $post): RedirectResponse
    {
        $this->authorize('create', Comment::class);

        $comment = $post->comments()->create([
            'user_id' => Auth::id(),
            'content' => $request->validated('content'),
            'image' => $this->resolveImagePath($request),
        ]);
        $this->storeCommentMedia($comment, $request->file('media_images', []));

        return back()->with('status', 'Da them comment cho bai viet.');
    }

    public function storeReply(StoreCommentRequest $request, Comment $comment): RedirectResponse
    {
        $this->authorize('create', Comment::class);

        $reply = $comment->comments()->create([
            'user_id' => Auth::id(),
            'content' => $request->validated('content'),
            'image' => $this->resolveImagePath($request),
        ]);
        $this->storeCommentMedia($reply, $request->file('media_images', []));

        return back()->with('status', 'Da tra loi comment.');
    }

    public function edit(Comment $comment)
    {
        $this->authorize('update', $comment);

        return view('comment.edit', compact('comment'));
    }

    public function update(UpdateCommentRequest $request, Comment $comment): RedirectResponse
    {
        $this->authorize('update', $comment);

        $data = $request->validated();
        $data['image'] = $this->resolveImagePath($request, $comment->image);
        unset($data['image_file'], $data['media_images']);

        $comment->update($data);
        $this->storeCommentMedia($comment, $request->file('media_images', []));

        return $this->redirectToCommentSource($comment)
            ->with('status', 'Da cap nhat comment.');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $this->authorize('delete', $comment);

        $redirect = $this->redirectToCommentSource($comment);
        $comment->delete();

        return $redirect->with('status', 'Da xoa comment.');
    }

    private function redirectToCommentSource(Comment $comment): RedirectResponse
    {
        $owner = $comment->commentable;

        while ($owner instanceof Comment) {
            $owner = $owner->commentable;
        }

        if ($owner instanceof Post) {
            return redirect()->route('post.detail', $owner->url);
        }

        return redirect()->route('home');
    }

    private function resolveImagePath(StoreCommentRequest|UpdateCommentRequest $request, ?string $fallback = null): ?string
    {
        if ($request->hasFile('image_file')) {
            $storedPath = $request->file('image_file')->store('media/comments', 'public');

            return Storage::disk('public')->url($storedPath);
        }

        return $request->validated('image') ?: $fallback;
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
}

