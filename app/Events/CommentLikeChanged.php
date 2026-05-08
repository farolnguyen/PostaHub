<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

/**
 * Broadcast khi like count của một bình luận thay đổi.
 * Dùng public channel (không cần auth) để cả user chưa đăng nhập cũng cập nhật được.
 */
class CommentLikeChanged implements ShouldBroadcastNow
{
    public int $commentId;
    public int $count;
    public int $postId;

    public function __construct(int $commentId, int $count, int $postId)
    {
        $this->commentId = $commentId;
        $this->count     = $count;
        $this->postId    = $postId;
    }

    public function broadcastOn(): array
    {
        return [new Channel('post.' . $this->postId . '.comments')];
    }

    public function broadcastAs(): string
    {
        return 'CommentLikeChanged';
    }

    public function broadcastWith(): array
    {
        return [
            'comment_id' => $this->commentId,
            'count'      => $this->count,
        ];
    }
}
