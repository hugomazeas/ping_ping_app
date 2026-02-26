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

        $game = new Game([
            'player1_id' => $playerId,
            'player2_id' => $opponentId,
            'mode' => $request->input('mode'),
            'player1_score' => 0,
            'player2_score' => 0,
            'started_at' => now(),
            'serve_count' => 0,
        ]);

        // Set initial server based on score (which is 0-0)
        $game->current_server_id = $playerId;
        $game->updateServerBasedOnScore();
        $game->save();

        return redirect()->route('game.show', $game);
    }

    public function show(Game $game)
    {
        $game->load(['player1', 'player2', 'winner']);
        $reverseControls = env('REVERSE_CONTROLS', false);

        return view('game', compact('game', 'reverseControls'));
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

        // Update server based on total score
        $game->updateServerBasedOnScore();

        // Check for win condition
        $winnerId = $game->checkWinCondition();
        $eloChanges = null;
        if ($winnerId) {
            $game->winner_id = $winnerId;
            $game->ended_at = now();

            // Update ELO ratings
            $eloChanges = $this->updateEloRatings($game);
            $game->elo_applied = true;
        }

        $game->save();

        $response = [
            'player1_score' => $game->player1_score,
            'player2_score' => $game->player2_score,
            'winner_id' => $game->winner_id,
            'is_complete' => $game->isComplete(),
            'current_server_id' => $game->current_server_id,
            'serve_number' => $game->serve_number,
        ];

        if ($eloChanges) {
            $response['player1_elo_change'] = $eloChanges['player1_elo_change'];
            $response['player2_elo_change'] = $eloChanges['player2_elo_change'];
            $response['player1_initial_elo'] = $eloChanges['player1_initial_elo'];
            $response['player2_initial_elo'] = $eloChanges['player2_initial_elo'];
        }

        return response()->json($response);
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

        // Update ELO ratings
        $eloChanges = $this->updateEloRatings($game);
        $game->elo_applied = true;

        $game->save();

        return response()->json([
            'winner_id' => $game->winner_id,
            'is_complete' => true,
            'duration' => $game->duration_formatted,
            'player1_elo_change' => $eloChanges['player1_elo_change'],
            'player2_elo_change' => $eloChanges['player2_elo_change'],
            'player1_initial_elo' => $eloChanges['player1_initial_elo'],
            'player2_initial_elo' => $eloChanges['player2_initial_elo'],
        ]);
    }

    /**
     * Update ELO ratings for both players after a game ends
     */
    private function updateEloRatings(Game $game): array
    {
        $player1 = $game->player1;
        $player2 = $game->player2;

        // Capture initial ratings before updating
        $player1InitialElo = $player1->elo_rating;
        $player2InitialElo = $player2->elo_rating;

        // Determine scores (1 for win, 0.5 for draw, 0 for loss)
        if ($game->winner_id === null) {
            // Draw
            $player1Score = 0.5;
            $player2Score = 0.5;
        } elseif ($game->winner_id === $player1->id) {
            // Player 1 wins
            $player1Score = 1.0;
            $player2Score = 0.0;
        } else {
            // Player 2 wins
            $player1Score = 0.0;
            $player2Score = 1.0;
        }

        // Calculate changes before updating
        $player1Change = $player1->calculateEloChange($player2, $player1Score);
        $player2Change = $player2->calculateEloChange($player1, $player2Score);

        // Update ratings
        $player1->updateEloRating($player2, $player1Score);
        $player2->updateEloRating($player1, $player2Score);

        return [
            'player1_elo_change' => $player1Change,
            'player2_elo_change' => $player2Change,
            'player1_initial_elo' => $player1InitialElo,
            'player2_initial_elo' => $player2InitialElo,
        ];
    }

    public function rematch(Game $game)
    {
        $newGame = new Game([
            'player1_id' => $game->player1_id,
            'player2_id' => $game->player2_id,
            'mode' => $game->mode,
            'player1_score' => 0,
            'player2_score' => 0,
            'started_at' => now(),
            'serve_count' => 0,
        ]);

        // Set initial server based on score (which is 0-0)
        $newGame->current_server_id = $game->player1_id;
        $newGame->updateServerBasedOnScore();
        $newGame->save();

        return redirect()->route('game.show', $newGame);
    }
}
