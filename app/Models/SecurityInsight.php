<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityInsight extends Model
{
    protected $fillable = [
        'user_id',
        'login_log_id',
        'severity',
        'reason',
        'recommendation',
        'model_name',
        'provider_status',
        'final_action',
        'local_risk_band',
        'ai_response_json',
        'decision_metadata',
    ];

    protected $casts = [
        'ai_response_json' => 'array',
        'decision_metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function loginLog()
    {
        return $this->belongsTo(LoginLog::class);
    }
}
