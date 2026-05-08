<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class NotificationHelper
{
    /**
     * Throttle: trả về true nếu nên bỏ qua (đã notify gần đây với cùng key).
     * Dùng cho like/unlike để tránh spam notification khi user thao tác nhanh.
     *
     * @param  string  $key       ví dụ: "notif:post_liked:actor5:post12"
     * @param  int     $ttl       giây, mặc định 90s
     */
    public static function throttled(string $key, int $ttl = 90): bool
    {
        if (Cache::has($key)) {
            return true;
        }
        Cache::put($key, 1, $ttl);
        return false;
    }

    /**
     * Tự động xoá notification cũ nhất nếu user vượt quá $max notifications.
     * Gọi sau mỗi $notifiable->notify(...).
     *
     * @param  Model  $notifiable  User model có trait Notifiable
     * @param  int    $max         Giới hạn giữ lại (mặc định 100)
     */
    public static function prune(Model $notifiable, int $max = 100): void
    {
        $count = $notifiable->notifications()->count();
        if ($count > $max) {
            $notifiable->notifications()
                ->oldest()
                ->limit($count - $max)
                ->delete();
        }
    }
}
