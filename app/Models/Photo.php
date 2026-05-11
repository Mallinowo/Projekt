<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Photo extends Model
{
    protected $fillable = ['user_id', 'path', 'order'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
