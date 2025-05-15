<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    protected $fillable = [
        'chat_session_id',
        'user_id',
        'message',
        'is_bot_message',
        'is_system_message',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_bot_message' => 'boolean',
        'is_system_message' => 'boolean',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(ChatSession::class, 'chat_session_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
