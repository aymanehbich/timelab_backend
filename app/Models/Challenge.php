<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Challenge extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'estimated_duration',
        'actual_duration',
        'status',
        'started_at',
        'completed_at',
        'accuracy_percentage'
    ];

    protected $casts = [
        'estimated_duration' => 'integer',
        'actual_duration' => 'integer',
        'accuracy_percentage' => 'float',
        'started_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function timeEstimations()
    {
        return $this->hasMany(TimeEstimation::class);
    }

    // Méthodes métier
    public function calculateAccuracy()
    {
        if ($this->estimated_duration && $this->actual_duration) {
            $difference = abs($this->estimated_duration - $this->actual_duration);
            $accuracy = 100 - (($difference / $this->estimated_duration) * 100);
            $this->accuracy_percentage = max(0, $accuracy);
            $this->save();
        }
    }

    public function isHighAccuracy()
    {
        return $this->accuracy_percentage >= 90;
    }
}
