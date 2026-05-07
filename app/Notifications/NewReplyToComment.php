<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewReplyToComment extends Notification
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

    public static function fromModels(Comment $reply, Post $post): static
    {
        return new static(
            actorId:    (int) $reply->user_id,
            actorName:  $reply->user->name ?? 'N/A',
            postId:     (int) $post->id,
            postTitle:  (string) $post->title,
            postUrl:    (string) $post->url,
            commentId:  (int) $reply->id,
            excerpt:    mb_substr(strip_tags($reply->content ?? ''), 0, 80),
        );
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'       => 'new_reply_to_comment',
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
