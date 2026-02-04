<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Models\Player;
use Illuminate\Console\Command;

class PingSeedDemo extends Command
{
    protected $signature = 'ping:seed-demo';

    protected $description = 'Seed realistic demo data for testing';

    public function handle(): int
    {
        $this->info('Seeding demo data...');

        // Create demo players
        $playerNames = [
            'Alex',
            'Jordan',
            'Taylor',
            'Casey',
            'Morgan',
            'Riley',
            'Jamie',
            'Sam',
        ];

        $players = [];
        foreach ($playerNames as $name) {
            $players[] = Player::firstOrCreate(['name' => $name]);
        }

        $this->line("Created " . count($players) . " players");

        // Create demo games
        $modes = ['11', '21', 'freestyle'];
        $gamesCreated = 0;

        // Generate games over the past 30 days
        for ($i = 0; $i < 50; $i++) {
            // Pick two random different players
            $player1Index = array_rand($players);
            do {
                $player2Index = array_rand($players);
            } while ($player2Index === $player1Index);

            $player1 = $players[$player1Index];
            $player2 = $players[$player2Index];

            $mode = $modes[array_rand($modes)];

            // Generate realistic scores
            $winScore = $mode === 'freestyle' ? rand(7, 21) : (int) $mode;

            // Randomly determine winner
            $player1Wins = rand(0, 1) === 1;

            if ($player1Wins) {
                $player1Score = $winScore;
                // Loser score is somewhere below winning score
                $player2Score = rand(max(0, $winScore - 10), $winScore - 2);
            } else {
                $player2Score = $winScore;
                $player1Score = rand(max(0, $winScore - 10), $winScore - 2);
            }

            // Sometimes make it a close game (deuce scenarios)
            if (rand(0, 4) === 0 && $mode !== 'freestyle') {
                $winScore = (int) $mode;
                if ($player1Wins) {
                    $player1Score = $winScore + rand(0, 3);
                    $player2Score = $player1Score - 2;
                } else {
                    $player2Score = $winScore + rand(0, 3);
                    $player1Score = $player2Score - 2;
                }
            }

            $winnerId = $player1Wins ? $player1->id : $player2->id;

            // Random date in the past 30 days
            $daysAgo = rand(0, 30);
            $hoursAgo = rand(0, 23);
            $startedAt = now()->subDays($daysAgo)->subHours($hoursAgo);

            // Game duration between 3-15 minutes
            $durationSeconds = rand(180, 900);
            $endedAt = $startedAt->copy()->addSeconds($durationSeconds);

            Game::create([
                'player1_id' => $player1->id,
                'player2_id' => $player2->id,
                'player1_score' => $player1Score,
                'player2_score' => $player2Score,
                'winner_id' => $winnerId,
                'mode' => $mode,
                'started_at' => $startedAt,
                'ended_at' => $endedAt,
            ]);

            $gamesCreated++;
        }

        $this->line("Created {$gamesCreated} demo games");
        $this->info('Demo data seeded successfully!');
        $this->line('');
        $this->line('Run `php artisan ping:stats` to see the stats.');

        return Command::SUCCESS;
    }
}
