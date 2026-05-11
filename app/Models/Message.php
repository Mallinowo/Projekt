<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    protected $fillable = ['match_id', 'sender_id', 'type', 'body', 'gif_url'];
    protected $casts = ['read_at' => 'datetime'];

    public function match(): BelongsTo { return $this->belongsTo(UserMatch::class, 'match_id'); }
    public function sender(): BelongsTo { return $this->belongsTo(User::class, 'sender_id'); }
    public function reactions(): HasMany { return $this->hasMany(MessageReaction::class, 'message_id'); }
}
