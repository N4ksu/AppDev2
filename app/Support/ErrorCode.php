<?php

namespace App\Support;

class ErrorCode
{
    public const CONFIG_MISSING = 'CONFIG_MISSING';
    public const API_TIMEOUT = 'API_TIMEOUT';
    public const API_BAD_RESPONSE = 'API_BAD_RESPONSE';
    public const VALIDATION_FAILED = 'VALIDATION_FAILED';
    public const UNAUTHORIZED = 'UNAUTHORIZED';
    public const RISK_DENIED = 'RISK_DENIED';
}
