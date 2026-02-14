<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TypingChallenge extends Model
{
    protected $fillable = [
        'user_id',
        'typing_text_id',
        'level',
        'text_content',
        'time_limit',
        'words_typed',
        'total_words',
        'accuracy_percentage',
        'words_per_minute',
        'time_used',
        'completed',
        'finished_before_timer',
        'points_earned',
    ];

    protected $casts = [
        'completed' => 'boolean',
        'finished_before_timer' => 'boolean',
        'accuracy_percentage' => 'float',
        'words_per_minute' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function typingText(): BelongsTo
    {
        return $this->belongsTo(TypingText::class);
    }
}
