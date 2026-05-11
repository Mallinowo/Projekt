<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UserMatch extends Model
{
    protected $table = 'matches';
    protected $fillable = ['user1_id', 'user2_id'];

    public function user1(): BelongsTo { return $this->belongsTo(User::class, 'user1_id'); }
    public function user2(): BelongsTo { return $this->belongsTo(User::class, 'user2_id'); }
    public function messages(): HasMany { return $this->hasMany(Message::class, 'match_id')->orderBy('created_at'); }
    public function latestMessage(): HasOne { return $this->hasOne(Message::class, 'match_id')->latestOfMany(); }

    public function getOtherUser(int $myId): User
    {
        return $this->user1_id === $myId ? $this->user2 : $this->user1;
    }

    public function getLastMessage(): ?Message
    {
        return $this->messages()->latest()->first();
    }

    public function getUnreadCount(int $userId): int
    {
        return $this->messages()->where('sender_id', '!=', $userId)->whereNull('read_at')->count();
    }
}
