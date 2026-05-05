<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
    protected $fillable = [
        'user_id', 
        'email', 
        'ip_address', 
        'user_agent', 
        'status', 
        'action', 
        'action_taken',
        'login_method', 
        'failed_attempts', 
        'risk_score', 
        'risk_level',
        'ai_risk_score',
        'anomaly_flags',
        'explanation',
        'recommended_action',
    ];

    protected $casts = [
        'anomaly_flags' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
