<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostSemanticChunk extends Model
{
    protected $fillable = [
        'post_id',
        'chunk_index',
        'chunk_text',
        'content_hash',
        'embedding_model',
        'embedding_dimensions',
        'vector_point_id',
        'is_indexed',
        'indexed_at',
        'last_error',
    ];

    protected $casts = [
        'is_indexed' => 'boolean',
        'indexed_at' => 'datetime',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}

