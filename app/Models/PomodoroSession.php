<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PomodoroSession extends Model
{
    protected $fillable = [
        'user_id',
        'task_name',
        'duration',
        'type',
        'started_at',
        'completed_at',
        'completed',
        'interruptions',
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
        'completed'    => 'boolean',
        'duration'     => 'integer',
        'interruptions'=> 'integer',
    ];

    // ─────────────────────────────────────
    // Relations
    // ─────────────────────────────────────

    // Une session appartient à un user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─────────────────────────────────────
    // Scopes (filtres réutilisables)
    // ─────────────────────────────────────

    // Seulement les sessions "work"
    public function scopeWork($query)
    {
        return $query->where('type', 'work');
    }

    // Seulement les sessions complétées
    public function scopeCompleted($query)
    {
        return $query->where('completed', true);
    }

    // Sessions du jour
    public function scopeToday($query)
    {
        return $query->whereDate('started_at', today());
    }

    // ─────────────────────────────────────
    // Accessors utiles
    // ─────────────────────────────────────

    // Durée réelle en minutes (temps entre start et complete)
    public function getRealDurationAttribute(): int
    {
        if (!$this->completed_at) return 0;
        return (int) $this->started_at->diffInMinutes($this->completed_at);
    }
}