<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    public function create(User $user): bool
    {
        return $user->rule?->can_comment ?? true;
    }

    public function update(User $user, Comment $comment): bool
    {
        return (int) $user->id === (int) $comment->user_id;
    }

    public function delete(User $user, Comment $comment): bool
    {
        return (int) $user->id === (int) $comment->user_id;
    }
}
