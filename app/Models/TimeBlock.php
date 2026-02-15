<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'task_title',
        'task_duration',
        'task_color',
        'start_hour',
        'end_hour',
        'completed',
        'block_date',
    ];

    protected $casts = [
        'start_hour' => 'decimal:2',
        'end_hour' => 'decimal:2',
        'task_duration' => 'integer',
        'completed' => 'boolean',
        'block_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationship
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scope for a specific date
    public function scopeForDate($query, $date)
    {
        return $query->where('block_date', $date);
    }

    // Scope completed
    public function scopeCompleted($query)
    {
        return $query->where('completed', true);
    }

    // Scope incomplete
    public function scopeIncomplete($query)
    {
        return $query->where('completed', false);
    }

    /**
     * Check for time conflicts.
     */
    public function conflictsWith($startHour, $endHour, $date, $excludeId = null)
    {
        $query = static::where('user_id', $this->user_id)
            ->where('block_date', $date)
            ->where(function ($q) use ($startHour, $endHour) {
                $q->where(function ($q) use ($startHour, $endHour) {
                    $q->where('start_hour', '<', $endHour)
                      ->where('end_hour', '>', $startHour);
                });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Format for API response.
     */
    public function toApiResponse(): array
    {
        return [
            'id' => (string) $this->id,
            'task' => [
                'id' => 'task-' . $this->id,
                'title' => $this->task_title,
                'duration' => $this->task_duration,
                'color' => $this->task_color,
            ],
            'startHour' => (float) $this->start_hour,
            'endHour' => (float) $this->end_hour,
            'completed' => $this->completed,
            'blockDate' => $this->block_date->format('Y-m-d'),
            'createdAt' => $this->created_at->toISOString(),
            'updatedAt' => $this->updated_at->toISOString(),
        ];
    }
}
