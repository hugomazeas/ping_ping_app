<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Player;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function selectOpponent()
    {
        $playerId = session('player_id');

        if (!$playerId) {
            return redirect()->route('home')->with('error', 'Please enter your name first.');
        }

        $player = Player::find($playerId);

        if (!$player) {
            session()->forget('player_id');
            return redirect()->route('home')->with('error', 'Player not found.');
        }

        $opponents = Player::where('id', '!=', $playerId)->orderBy('name')->get();

        return view('play', compact('player', 'opponents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'opponent_id' => 'nullable|exists:players,id',
            'opponent_name' => 'nullable|string|min:1|max:50',
            'mode' => 'required|in:11,21,freestyle',
        ]);

        $playerId = session('player_id');

        if (!$playerId) {
            return redirect()->route('home')->with('error', 'Please enter your name first.');
        }

        // Get or create opponent
        if ($request->filled('opponent_id')) {
            $opponentId = $request->input('opponent_id');
        } elseif ($request->filled('opponent_name')) {
            $opponent = Player::firstOrCreate(['name' => trim($request->input('opponent_name'))]);
            $opponentId = $opponent->id;
        } else {
            return back()->with('error', 'Please select or enter an opponent.');
        }

        if ($opponentId == $playerId) {
            return back()->with('error', 'You cannot play against yourself.');
        }

        $game = Game::create([
            'player1_id' => $playerId,
            'player2_id' => $opponentId,
            'mode' => $request->input('mode'),
            'player1_score' => 0,
            'player2_score' => 0,
            'started_at' => now(),
        ]);

        return redirect()->route('game.show', $game);
    }

    public function show(Game $game)
    {
        $game->load(['player1', 'player2', 'winner']);

        return view('game', compact('game'));
    }

    public function update(Request $request, Game $game)
    {
        if ($game->isComplete()) {
            return response()->json(['error' => 'Game is already complete'], 400);
        }

        $request->validate([
            'player' => 'required|in:1,2',
            'action' => 'required|in:increment,decrement',
        ]);

        $player = $request->input('player');
        $action = $request->input('action');
        $scoreField = "player{$player}_score";

        if ($action === 'increment') {
            $game->$scoreField++;
        } else {
            $game->$scoreField = max(0, $game->$scoreField - 1);
        }

        // Check for win condition
        $winnerId = $game->checkWinCondition();
        if ($winnerId) {
            $game->winner_id = $winnerId;
            $game->ended_at = now();
        }

        $game->save();

        return response()->json([
            'player1_score' => $game->player1_score,
            'player2_score' => $game->player2_score,
            'winner_id' => $game->winner_id,
            'is_complete' => $game->isComplete(),
        ]);
    }

    public function end(Request $request, Game $game)
    {
        if ($game->isComplete()) {
            return response()->json(['error' => 'Game is already complete'], 400);
        }

        // Determine winner based on current scores
        $winnerId = null;
        if ($game->player1_score > $game->player2_score) {
            $winnerId = $game->player1_id;
        } elseif ($game->player2_score > $game->player1_score) {
            $winnerId = $game->player2_id;
        }
        // If tied, no winner (draw)

        $game->winner_id = $winnerId;
        $game->ended_at = now();
        $game->save();

        return response()->json([
            'winner_id' => $game->winner_id,
            'is_complete' => true,
            'duration' => $game->duration_formatted,
        ]);
    }

    public function rematch(Game $game)
    {
        $newGame = Game::create([
            'player1_id' => $game->player1_id,
            'player2_id' => $game->player2_id,
            'mode' => $game->mode,
            'player1_score' => 0,
            'player2_score' => 0,
            'started_at' => now(),
        ]);

        return redirect()->route('game.show', $newGame);
    }
}
