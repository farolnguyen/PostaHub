<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Media extends Model
{
    protected $table = 'media';

    protected $fillable = [
        'path',
        'type',
        'size',
        'mediable_type',
        'mediable_id',
    ];

    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }
}
