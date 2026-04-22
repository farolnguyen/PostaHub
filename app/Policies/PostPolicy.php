<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function create(User|Admin $user): bool
    {
        if ($user instanceof Admin) {
            return true;
        }

        return $user->rule?->can_post ?? true;
    }

    public function update(User|Admin $user, Post $post): bool
    {
        if ($user instanceof Admin) {
            return true;
        }

        return (int) $user->id === (int) $post->user_id;
    }

    public function delete(User|Admin $user, Post $post): bool
    {
        if ($user instanceof Admin) {
            return true;
        }

        return (int) $user->id === (int) $post->user_id;
    }
}
