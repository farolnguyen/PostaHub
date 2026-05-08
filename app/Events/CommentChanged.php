<?php

namespace App\Events;

use App\Models\Comment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentChanged implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    /**
     * @param  array{
     *     action: string,
     *     post_id: int,
     *     comment_id: int,
     *     parent_comment_id: ?int,
     *     comment: ?array<string, mixed>,
     *     server_ts: string
     * }  $payload
     */
    public function __construct(
        public array $payload
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('post.'.$this->payload['post_id'].'.comments');
    }

    public function broadcastAs(): string
    {
        return 'CommentChanged';
    }

    public static function payloadFromComment(
        Comment $comment,
        string $action,
        int $postId,
        ?int $rootCommentId = null,
        ?string $mentionUserName = null
    ): array {
        $comment->loadMissing(['user:id,name', 'media:id,mediable_id,mediable_type,path,type,size']);

        return [
            'action'            => $action,
            'post_id'           => $postId,
            'comment_id'        => (int) $comment->id,
            'parent_comment_id' => $rootCommentId,    // kept for backward compat
            'root_comment_id'   => $rootCommentId,    // top-level ancestor ID
            'mention_user_name' => $mentionUserName,  // @mention khi reply to reply
            'comment' => [
                'id' => (int) $comment->id,
                'content_html' => (string) $comment->content,
                'author_name' => (string) ($comment->user->name ?? 'N/A'),
                'author_id' => (int) $comment->user_id,
                'created_at_iso' => optional($comment->created_at)->toIso8601String(),
                'updated_at_iso' => optional($comment->updated_at)->toIso8601String(),
                'media' => $comment->media->map(function ($media) {
                    $type = (string) $media->type;

                    return [
                        'id' => (int) $media->id,
                        'path' => (string) $media->path,
                        'type' => $type,
                        'size' => (int) ($media->size ?? 0),
                        'kind' => str_starts_with($type, 'image/')
                            ? 'image'
                            : (str_starts_with($type, 'video/')
                                ? 'video'
                                : (str_starts_with($type, 'audio/') ? 'audio' : 'file')),
                    ];
                })->values()->all(),
            ],
            'server_ts' => now()->toIso8601String(),
        ];
    }

    public static function payloadForDelete(int $postId, int $commentId, ?int $rootCommentId = null): array
    {
        return [
            'action'            => 'deleted',
            'post_id'           => $postId,
            'comment_id'        => $commentId,
            'parent_comment_id' => $rootCommentId,
            'root_comment_id'   => $rootCommentId,
            'mention_user_name' => null,
            'comment'           => null,
            'server_ts'         => now()->toIso8601String(),
        ];
    }
}
