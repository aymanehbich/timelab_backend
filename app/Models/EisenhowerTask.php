<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EisenhowerTask extends Model
{
    use HasFactory;

    protected $table = 'eisenhower_tasks';

    protected $fillable = [
        'title',
        'description',
        'is_urgent',
        'is_important',
        'quadrant',
        'explanation',
        'level',
    ];

    protected $casts = [
        'is_urgent' => 'boolean',
        'is_important' => 'boolean',
    ];

    public function attempts()
    {
        return $this->hasMany(EisenhowerAttempt::class);
    }
}
