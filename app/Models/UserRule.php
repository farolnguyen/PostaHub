<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRule extends Model
{
    protected $fillable = [
        'user_id',
        'can_post',
        'can_comment',
    ];

    protected $casts = [
        'can_post' => 'boolean',
        'can_comment' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
