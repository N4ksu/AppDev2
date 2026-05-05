<?php

namespace App\Console\Commands;

use App\Jobs\AssessLoginWithGemini;
use App\Models\LoginLog;
use Illuminate\Console\Command;

class BackfillAiAssessment extends Command
{
    protected $signature = 'ai:backfill {--count=50 : Number of recent events to backfill}';
    protected $description = 'Backfill AI security assessments for recent login events that have not been assessed yet.';

    public function handle(): int
    {
        $count = (int) $this->option('count');

        $logs = LoginLog::whereNull('ai_risk_score')
            ->latest()
            ->take($count)
            ->get();

        if ($logs->isEmpty()) {
            $this->info('No login events need backfilling.');
            return self::SUCCESS;
        }

        $this->info("Dispatching AI assessment for {$logs->count()} login events...");

        $bar = $this->output->createProgressBar($logs->count());
        $bar->start();

        foreach ($logs as $log) {
            AssessLoginWithGemini::dispatch($log->id);
            $bar->advance();
            sleep(3); // Conservative delay to avoid 429
        }

        $bar->finish();
        $this->newLine();
        $this->info('All jobs dispatched. Run `php artisan queue:work` to process them.');

        return self::SUCCESS;
    }
}
