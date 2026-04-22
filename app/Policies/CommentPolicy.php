<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    public function create(User|Admin $user): bool
    {
        if ($user instanceof Admin) {
            return true;
        }

        return $user->rule?->can_comment ?? true;
    }

    public function update(User|Admin $user, Comment $comment): bool
    {
        if ($user instanceof Admin) {
            return true;
        }

        return (int) $user->id === (int) $comment->user_id;
    }

    public function delete(User|Admin $user, Comment $comment): bool
    {
        if ($user instanceof Admin) {
            return true;
        }

        return (int) $user->id === (int) $comment->user_id;
    }
}
