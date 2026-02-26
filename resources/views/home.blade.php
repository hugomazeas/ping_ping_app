@extends('layouts.app')

@section('title', 'Ping Pong Tracker')

@section('content')
<div x-data="homeKeyboard()" @keydown.window="handleKeydown($event)" class="flex flex-col h-full">
    <!-- Header Section - Compact -->
    <div class="flex items-center justify-between mb-4">
        <h1 class="font-display text-8xl rainbow-text">
            🏓 PING PONG TRACKER
        </h1>

        <div class="flex items-center gap-4">
            <!-- Settings Link -->
            <a href="{{ route('settings') }}" class="bg-white/10 backdrop-blur rounded-2xl px-6 py-3 funky-border hover:bg-white/20 transition-all card-hover focus:outline-none focus:ring-4 focus:ring-yellow-400">
                <div class="text-4xl">⚙️</div>
            </a>

            <!-- Keyboard Shortcuts Help -->
            <div class="bg-white/10 backdrop-blur rounded-2xl px-6 py-3 funky-border">
                <div class="text-lg font-bold text-yellow-400 mb-1">⌨️ KEYBOARD SHORTCUTS</div>
                <div class="text-base text-white/80 space-y-1">
                    <div><kbd class="px-2 py-1 bg-white/20 rounded">↑↓</kbd> Navigate Players</div>
                    <div><kbd class="px-2 py-1 bg-white/20 rounded">Enter</kbd> Select Player</div>
                    <div><kbd class="px-2 py-1 bg-white/20 rounded">Tab</kbd> Focus Input</div>
                </div>
            </div>
        </div>
    </div>

    <div class="flex-1 grid grid-cols-2 gap-8">
        <!-- Left Column: Players -->
        <div class="flex flex-col">
            <!-- New player input -->
            <form action="{{ route('players.store') }}" method="POST" class="mb-4" id="newPlayerForm">
                @csrf
                <div class="flex gap-4">
                    <input
                        id="newPlayerInput"
                        type="text"
                        name="name"
                        placeholder="🎮 New challenger..."
                        class="flex-1 px-6 py-4 text-3xl bg-white/10 backdrop-blur border-3 funky-border rounded-2xl focus:outline-none focus:ring-4 focus:ring-pink-500 placeholder-white/50 font-semibold"
                    >
                    <button
                        type="submit"
                        class="px-8 py-4 text-3xl font-bold bg-gradient-to-r from-pink-500 via-purple-500 to-cyan-500 hover:from-pink-600 hover:via-purple-600 hover:to-cyan-600 rounded-2xl transition-all card-hover shadow-lg shadow-purple-500/30 focus:outline-none focus:ring-4 focus:ring-pink-500"
                    >
                        JOIN! 🚀
                    </button>
                </div>
            </form>

            <!-- Existing players -->
            @if($players->count() > 0)
                <div class="flex-1 overflow-y-auto focus:outline-none focus:ring-4 focus:ring-pink-500 rounded-2xl" tabindex="0">
                    <h2 class="font-display text-4xl text-center mb-4 text-yellow-400">
                        ⚡ CHOOSE YOUR FIGHTER ⚡
                    </h2>
                    <div class="grid grid-cols-4 gap-4 pr-2">
                        @foreach($players->sortBy('name') as $index => $player)
                            <form action="{{ route('players.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="name" value="{{ $player->name }}">
                                <button
                                    type="submit"
                                    data-player-button
                                    data-player-index="{{ $index }}"
                                    :class="{'ring-4 ring-yellow-400 scale-105 bg-white/20': focusedIndex === {{ $index }}}"
                                    class="w-full px-4 py-6 bg-white/10 backdrop-blur funky-border rounded-2xl card-hover text-center group transition-all focus:outline-none focus:ring-4 focus:ring-yellow-400"
                                    style="animation-delay: {{ $index * 0.1 }}s"
                                >
                                    <div class="text-5xl mb-2 group-hover:animate-bounce">
                                        {{ ['🎯', '🔥', '⚡', '🌟', '💫', '🎪', '🎨', '🎭'][$index % 8] }}
                                    </div>
                                    <div class="font-bold text-2xl text-white truncate">{{ $player->name }}</div>
                                    <div class="text-xl text-yellow-400 font-bold mt-1">
                                        ⭐ {{ $player->elo_rating }}
                                    </div>
                                    @if($player->total_games > 0)
                                        <div class="text-sm text-cyan-400 font-semibold">
                                            {{ $player->total_games }} battles
                                        </div>
                                    @else
                                        <div class="text-sm text-pink-400 font-semibold">
                                            New!
                                        </div>
                                    @endif
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="flex-1 flex items-center justify-center">
                    <div class="text-center p-8 bg-white/5 backdrop-blur rounded-3xl funky-border">
                        <div class="text-8xl mb-4 animate-float">🎾</div>
                        <p class="text-3xl text-white/70">No players yet!</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Right Column: Leaderboard -->
        <div x-data="leaderboardComponent()" x-init="loadLeaderboard()" class="flex flex-col">
            <h2 class="font-display text-5xl text-center mb-4 text-yellow-400">
                🏆 LEADERBOARD
            </h2>

            <div x-show="loading" class="flex-1 flex items-center justify-center">
                <div class="text-8xl animate-spin">🏓</div>
            </div>

            <div x-show="!loading && leaderboard.length > 0" class="flex-1 overflow-y-auto space-y-3 pr-2 focus:outline-none focus:ring-4 focus:ring-cyan-500 rounded-2xl" tabindex="0">
                <template x-for="(player, index) in leaderboard" :key="player.id">
                    <a :href="'/stats/' + player.id"
                       class="block px-6 py-4 bg-white/10 backdrop-blur rounded-2xl funky-border card-hover">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="text-4xl font-bold w-16"
                                     :class="{
                                         'text-yellow-400': index === 0,
                                         'text-gray-300': index === 1,
                                         'text-orange-400': index === 2,
                                         'text-cyan-400': index > 2
                                     }"
                                     x-text="'#' + (index + 1)">
                                </div>
                                <div>
                                    <div class="font-bold text-3xl" x-text="player.name"></div>
                                    <div class="text-2xl text-yellow-400 font-bold">
                                        ⭐ <span x-text="player.elo_rating"></span>
                                    </div>
                                    <div class="text-lg text-white/60">
                                        <span x-text="player.total_wins"></span>W / <span x-text="player.total_games"></span>G
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-4xl font-bold text-green-400"
                                     x-text="player.win_rate + '%'">
                                </div>
                                <div class="text-lg text-white/60">win rate</div>
                            </div>
                        </div>
                    </a>
                </template>
            </div>

            <div x-show="!loading && leaderboard.length === 0"
                 class="flex-1 flex items-center justify-center text-3xl text-white/60">
                No games yet!
            </div>
        </div>
    </div>
</div>

<script>
function leaderboardComponent() {
    return {
        leaderboard: [],
        loading: true,

        async loadLeaderboard() {
            try {
                const response = await fetch('/api/leaderboard');
                const data = await response.json();
                this.leaderboard = data;
            } catch (e) {
                console.error('Failed to load leaderboard:', e);
            }
            this.loading = false;
        }
    }
}

function homeKeyboard() {
    return {
        focusedIndex: 0,
        playerCount: {{ $players->count() }},

        handleKeydown(event) {
            // Tab key - focus input
            if (event.key === 'Tab' && !event.shiftKey) {
                const input = document.getElementById('newPlayerInput');
                if (input && document.activeElement !== input) {
                    event.preventDefault();
                    input.focus();
                    return;
                }
            }

            // If input is focused and we press backspace, blur it
            if (event.key === 'Backspace') {
                if (document.activeElement === document.getElementById('newPlayerInput')) {
                    document.activeElement.blur();
                    event.preventDefault();
                    return;
                }
            }

            if (!this.playerCount) return;

            // Prevent default for navigation keys
            if (['ArrowUp', 'ArrowDown', 'Enter', 'ArrowLeft', 'ArrowRight'].includes(event.key)) {
                event.preventDefault();
            }

            switch(event.key) {
                case 'ArrowDown':
                case 'ArrowRight':
                    this.focusedIndex = (this.focusedIndex + 1) % this.playerCount;
                    this.scrollToFocused();
                    break;
                case 'ArrowUp':
                case 'ArrowLeft':
                    this.focusedIndex = (this.focusedIndex - 1 + this.playerCount) % this.playerCount;
                    this.scrollToFocused();
                    break;
                case 'Enter':
                    this.selectPlayer();
                    break;
            }
        },

        selectPlayer() {
            const buttons = document.querySelectorAll('[data-player-button]');
            if (buttons[this.focusedIndex]) {
                buttons[this.focusedIndex].click();
            }
        },

        scrollToFocused() {
            const buttons = document.querySelectorAll('[data-player-button]');
            if (buttons[this.focusedIndex]) {
                buttons[this.focusedIndex].scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            }
        }
    }
}
</script>
@endsection
