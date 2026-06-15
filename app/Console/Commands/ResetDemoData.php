<?php

namespace App\Console\Commands;

use App\Models\GameSession;
use App\Models\User;
use Database\Seeders\SampleQuizSeeder;
use Illuminate\Console\Command;

class ResetDemoData extends Command
{
    protected $signature = 'demo:reset';

    protected $description = 'Reset the shared demo account (quizzes, games, AI settings) back to a clean seeded state';

    public function handle(): int
    {
        $demo = User::where('email', SampleQuizSeeder::DEMO_EMAIL)->first();

        if ($demo) {
            // Sessions the demo user hosted on other people's quizzes
            // (sessions on demo-owned quizzes cascade when the seeder wipes them).
            GameSession::where('host_id', $demo->id)->delete();
            $demo->llmSetting()->delete();
        }

        // Seeder is idempotent: it wipes existing demo quizzes, then recreates them.
        $this->callSilent('db:seed', [
            '--class' => SampleQuizSeeder::class,
            '--force' => true,
        ]);

        $this->info('Demo data reset.');

        return self::SUCCESS;
    }
}
