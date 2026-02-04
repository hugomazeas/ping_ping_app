<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $players = Player::orderBy('name')->get();
        return view('home', compact('players'));
    }

    public function setPlayer(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:1|max:50',
        ]);

        $name = trim($request->input('name'));

        $player = Player::firstOrCreate(['name' => $name]);

        session(['player_id' => $player->id]);

        return redirect()->route('play');
    }

    public function playersAutocomplete(Request $request)
    {
        $query = $request->input('q', '');

        $players = Player::where('name', 'like', "%{$query}%")
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name']);

        return response()->json($players);
    }
}
