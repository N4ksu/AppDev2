<?php

namespace App\Events;

use App\Models\SecurityInsight;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SecurityInsightCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public SecurityInsight $insight)
    {
    }

    public function broadcastOn(): array
    {
        return [new Channel('security-insights')];
    }

    public function broadcastAs(): string
    {
        return 'security.insight.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->insight->id,
            'login_log_id' => $this->insight->login_log_id,
            'severity' => $this->insight->severity,
            'final_action' => $this->insight->final_action,
            'provider_status' => $this->insight->provider_status,
            'created_at' => optional($this->insight->created_at)->toDateTimeString(),
        ];
    }
}
