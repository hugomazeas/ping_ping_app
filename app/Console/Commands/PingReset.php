<?php

namespace App\Console\Commands;

use App\Models\Game;
use Illuminate\Console\Command;

class PingReset extends Command
{
    protected $signature = 'ping:reset {--force : Skip confirmation}';

    protected $description = 'Clear all games (keeps players)';

    public function handle(): int
    {
        $gameCount = Game::count();

        if ($gameCount === 0) {
            $this->info('No games to delete.');
            return Command::SUCCESS;
        }

        $this->warn("This will delete all {$gameCount} games from the database.");
        $this->line('Players will be kept.');

        if (!$this->option('force')) {
            if (!$this->confirm('Are you sure you want to proceed?')) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        Game::truncate();

        $this->info("Successfully deleted {$gameCount} games.");

        return Command::SUCCESS;
    }
}
