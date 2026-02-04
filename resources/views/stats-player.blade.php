@extends('layouts.app')

@section('title', $player->name . ' - Hall of Fame')

@section('content')
<div>
    <!-- Header -->
    <div class="flex justify-between items-start mb-10">
        <div>
            <a href="{{ route('stats') }}" class="text-white/60 hover:text-white transition-colors mb-4 inline-block">
                ← Back to Hall of Fame
            </a>
            <div class="flex items-center gap-4">
                <div class="text-6xl">
                    @if($player->win_rate >= 70) 🏆
                    @elseif($player->win_rate >= 50) ⭐
                    @elseif($player->win_rate >= 30) 🎯
                    @else 🌱
                    @endif
                </div>
                <div>
                    <h1 class="font-display text-5xl text-pink-400 neon-text">{{ strtoupper($player->name) }}</h1>
                    <p class="text-white/60 mt-1">
                        @if($player->total_games == 0) Rookie - No battles yet
                        @elseif($player->win_rate >= 70) Legendary Champion
                        @elseif($player->win_rate >= 50) Rising Star
                        @elseif($player->win_rate >= 30) Determined Fighter
                        @else Training Hard
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Overview Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white/10 backdrop-blur rounded-3xl p-6 text-center funky-border card-hover">
            <div class="text-4xl mb-2">🎮</div>
            <div class="text-4xl font-bold text-white">{{ $player->total_games }}</div>
            <div class="text-white/60">Total Games</div>
        </div>
        <div class="bg-gradient-to-br from-green-500/20 to-emerald-600/20 backdrop-blur border-2 border-green-500 rounded-3xl p-6 text-center card-hover">
            <div class="text-4xl mb-2">🏆</div>
            <div class="text-4xl font-bold text-green-400">{{ $player->total_wins }}</div>
            <div class="text-white/60">Wins</div>
        </div>
        <div class="bg-gradient-to-br from-red-500/20 to-rose-600/20 backdrop-blur border-2 border-red-500 rounded-3xl p-6 text-center card-hover">
            <div class="text-4xl mb-2">💔</div>
            <div class="text-4xl font-bold text-red-400">{{ $player->total_losses }}</div>
            <div class="text-white/60">Losses</div>
        </div>
        <div class="bg-gradient-to-br from-yellow-500/20 to-orange-600/20 backdrop-blur border-2 border-yellow-500 rounded-3xl p-6 text-center card-hover">
            <div class="text-4xl mb-2">📊</div>
            <div class="text-4xl font-bold text-yellow-400">{{ $player->win_rate }}%</div>
            <div class="text-white/60">Win Rate</div>
        </div>
    </div>

    <!-- Additional Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
        <div class="bg-white/10 backdrop-blur rounded-3xl p-6 funky-border">
            <h3 class="font-display text-xl text-cyan-400 mb-4">⏱️ AVG GAME TIME</h3>
            @if($player->average_game_duration)
                @php
                    $mins = floor($player->average_game_duration / 60);
                    $secs = $player->average_game_duration % 60;
                @endphp
                <div class="text-4xl font-bold">{{ sprintf('%02d:%02d', $mins, $secs) }}</div>
            @else
                <div class="text-white/40">No completed games</div>
            @endif
        </div>
        <div class="bg-white/10 backdrop-blur rounded-3xl p-6 funky-border">
            <h3 class="font-display text-xl text-purple-400 mb-4">🔥 CURRENT STREAK</h3>
            @php $streak = $player->current_streak; @endphp
            @if($streak['type'])
                <div class="flex items-center gap-3">
                    <span class="text-4xl">
                        @if($streak['type'] === 'win') 🚀 @else 😤 @endif
                    </span>
                    <span class="text-4xl font-bold {{ $streak['type'] === 'win' ? 'text-green-400' : 'text-red-400' }}">
                        {{ $streak['count'] }} {{ $streak['type'] === 'win' ? 'Win' : 'Loss' }}{{ $streak['count'] > 1 ? 's' : '' }}
                    </span>
                </div>
            @else
                <div class="text-white/40">No games yet</div>
            @endif
        </div>
    </div>

    <!-- Head to Head Records -->
    @if(count($headToHeadRecords) > 0)
        <div class="bg-white/10 backdrop-blur rounded-3xl p-6 mb-8 funky-border">
            <h2 class="font-display text-2xl text-yellow-400 mb-6">⚔️ HEAD-TO-HEAD</h2>
            <div class="space-y-3">
                @foreach($headToHeadRecords as $record)
                    <a
                        href="{{ route('stats.player', $record['opponent']) }}"
                        class="flex items-center justify-between bg-white/5 hover:bg-white/10 rounded-2xl p-4 transition-all card-hover"
                    >
                        <div class="flex items-center gap-3">
                            <div class="text-2xl">
                                @if($record['wins'] > $record['losses']) 💪
                                @elseif($record['wins'] < $record['losses']) 😓
                                @else 🤝
                                @endif
                            </div>
                            <span class="font-bold text-lg">{{ $record['opponent']->name }}</span>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="text-green-400 font-bold">{{ $record['wins'] }}W</span>
                            <span class="text-white/40">-</span>
                            <span class="text-red-400 font-bold">{{ $record['losses'] }}L</span>
                            <span class="text-white/40 text-sm">({{ $record['total'] }} games)</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Game History -->
    <div class="bg-white/10 backdrop-blur rounded-3xl p-6 funky-border">
        <h2 class="font-display text-2xl text-cyan-400 mb-6">📜 BATTLE HISTORY</h2>

        @if($games->isEmpty())
            <div class="text-center py-12">
                <div class="text-6xl mb-4">🆕</div>
                <p class="text-xl text-white/60">No battles recorded yet!</p>
            </div>
        @else
            <div class="space-y-3">
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
                                <span class="text-2xl">
                                    @if($won) 🏆 @elseif($game->winner_id) 💔 @else 🤝 @endif
                                </span>
                                <span class="text-3xl font-bold {{ $won ? 'text-green-400' : 'text-white' }}">{{ $playerScore }}</span>
                                <span class="text-white/40 text-xl">vs</span>
                                <span class="text-3xl font-bold text-white">{{ $opponentScore }}</span>
                                <a href="{{ route('stats.player', $opponent) }}" class="font-semibold hover:text-cyan-400 transition-colors">
                                    {{ $opponent->name }}
                                </a>
                            </div>
                            <div class="text-sm text-white/40 mt-2 ml-10">
                                {{ $game->ended_at->diffForHumans() }} · ⏱️ {{ $game->duration_formatted }}
                            </div>
                        </div>
                        <div class="px-4 py-2 rounded-full font-bold text-sm
                            {{ $won ? 'bg-green-500/20 text-green-400' : ($game->winner_id ? 'bg-red-500/20 text-red-400' : 'bg-yellow-500/20 text-yellow-400') }}">
                            {{ $won ? 'WIN' : ($game->winner_id ? 'LOSS' : 'DRAW') }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
