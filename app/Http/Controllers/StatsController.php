<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Player;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function index()
    {
        $recentGames = Game::with(['player1', 'player2', 'winner'])
            ->whereNotNull('ended_at')
            ->orderBy('ended_at', 'desc')
            ->limit(10)
            ->get();

        $players = Player::all();

        return view('stats', compact('recentGames', 'players'));
    }

    public function player(Player $player)
    {
        $games = $player->completedGames()
            ->with(['player1', 'player2', 'winner'])
            ->orderBy('ended_at', 'desc')
            ->get();

        // Get head-to-head records with each opponent
        $opponents = Player::where('id', '!=', $player->id)->get();
        $headToHeadRecords = [];

        foreach ($opponents as $opponent) {
            $record = $player->headToHead($opponent);
            if ($record['total'] > 0) {
                $headToHeadRecords[] = [
                    'opponent' => $opponent,
                    'wins' => $record['wins'],
                    'losses' => $record['losses'],
                    'total' => $record['total'],
                ];
            }
        }

        // Sort by total games played
        usort($headToHeadRecords, fn($a, $b) => $b['total'] - $a['total']);

        return view('stats-player', compact('player', 'games', 'headToHeadRecords'));
    }

    public function leaderboard()
    {
        $players = Player::all()->map(function ($player) {
            return [
                'id' => $player->id,
                'name' => $player->name,
                'total_games' => $player->total_games,
                'wins' => $player->total_wins,
                'losses' => $player->total_losses,
                'win_rate' => $player->win_rate,
            ];
        });

        // Filter to players with at least 1 game, then sort by win rate
        $leaderboard = $players
            ->filter(fn($p) => $p['total_games'] >= 1)
            ->sortByDesc('win_rate')
            ->values();

        return response()->json($leaderboard);
    }
}
