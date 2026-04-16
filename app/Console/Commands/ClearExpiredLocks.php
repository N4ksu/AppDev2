<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('security:clear-expired-locks')]
#[Description('Clear all temporary account locks that have expired.')]
class ClearExpiredLocks extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = \App\Models\User::where('is_locked', true)
            ->whereNotNull('locked_until')
            ->where('locked_until', '<=', now())
            ->update([
                'is_locked' => false,
                'failed_attempts' => 0,
                'locked_until' => null,
            ]);

        $this->info("Successfully cleared {$count} expired account locks.");
    }
}
