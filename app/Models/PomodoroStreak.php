<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PomodoroStreak extends Model
{
    protected $fillable = [
        'user_id',
        'current_streak',
        'longest_streak',
        'last_session_date',
    ];

    protected $casts = [
        'last_session_date' => 'date',
        'current_streak'    => 'integer',
        'longest_streak'    => 'integer',
    ];

    // ─────────────────────────────────────
    // Relations
    // ─────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─────────────────────────────────────
    // Accessors
    // ─────────────────────────────────────

    // Est-ce que le streak est actif aujourd'hui ?
    public function getIsActiveAttribute(): bool
    {
        if (!$this->last_session_date) return false;

        return $this->last_session_date->isToday()
            || $this->last_session_date->isYesterday();
    }
}