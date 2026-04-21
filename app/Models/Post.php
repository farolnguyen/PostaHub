<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Post extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'url',
        'content',
        'thumbnail',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    /**
     * Sinh slug URL duy nhất từ tiêu đề (hỗ trợ tiếng Việt qua Str::slug locale vi).
     */
    public static function makeUniqueUrl(string $title, ?int $exceptPostId = null): string
    {
        $base = Str::slug($title, '-', 'vi');
        if ($base === '') {
            $base = 'bai-viet';
        }

        $base = substr($base, 0, 220);

        $slug = $base;
        $counter = 2;
        while (static::query()
            ->where('url', $slug)
            ->when($exceptPostId !== null, fn ($q) => $q->where('id', '!=', $exceptPostId))
            ->exists()) {
            $suffix = '-'.$counter;
            $counter++;
            $slug = substr($base, 0, max(1, 220 - strlen($suffix))).$suffix;
            $slug = rtrim($slug, '-');
        }

        return $slug;
    }
}
