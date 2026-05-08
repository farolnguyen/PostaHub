<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class PostLiked extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public function __construct(
        private readonly int $actorId,
        private readonly string $actorName,
        private readonly int $postId,
        private readonly string $postTitle,
        private readonly string $postUrl,
    ) {}

    public static function fromModels(User $actor, Post $post): static
    {
        return new static(
            actorId:    (int) $actor->id,
            actorName:  (string) $actor->name,
            postId:     (int) $post->id,
            postTitle:  (string) $post->title,
            postUrl:    (string) $post->url,
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
            'type'       => 'post_liked',
            'actor_id'   => $this->actorId,
            'actor_name' => $this->actorName,
            'post_id'    => $this->postId,
            'post_title' => $this->postTitle,
            'post_url'   => $this->postUrl,
            'link'       => route('post.detail', $this->postUrl),
        ];
    }
}
