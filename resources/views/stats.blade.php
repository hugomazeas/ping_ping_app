@extends('layouts.app')

@section('title', 'Hall of Fame - Ping Pong Tracker')

@section('content')
<div x-data="statsPage()" x-init="init()">
    <!-- Header -->
    <div class="flex justify-between items-center mb-10">
        <div>
            <h1 class="font-display text-5xl rainbow-text">HALL OF FAME</h1>
            <p class="text-white/60 mt-2">Where legends are born 🌟</p>
        </div>
        <a href="{{ route('home') }}" class="px-6 py-3 bg-white/10 backdrop-blur rounded-full font-bold hover:bg-white/20 transition-all funky-border">
            🏠 Lobby
        </a>
    </div>

    <!-- Leaderboard -->
    <div class="bg-white/10 backdrop-blur rounded-3xl p-6 mb-8 funky-border">
        <h2 class="font-display text-3xl text-yellow-400 mb-6">🏆 LEADERBOARD</h2>

        <div x-show="leaderboard.length === 0" class="text-center py-12">
            <div class="text-6xl mb-4">🎮</div>
            <p class="text-xl text-white/60">No battles yet! Start playing!</p>
        </div>

        <div x-show="leaderboard.length > 0" class="space-y-3">
            <template x-for="(player, index) in leaderboard" :key="player.id">
                <a
                    :href="'/stats/' + player.id"
                    class="flex items-center justify-between bg-white/5 hover:bg-white/10 rounded-2xl p-4 transition-all card-hover"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 rounded-full flex items-center justify-center font-display text-xl"
                            :class="{
                                'bg-gradient-to-br from-yellow-400 to-orange-500 text-black': index === 0,
                                'bg-gradient-to-br from-gray-300 to-gray-400 text-black': index === 1,
                                'bg-gradient-to-br from-orange-600 to-orange-700 text-white': index === 2,
                                'bg-white/20 text-white': index > 2
                            }"
                        >
                            <span x-show="index === 0">👑</span>
                            <span x-show="index === 1">🥈</span>
                            <span x-show="index === 2">🥉</span>
                            <span x-show="index > 2" x-text="index + 1"></span>
                        </div>
                        <div>
                            <div class="font-bold text-lg" x-text="player.name"></div>
                            <div class="text-sm text-white/60">
                                <span class="text-green-400" x-text="player.wins + 'W'"></span>
                                <span class="mx-1">·</span>
                                <span class="text-red-400" x-text="player.losses + 'L'"></span>
                                <span class="mx-1">·</span>
                                <span x-text="player.total_games + ' games'"></span>
                            </div>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-green-400">
                        <span x-text="player.win_rate + '%'"></span>
                    </div>
                </a>
            </template>
        </div>
    </div>

    <!-- Recent Games -->
    <div class="bg-white/10 backdrop-blur rounded-3xl p-6 mb-8 funky-border">
        <h2 class="font-display text-3xl text-cyan-400 mb-6">⚡ RECENT BATTLES</h2>

        @if($recentGames->isEmpty())
            <div class="text-center py-12">
                <div class="text-6xl mb-4">🦗</div>
                <p class="text-xl text-white/60">No battles recorded yet!</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($recentGames as $game)
                    <div class="flex items-center justify-between bg-white/5 rounded-2xl p-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <a href="{{ route('stats.player', $game->player1) }}" class="font-bold hover:text-pink-400 transition-colors {{ $game->winner_id === $game->player1_id ? 'text-pink-400' : 'text-white' }}">
                                    {{ $game->player1->name }}
                                    @if($game->winner_id === $game->player1_id) 🏆 @endif
                                </a>
                                <span class="text-2xl font-bold text-white">{{ $game->player1_score }}</span>
                                <span class="text-white/40 text-xl">vs</span>
                                <span class="text-2xl font-bold text-white">{{ $game->player2_score }}</span>
                                <a href="{{ route('stats.player', $game->player2) }}" class="font-bold hover:text-cyan-400 transition-colors {{ $game->winner_id === $game->player2_id ? 'text-cyan-400' : 'text-white' }}">
                                    @if($game->winner_id === $game->player2_id) 🏆 @endif
                                    {{ $game->player2->name }}
                                </a>
                            </div>
                            <div class="text-sm text-white/40 mt-2">
                                {{ $game->ended_at->diffForHumans() }} · ⏱️ {{ $game->duration_formatted }}
                            </div>
                        </div>
                        <div class="px-3 py-1 rounded-full text-sm font-semibold
                            @if($game->mode === '11') bg-green-500/20 text-green-400
                            @elseif($game->mode === '21') bg-blue-500/20 text-blue-400
                            @else bg-purple-500/20 text-purple-400
                            @endif">
                            @if($game->mode === 'freestyle')
                                🎪 Free
                            @else
                                🎯 {{ $game->mode }}
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- All Players -->
    <div class="bg-white/10 backdrop-blur rounded-3xl p-6 funky-border">
        <h2 class="font-display text-3xl text-pink-400 mb-6">👥 ALL FIGHTERS</h2>

        @if($players->isEmpty())
            <div class="text-center py-12">
                <div class="text-6xl mb-4">👻</div>
                <p class="text-xl text-white/60">No warriors have joined yet!</p>
            </div>
        @else
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                @foreach($players->sortBy('name') as $index => $player)
                    <a
                        href="{{ route('stats.player', $player) }}"
                        class="bg-white/5 hover:bg-white/10 rounded-2xl p-4 transition-all card-hover text-center"
                    >
                        <div class="text-2xl mb-2">{{ ['🎯', '🔥', '⚡', '🌟', '💫', '🎪', '🎨', '🎭'][$index % 8] }}</div>
                        <div class="font-bold truncate">{{ $player->name }}</div>
                        <div class="text-sm text-white/40">{{ $player->total_games }} games</div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>

<script>
function statsPage() {
    return {
        leaderboard: [],

        async init() {
            try {
                const response = await fetch('/api/leaderboard');
                this.leaderboard = await response.json();
            } catch (e) {
                console.error('Failed to load leaderboard:', e);
            }
        }
    }
}
</script>
@endsection
