<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Models\Player;
use Illuminate\Console\Command;

class PingStats extends Command
{
    protected $signature = 'ping:stats';

    protected $description = 'Show quick ping pong stats in the terminal';

    public function handle(): int
    {
        $totalPlayers = Player::count();
        $totalGames = Game::whereNotNull('ended_at')->count();
        $activeGames = Game::whereNull('ended_at')->count();

        $this->info('');
        $this->info('=== Ping Pong Tracker Stats ===');
        $this->info('');
        $this->line("Total Players: {$totalPlayers}");
        $this->line("Completed Games: {$totalGames}");
        $this->line("Active Games: {$activeGames}");
        $this->info('');

        if ($totalGames > 0) {
            $this->info('--- Top 5 Players (by win rate) ---');

            $players = Player::all()
                ->filter(fn($p) => $p->total_games >= 1)
                ->sortByDesc(fn($p) => $p->win_rate)
                ->take(5);

            if ($players->isEmpty()) {
                $this->line('No players with games yet');
            } else {
                $tableData = $players->map(fn($p) => [
                    $p->name,
                    $p->total_wins . 'W - ' . $p->total_losses . 'L',
                    $p->win_rate . '%',
                ])->toArray();

                $this->table(['Player', 'Record', 'Win Rate'], $tableData);
            }

            $this->info('');
            $this->info('--- Last 5 Games ---');

            $recentGames = Game::with(['player1', 'player2', 'winner'])
                ->whereNotNull('ended_at')
                ->orderBy('ended_at', 'desc')
                ->limit(5)
                ->get();

            $gameData = $recentGames->map(function ($game) {
                $p1 = $game->player1->name;
                $p2 = $game->player2->name;
                $winner = $game->winner ? $game->winner->name : 'Draw';

                return [
                    "{$p1} vs {$p2}",
                    "{$game->player1_score} - {$game->player2_score}",
                    $winner,
                    $game->ended_at->diffForHumans(),
                ];
            })->toArray();

            $this->table(['Match', 'Score', 'Winner', 'When'], $gameData);
        }

        $this->info('');

        return Command::SUCCESS;
    }
}
