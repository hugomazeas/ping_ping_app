<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Player;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $playerCount = Player::count();
        $gameCount = Game::count();
        $incompleteCount = Game::whereNull('ended_at')->count();

        return view('settings', compact('playerCount', 'gameCount', 'incompleteCount'));
    }

    public function clearGames()
    {
        Game::truncate();

        return redirect()->route('settings')->with('success', '✅ All games have been deleted! Players preserved.');
    }

    public function clearAll()
    {
        Game::truncate();
        Player::truncate();

        // Clear session
        session()->forget('player_id');

        return redirect()->route('settings')->with('success', '✅ All players and games have been deleted!');
    }

    public function cleanupIncomplete()
    {
        $deleted = Game::whereNull('ended_at')->delete();

        return redirect()->route('settings')->with('success', "✅ Cleaned up {$deleted} incomplete game(s)!");
    }
}
