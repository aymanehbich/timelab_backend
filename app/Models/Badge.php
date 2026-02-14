<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    //
    protected $fillable = ['name', 'description', 'icon', 'required_points', 'condition_key'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_badges');
    }
}
