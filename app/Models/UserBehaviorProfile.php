<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBehaviorProfile extends Model
{
    protected $fillable = [
        'user_id',
        'baseline_data',
    ];

    protected $casts = [
        'baseline_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
