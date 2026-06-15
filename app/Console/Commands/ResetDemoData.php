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
            $demoQuizIds = $demo->quizzes()->pluck('id');

            // Wipe every game played on the demo: sessions hosted by the demo
            // account on any quiz, plus sessions anyone hosted on a demo quiz.
            // Deleting a session cascades its players and player answers.
            GameSession::where('host_id', $demo->id)
                ->orWhereIn('quiz_id', $demoQuizIds)
                ->delete();

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
