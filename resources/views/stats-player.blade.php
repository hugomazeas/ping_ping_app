@extends('layouts.app')

@section('title', $player->name . ' - Leaderboard')

@section('content')
<div class="h-full flex flex-col" x-data="{}" @keydown.window.backspace="window.location.href = '{{ route('home') }}'">
    <!-- Header -->
    <div class="mb-4 flex items-center justify-between">
        <a href="{{ route('home') }}" class="text-2xl text-white/60 hover:text-white transition-colors inline-block focus:outline-none focus:ring-4 focus:ring-pink-500 rounded px-2" autofocus>
            ← Back to Home (Press <kbd class="px-2 py-1 bg-white/20 rounded">Backspace</kbd>)
        </a>
        <div class="flex items-center gap-6 flex-1">
            <div class="text-7xl">
                @if($player->win_rate >= 70) 🏆
                @elseif($player->win_rate >= 50) ⭐
                @elseif($player->win_rate >= 30) 🎯
                @else 🌱
                @endif
            </div>
            <div>
                <h1 class="font-display text-6xl text-pink-400 neon-text">{{ strtoupper($player->name) }}</h1>
                <div class="flex items-center gap-4 mt-2">
                    <p class="text-2xl text-white/60">
                        @if($player->total_games == 0) Rookie
                        @elseif($player->win_rate >= 70) Legendary Champion
                        @elseif($player->win_rate >= 50) Rising Star
                        @elseif($player->win_rate >= 30) Determined Fighter
                        @else Training Hard
                        @endif
                    </p>
                    <div class="px-4 py-2 bg-yellow-400/20 border-2 border-yellow-400 rounded-xl">
                        <span class="text-3xl font-bold text-yellow-400">⭐ {{ $player->elo_rating }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Keyboard Shortcuts Help -->
        <div class="bg-white/10 backdrop-blur rounded-2xl px-6 py-3 funky-border">
            <div class="text-lg font-bold text-yellow-400 mb-1">⌨️ KEYBOARD</div>
            <div class="text-base text-white/80">
                <div><kbd class="px-2 py-1 bg-white/20 rounded">Backspace</kbd> Back to Home</div>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-7 gap-4 mb-4">
        <div class="bg-gradient-to-br from-yellow-500/20 to-orange-600/20 backdrop-blur border-2 border-yellow-400 rounded-2xl p-4 text-center card-hover">
            <div class="text-4xl mb-1">⭐</div>
            <div class="text-4xl font-bold text-yellow-400">{{ $player->elo_rating }}</div>
            <div class="text-sm text-white/60">ELO Rating</div>
        </div>
        <div class="bg-white/10 backdrop-blur rounded-2xl p-4 text-center funky-border card-hover">
            <div class="text-4xl mb-1">🎮</div>
            <div class="text-4xl font-bold text-white">{{ $player->total_games }}</div>
            <div class="text-sm text-white/60">Games</div>
        </div>
        <div class="bg-gradient-to-br from-green-500/20 to-emerald-600/20 backdrop-blur border-2 border-green-500 rounded-2xl p-4 text-center card-hover">
            <div class="text-4xl mb-1">🏆</div>
            <div class="text-4xl font-bold text-green-400">{{ $player->total_wins }}</div>
            <div class="text-sm text-white/60">Wins</div>
        </div>
        <div class="bg-gradient-to-br from-red-500/20 to-rose-600/20 backdrop-blur border-2 border-red-500 rounded-2xl p-4 text-center card-hover">
            <div class="text-4xl mb-1">💔</div>
            <div class="text-4xl font-bold text-red-400">{{ $player->total_losses }}</div>
            <div class="text-sm text-white/60">Losses</div>
        </div>
        <div class="bg-gradient-to-br from-purple-500/20 to-pink-600/20 backdrop-blur border-2 border-purple-500 rounded-2xl p-4 text-center card-hover">
            <div class="text-4xl mb-1">📊</div>
            <div class="text-4xl font-bold text-purple-400">{{ $player->win_rate }}%</div>
            <div class="text-sm text-white/60">Win Rate</div>
        </div>
        <div class="bg-white/10 backdrop-blur rounded-2xl p-4 text-center funky-border">
            <div class="text-sm text-cyan-400 mb-1">⏱️ AVG TIME</div>
            @if($player->average_game_duration)
                @php
                    $mins = floor($player->average_game_duration / 60);
                    $secs = $player->average_game_duration % 60;
                @endphp
                <div class="text-3xl font-bold">{{ sprintf('%02d:%02d', $mins, $secs) }}</div>
            @else
                <div class="text-lg text-white/40">--:--</div>
            @endif
        </div>
        <div class="bg-white/10 backdrop-blur rounded-2xl p-4 text-center funky-border">
            <div class="text-sm text-purple-400 mb-1">🔥 STREAK</div>
            @php $streak = $player->current_streak; @endphp
            @if($streak['type'])
                <div class="text-3xl font-bold {{ $streak['type'] === 'win' ? 'text-green-400' : 'text-red-400' }}">
                    {{ $streak['count'] }} {{ $streak['type'] === 'win' ? 'W' : 'L' }}
                </div>
            @else
                <div class="text-lg text-white/40">--</div>
            @endif
        </div>
    </div>

    <!-- Content Grid: Head-to-Head & Game History -->
    <div class="flex-1 grid grid-cols-2 gap-4 overflow-hidden">
        <!-- Head to Head Records -->
        <div class="bg-white/10 backdrop-blur rounded-2xl p-6 funky-border flex flex-col">
            <h2 class="font-display text-3xl text-yellow-400 mb-4">⚔️ HEAD-TO-HEAD</h2>
            @if(count($headToHeadRecords) > 0)
                <div class="flex-1 overflow-y-auto space-y-3 pr-2 focus:outline-none focus:ring-4 focus:ring-cyan-500 rounded-2xl" tabindex="0">
                    @foreach($headToHeadRecords as $record)
                        <a
                            href="{{ route('stats.player', $record['opponent']) }}"
                            class="flex items-center justify-between bg-white/5 hover:bg-white/10 rounded-2xl p-4 transition-all card-hover focus:outline-none focus:ring-4 focus:ring-cyan-500"
                        >
                            <div class="flex items-center gap-3">
                                <div class="text-3xl">
                                    @if($record['wins'] > $record['losses']) 💪
                                    @elseif($record['wins'] < $record['losses']) 😓
                                    @else 🤝
                                    @endif
                                </div>
                                <span class="font-bold text-2xl">{{ $record['opponent']->name }}</span>
                            </div>
                            <div class="flex items-center gap-3 text-xl">
                                <span class="text-green-400 font-bold">{{ $record['wins'] }}W</span>
                                <span class="text-white/40">-</span>
                                <span class="text-red-400 font-bold">{{ $record['losses'] }}L</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="flex-1 flex items-center justify-center text-2xl text-white/40">
                    No head-to-head yet
                </div>
            @endif
        </div>

        <!-- Game History -->
        <div class="bg-white/10 backdrop-blur rounded-2xl p-6 funky-border flex flex-col">
            <h2 class="font-display text-3xl text-cyan-400 mb-4">📜 BATTLE HISTORY</h2>

            @if($games->isEmpty())
                <div class="flex-1 flex items-center justify-center">
                    <div class="text-center">
                        <div class="text-7xl mb-3">🆕</div>
                        <p class="text-2xl text-white/60">No battles yet!</p>
                    </div>
                </div>
            @else
                <div class="flex-1 overflow-y-auto space-y-3 pr-2 focus:outline-none focus:ring-4 focus:ring-cyan-500 rounded-2xl" tabindex="0">
                    @foreach($games as $game)
                        @php
                            $isPlayer1 = $game->player1_id === $player->id;
                            $opponent = $isPlayer1 ? $game->player2 : $game->player1;
                            $playerScore = $isPlayer1 ? $game->player1_score : $game->player2_score;
                            $opponentScore = $isPlayer1 ? $game->player2_score : $game->player1_score;
                            $won = $game->winner_id === $player->id;
                        @endphp
                        <div class="flex items-center justify-between bg-white/5 rounded-2xl p-4
                            {{ $won ? 'border-l-4 border-green-500' : ($game->winner_id ? 'border-l-4 border-red-500' : 'border-l-4 border-yellow-500') }}">
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    <span class="text-3xl">
                                        @if($won) 🏆 @elseif($game->winner_id) 💔 @else 🤝 @endif
                                    </span>
                                    <span class="text-3xl font-bold {{ $won ? 'text-green-400' : 'text-white' }}">{{ $playerScore }}</span>
                                    <span class="text-white/40 text-2xl">vs</span>
                                    <span class="text-3xl font-bold text-white">{{ $opponentScore }}</span>
                                    <a href="{{ route('stats.player', $opponent) }}" class="font-semibold text-xl hover:text-cyan-400 transition-colors truncate focus:outline-none focus:ring-2 focus:ring-cyan-500 rounded px-1">
                                        {{ $opponent->name }}
                                    </a>
                                </div>
                                @if($game->ended_at)
                                    <div class="text-sm text-white/40 mt-1 ml-10">
                                        {{ $game->ended_at->diffForHumans() }} · {{ $game->duration_formatted }}
                                    </div>
                                @else
                                    <div class="text-sm text-orange-400 mt-1 ml-10">
                                        ⚠️ Game in progress
                                    </div>
                                @endif
                            </div>
                            <div class="px-4 py-2 rounded-full font-bold text-lg
                                {{ $won ? 'bg-green-500/20 text-green-400' : ($game->winner_id ? 'bg-red-500/20 text-red-400' : 'bg-yellow-500/20 text-yellow-400') }}">
                                {{ $won ? 'WIN' : ($game->winner_id ? 'LOSS' : 'DRAW') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
