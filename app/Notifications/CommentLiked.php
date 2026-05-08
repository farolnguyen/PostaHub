<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class CommentLiked extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public function __construct(
        private readonly int $actorId,
        private readonly string $actorName,
        private readonly int $postId,
        private readonly string $postTitle,
        private readonly string $postUrl,
        private readonly int $commentId,
        private readonly string $excerpt,
    ) {}

    public static function fromModels(User $actor, Comment $comment, Post $post): static
    {
        return new static(
            actorId:    (int) $actor->id,
            actorName:  (string) $actor->name,
            postId:     (int) $post->id,
            postTitle:  (string) $post->title,
            postUrl:    (string) $post->url,
            commentId:  (int) $comment->id,
            excerpt:    mb_substr(strip_tags($comment->content ?? ''), 0, 80),
        );
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'       => 'comment_liked',
            'actor_id'   => $this->actorId,
            'actor_name' => $this->actorName,
            'post_id'    => $this->postId,
            'post_title' => $this->postTitle,
            'post_url'   => $this->postUrl,
            'comment_id' => $this->commentId,
            'excerpt'    => $this->excerpt,
            'link'       => route('post.detail', $this->postUrl) . '#comment-' . $this->commentId,
        ];
    }
}
