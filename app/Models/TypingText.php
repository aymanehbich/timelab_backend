<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypingText extends Model
{
    protected $fillable = [
        'level',
        'title',
        'content',
        'word_count',
        'time_limit',
    ];

    public function typingChallenges(): HasMany
    {
        return $this->hasMany(TypingChallenge::class);
    }
}
