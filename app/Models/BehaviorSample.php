<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BehaviorSample extends Model
{
    protected $fillable = [
        'user_id',
        'typing_speed',
        'mouse_velocity',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
