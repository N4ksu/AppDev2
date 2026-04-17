<?php

namespace App\Services;

use App\Models\LoginLog;
use App\Models\SecurityIncident;
use App\Models\User;
use Illuminate\Support\Carbon;

class SecurityIncidentService
{
    /**
     * Handle detection for a given login log.
     */
    public function handle(LoginLog $log): void
    {
        // Only process failed or locked attempts for incident detection
        if ($log->status === 'success') {
            return;
        }

        $this->detectBruteForce($log);
        $this->detectEnumeration($log);
        $this->detectLockLoop($log);
        $this->detectBurst($log);
    }

    /**
     * Rule E: Suspicious burst activity.
     */
    protected function detectBurst(LoginLog $log): void
    {
        $lookbackSeconds = 10;
        $threshold = 5; // 5 attempts in 10 seconds is a burst

        $count = LoginLog::where('ip_address', $log->ip_address)
            ->where('status', '!=', 'success')
            ->where('created_at', '>=', now()->subSeconds($lookbackSeconds))
            ->count();

        if ($count >= $threshold) {
             $this->createOrUpdateIncident([
                'type' => 'burst',
                'source_ip' => $log->ip_address,
                'remarks' => 'Aggressive burst of failed activity detected.',
            ], $log, $count);
        }
    }

    /**
     * Create a new incident or update an existing open one.
     */
    protected function detectBruteForce(LoginLog $log): void
    {
        $lookbackMinutes = 30;
        $threshold = 5;

        // Pattern 1: Target-based (Multiple IPs or one IP targeting one real email)
        if ($log->user_id) {
            $count = LoginLog::where('email', $log->email)
                ->where('status', '!=', 'success')
                ->where('created_at', '>=', now()->subMinutes($lookbackMinutes))
                ->count();

            if ($count >= $threshold) {
                $this->createOrUpdateIncident([
                    'type' => 'brute_force',
                    'target_identifier' => $log->email,
                    'affected_user_id' => $log->user_id,
                ], $log, $count);
            }
        }

        // Pattern 2: Source-based (One IP targeting multiple accounts - Credential Stuffing)
        $ipCount = LoginLog::where('ip_address', $log->ip_address)
            ->where('status', '!=', 'success')
            ->where('created_at', '>=', now()->subMinutes($lookbackMinutes))
            ->count();

        if ($ipCount >= 10) {
            $this->createOrUpdateIncident([
                'type' => 'credential_stuffing',
                'source_ip' => $log->ip_address,
            ], $log, $ipCount);
        }
    }

    /**
     * Rule B: Non-existing account enumeration.
     */
    protected function detectEnumeration(LoginLog $log): void
    {
        if ($log->user_id !== null) return;

        $lookbackMinutes = 30;
        $threshold = 5;

        $count = LoginLog::where('ip_address', $log->ip_address)
            ->whereNull('user_id')
            ->where('created_at', '>=', now()->subMinutes($lookbackMinutes))
            ->count();

        if ($count >= $threshold) {
            $this->createOrUpdateIncident([
                'type' => 'enumeration',
                'source_ip' => $log->ip_address,
                'remarks' => 'Multiple attempts on non-existent accounts detected.',
            ], $log, $count);
        }
    }

    /**
     * Rule D: Repeated account lock trigger.
     */
    protected function detectLockLoop(LoginLog $log): void
    {
        if ($log->status !== 'locked' || !$log->user_id) return;

        $lookbackHours = 24;
        $threshold = 2; // Getting locked twice in 24 hours is highly suspicious

        $count = LoginLog::where('user_id', $log->user_id)
            ->where('status', 'locked')
            ->where('created_at', '>=', now()->subHours($lookbackHours))
            ->count();

        if ($count >= $threshold) {
            $this->createOrUpdateIncident([
                'type' => 'lock_loop',
                'affected_user_id' => $log->user_id,
                'target_identifier' => $log->email,
            ], $log, $count);
        }
    }

    /**
     * Create a new incident or update an existing open one.
     */
    protected function createOrUpdateIncident(array $criteria, LoginLog $log, int $count): void
    {
        // Look for an existing OPEN incident of the same type and identifier/IP
        $incident = SecurityIncident::where('type', $criteria['type'])
            ->where('status', 'open')
            ->where(function ($q) use ($criteria) {
                if (isset($criteria['source_ip'])) {
                    $q->where('source_ip', $criteria['source_ip']);
                }
                if (isset($criteria['target_identifier'])) {
                    $q->where('target_identifier', $criteria['target_identifier']);
                }
            })
            ->where('created_at', '>=', now()->subHours(2)) // Group within a 2-hour window
            ->first();

        $severity = $this->calculateSeverity($criteria['type'], $count);

        if (!$incident) {
            // Find the earliest relevant log to accurately set first_detected_at
            $firstLog = LoginLog::where('status', '!=', 'success')
                ->where('created_at', '>=', now()->subHours(2))
                ->where(function ($q) use ($criteria) {
                    if (isset($criteria['source_ip'])) $q->where('ip_address', $criteria['source_ip']);
                    if (isset($criteria['target_identifier'])) $q->where('email', $criteria['target_identifier']);
                })
                ->orderBy('created_at', 'asc')
                ->first();

            $incident = SecurityIncident::create(array_merge($criteria, [
                'severity' => $severity,
                'status' => 'open',
                'first_detected_at' => $firstLog?->created_at ?? now(),
                'last_detected_at' => now(),
                'logs_count' => $count,
                'source_ip' => $criteria['source_ip'] ?? $log->ip_address,
            ]));
        } else {
            $incident->update([
                'last_detected_at' => now(),
                'logs_count' => $count,
                'severity' => $severity,
            ]);
        }

        // Link the current log to the incident
        $log->update(['security_incident_id' => $incident->id]);

        // Link related logs
        $this->linkRelatedLogs($incident);
    }

    /**
     * Link all logs related to the incident pattern.
     */
    protected function linkRelatedLogs(SecurityIncident $incident): void
    {
        $query = LoginLog::whereNull('security_incident_id');

        if ($incident->type === 'brute_force' || $incident->type === 'lock_loop') {
            $query->where('email', $incident->target_identifier);
        } elseif ($incident->type === 'credential_stuffing' || $incident->type === 'enumeration') {
            $query->where('ip_address', $incident->source_ip);
        } elseif ($incident->type === 'burst') {
            $query->where('ip_address', $incident->source_ip);
        }

        // Link all relevant logs from the same 2-hour window
        $query->where('created_at', '>=', $incident->first_detected_at->subSeconds(1));
        
        $query->update(['security_incident_id' => $incident->id]);
    }

    /**
     * Determine severity based on the volume and type.
     */
    protected function calculateSeverity(string $type, int $count): string
    {
        // Rule: Distribution-based severity
        if ($type === 'credential_stuffing' || $type === 'enumeration') {
            if ($count >= 50) return 'critical';
            if ($count >= 20) return 'high';
            return 'medium';
        }

        if ($type === 'lock_loop') return 'high';

        if ($count >= 20) return 'high';
        if ($count >= 5) return 'medium';

        return 'low';
    }

}
