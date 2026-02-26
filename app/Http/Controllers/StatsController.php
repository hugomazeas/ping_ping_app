<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Player;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function player(Player $player)
    {
        $games = $player->completedGames()
            ->with(['player1', 'player2', 'winner'])
            ->whereNotNull('ended_at')  // Extra safeguard
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
                'total_wins' => $player->total_wins,
                'total_losses' => $player->total_losses,
                'win_rate' => $player->win_rate,
                'elo_rating' => $player->elo_rating,
            ];
        });

        // Filter to players with at least 1 game, then sort by ELO rating
        $leaderboard = $players
            ->filter(fn($p) => $p['total_games'] >= 1)
            ->sortByDesc('elo_rating')
            ->values();

        return response()->json($leaderboard);
    }
}
