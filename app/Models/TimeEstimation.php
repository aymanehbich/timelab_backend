<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeEstimation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'challenge_id',
        'estimated_time',
        'actual_time',
        'difference_minutes'
    ];

    protected $casts = [
        'estimated_time' => 'integer',
        'actual_time' => 'integer',
        'difference_minutes' => 'integer'
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }

    // Méthodes métier
    public function calculateDifference()
    {
        if ($this->estimated_time && $this->actual_time) {
            $this->difference_minutes = $this->actual_time - $this->estimated_time;
            $this->save();
        }
    }
}
