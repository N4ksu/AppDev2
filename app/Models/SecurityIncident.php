<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SecurityIncident extends Model
{
    protected $fillable = [
        'type',
        'severity',
        'status',
        'source_ip',
        'target_identifier',
        'affected_user_id',
        'logs_count',
        'first_detected_at',
        'last_detected_at',
        'remarks',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'first_detected_at' => 'datetime',
        'last_detected_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function affectedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'affected_user_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(LoginLog::class);
    }
}
