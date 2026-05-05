<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\BehaviorSample;
use App\Models\User;
use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\AnomalyDetectors\IsolationForest;
use Rubix\ML\Persisters\Filesystem;
use Rubix\ML\Serializers\Native;

class TrainBehaviorModel extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'train:behavior {user_id? : Optional user ID to train a model for a specific user}';

    /**
     * The console command description.
     */
    protected $description = 'Train per-user behavioral anomaly detection models using Rubix ML Isolation Forest.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Ensure the models directory exists
        Storage::makeDirectory('behavior_models');

        $userId = $this->argument('user_id');

        if ($userId) {
            $users = User::where('id', $userId)->get();
        } else {
            // All users who have samples in the behavior_samples table
            $userIds = BehaviorSample::select('user_id')->distinct()->pluck('user_id');
            $users = User::whereIn('id', $userIds)->get();
        }

        $trained = 0;
        $skipped = 0;

        foreach ($users as $user) {
            $samples = BehaviorSample::where('user_id', $user->id)->get();

            if ($samples->count() < 10) {
                $this->warn("User {$user->id} ({$user->email}): skipped — only {$samples->count()} sample(s), need at least 10.");
                $skipped++;
                continue;
            }

            // Build feature matrix: [[typing_speed, mouse_velocity], ...]
            $features = $samples->map(function ($sample) {
                // Add micro-jitter to prevent DivisionByZero if all elements are identical
                $jitter1 = (mt_rand(-100, 100) / 100000);
                $jitter2 = (mt_rand(-100, 100) / 100000);
                return [
                    (float) $sample->typing_speed + $jitter1,
                    (float) $sample->mouse_velocity + $jitter2
                ];
            })->values()->toArray();

            $dataset = new Unlabeled($features);

            $estimator = new IsolationForest(100, 0.1);
            $estimator->train($dataset);

            $modelPath = storage_path("app/behavior_models/{$user->id}.model");
            $serializer = new Native();
            $encoding   = $serializer->serialize($estimator);
            $persister  = new Filesystem($modelPath);
            $persister->save($encoding);

            $this->info("User {$user->id} ({$user->email}): model trained on {$samples->count()} samples → {$modelPath}");
            $trained++;
        }

        $this->info("Done. Trained: {$trained}, Skipped (insufficient samples): {$skipped}.");

        return self::SUCCESS;
    }
}
