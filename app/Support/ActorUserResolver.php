<?php

namespace App\Support;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ActorUserResolver
{
    public static function current(): ?User
    {
        if (Auth::guard('web')->check()) {
            return Auth::guard('web')->user();
        }

        if (! Auth::guard('admin')->check()) {
            return null;
        }

        /** @var Admin $admin */
        $admin = Auth::guard('admin')->user();
        $email = sprintf('admin.%d@postahub.local', $admin->id);

        $user = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Admin '.$admin->name,
                'password' => 'Admin!'.bin2hex(random_bytes(8)),
            ]
        );

        if (! $user->rule) {
            $user->rule()->create([
                'can_post' => true,
                'can_comment' => true,
            ]);
        }

        if ($user->name !== 'Admin '.$admin->name) {
            $user->forceFill(['name' => 'Admin '.$admin->name])->save();
        }

        return $user;
    }
}
