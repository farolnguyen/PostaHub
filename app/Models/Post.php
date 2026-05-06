<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Post extends Model
{
    public const SEMANTIC_STATUS_PENDING = 'pending';

    public const SEMANTIC_STATUS_INDEXED = 'indexed';

    public const SEMANTIC_STATUS_FAILED = 'failed';

    protected $fillable = [
        'user_id',
        'title',
        'url',
        'content',
        'thumbnail',
    ];

    protected function casts(): array
    {
        return [
            'semantic_indexed_at' => 'datetime',
        ];
    }

    /**
     * Admin UI: có hiển thị badge / nút reindex semantic hay không.
     */
    public function semanticIndexTrackingApplicable(): bool
    {
        return (bool) config('semantic_search.enabled', false)
            && config('semantic_search.embedding.provider') === 'local_http';
    }

    /**
     * @return array{class: string, label: string}|null
     */
    public function semanticIndexBadge(): ?array
    {
        if (! $this->semanticIndexTrackingApplicable()) {
            return null;
        }

        if ($this->semantic_index_status === self::SEMANTIC_STATUS_FAILED) {
            return [
                'class' => 'badge-danger',
                'label' => 'Semantic: lỗi index',
            ];
        }

        if ($this->semantic_index_status === self::SEMANTIC_STATUS_PENDING) {
            return [
                'class' => 'badge-warning text-dark',
                'label' => 'Semantic: chưa đồng bộ',
            ];
        }

        if ($this->semantic_index_status === self::SEMANTIC_STATUS_INDEXED) {
            return [
                'class' => 'badge-success',
                'label' => 'Semantic: đã đồng bộ',
            ];
        }

        return [
            'class' => 'badge-secondary',
            'label' => 'Semantic: chưa index',
        ];
    }

    public static function markSemanticIndexPending(int $postId): void
    {
        static::query()->whereKey($postId)->update([
            'semantic_index_status' => self::SEMANTIC_STATUS_PENDING,
            'semantic_index_last_error' => null,
        ]);
    }

    public static function markSemanticIndexFailed(int $postId, string $message): void
    {
        static::query()->whereKey($postId)->update([
            'semantic_index_status' => self::SEMANTIC_STATUS_FAILED,
            'semantic_index_last_error' => mb_substr($message, 0, 2000),
        ]);
    }

    public static function markSemanticIndexSuccess(int $postId): void
    {
        $now = now();
        static::query()->whereKey($postId)->update([
            'semantic_index_status' => self::SEMANTIC_STATUS_INDEXED,
            'semantic_indexed_at' => $now,
            'semantic_index_last_error' => null,
        ]);
    }

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

    public function semanticChunks(): HasMany
    {
        return $this->hasMany(PostSemanticChunk::class);
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
