<?php

use Illuminate\Support\Facades\Broadcast;
use App\Support\ActorUserResolver;

/*
 * Kênh thông báo cá nhân: App.Models.User.{id}
 * Dùng cho cả web user và admin (shadow User).
 * $user được resolve từ ActorUserResolver thay vì chỉ dùng web guard.
 */
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
