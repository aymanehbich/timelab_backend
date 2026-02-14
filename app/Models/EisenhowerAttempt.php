<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EisenhowerAttempt extends Model
{
    protected $fillable = [
        'user_id',
        'eisenhower_task_id',
        'chosen_quadrant',
        'is_correct',
        'points_earned',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function task()
    {
        return $this->belongsTo(EisenhowerTask::class, 'eisenhower_task_id');
    }
}
