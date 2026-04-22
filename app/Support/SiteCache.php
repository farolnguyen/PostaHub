<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class SiteCache
{
    private const HOME_VERSION_KEY = 'cache:version:home';

    private const POST_DETAIL_VERSION_KEY = 'cache:version:post_detail';

    private const MYPAGE_VERSION_KEY = 'cache:version:mypage';

    public static function homeVersion(): int
    {
        return (int) Cache::get(self::HOME_VERSION_KEY, 1);
    }

    public static function postDetailVersion(): int
    {
        return (int) Cache::get(self::POST_DETAIL_VERSION_KEY, 1);
    }

    public static function mypageVersion(): int
    {
        return (int) Cache::get(self::MYPAGE_VERSION_KEY, 1);
    }

    public static function bumpAll(): void
    {
        self::increment(self::HOME_VERSION_KEY);
        self::increment(self::POST_DETAIL_VERSION_KEY);
        self::increment(self::MYPAGE_VERSION_KEY);
    }

    private static function increment(string $key): void
    {
        if (! Cache::has($key)) {
            Cache::forever($key, 1);
        }

        Cache::increment($key);
    }
}
