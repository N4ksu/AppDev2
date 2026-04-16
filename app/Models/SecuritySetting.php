<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecuritySetting extends Model
{
    protected $fillable = [
        'max_failed_attempts',
        'lock_duration_minutes',
    ];
}
