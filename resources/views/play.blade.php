@extends('layouts.app')

@section('title', 'Select Opponent - Ping Pong Tracker')

@section('content')
<div x-data="opponentSelect()">
    <!-- Header -->
    <div class="text-center mb-10">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-white/60 hover:text-white transition-colors mb-4">
            ← Back to lobby
        </a>
        <div class="text-6xl mb-4 animate-float">👋</div>
        <h1 class="font-display text-4xl md:text-5xl text-pink-400 neon-text">
            READY {{ strtoupper($player->name) }}?
        </h1>
        <p class="text-xl text-white/60 mt-2">Pick your opponent wisely...</p>
    </div>

    <!-- Step 1: Select Opponent -->
    <div x-show="!selectedOpponent" x-cloak>
        <!-- New opponent input -->
        <div class="mb-8">
            <div class="relative flex gap-3">
                <input
                    type="text"
                    x-model="newOpponentName"
                    placeholder="🆕 Challenge someone new..."
                    class="flex-1 px-6 py-4 text-lg bg-white/10 backdrop-blur funky-border rounded-2xl focus:outline-none focus:ring-4 focus:ring-cyan-500/50 placeholder-white/50 font-semibold"
                >
                <button
                    x-show="newOpponentName.length > 0"
                    @click="selectNewOpponent()"
                    class="px-6 py-4 bg-gradient-to-r from-cyan-500 to-blue-500 hover:from-cyan-600 hover:to-blue-600 rounded-2xl font-bold card-hover shadow-lg shadow-cyan-500/30"
                >
                    FIGHT! 🥊
                </button>
            </div>
        </div>

        <div class="text-center mb-6">
            <span class="px-6 py-2 bg-white/10 rounded-full text-white/60 font-semibold">
                ⚔️ OR PICK A RIVAL ⚔️
            </span>
        </div>

        <!-- Existing opponents -->
        @if($opponents->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($opponents as $index => $opponent)
                    <button
                        @click="selectOpponent({{ $opponent->id }}, '{{ addslashes($opponent->name) }}')"
                        class="relative px-6 py-6 text-left bg-white/10 backdrop-blur funky-border rounded-2xl card-hover group overflow-hidden"
                    >
                        <div class="absolute top-0 right-0 text-6xl opacity-20 transform translate-x-4 -translate-y-2 group-hover:scale-110 transition-transform">
                            {{ ['👊', '🎯', '🔥', '💪', '⚡', '🌪️', '🎲', '🃏'][$index % 8] }}
                        </div>
                        <div class="relative">
                            <span class="font-bold text-xl text-white">{{ $opponent->name }}</span>
                            @if($opponent->total_games > 0)
                                <div class="flex gap-4 mt-2 text-sm">
                                    <span class="text-green-400">{{ $opponent->total_wins }}W</span>
                                    <span class="text-red-400">{{ $opponent->total_losses }}L</span>
                                    <span class="text-yellow-400">{{ $opponent->win_rate }}%</span>
                                </div>
                            @else
                                <div class="text-sm text-purple-400 mt-2">Untested warrior 🌟</div>
                            @endif
                        </div>
                    </button>
                @endforeach
            </div>
        @else
            <div class="text-center p-12 bg-white/5 backdrop-blur rounded-3xl funky-border">
                <div class="text-6xl mb-4">🦗</div>
                <p class="text-xl text-white/60">No opponents yet... Enter a name above!</p>
            </div>
        @endif
    </div>

    <!-- Step 2: Select Game Mode -->
    <div x-show="selectedOpponent" x-cloak class="text-center">
        <div class="mb-10 p-6 bg-white/10 backdrop-blur rounded-3xl funky-border">
            <div class="text-2xl mb-4">⚔️ BATTLE CONFIRMED ⚔️</div>
            <div class="flex items-center justify-center gap-4 text-3xl font-display">
                <span class="text-pink-400 neon-text">{{ strtoupper($player->name) }}</span>
                <span class="text-yellow-400 animate-pulse">VS</span>
                <span class="text-cyan-400 neon-text" x-text="selectedOpponentName.toUpperCase()"></span>
            </div>
        </div>

        <h2 class="font-display text-3xl mb-8 text-yellow-400">🎮 SELECT MODE 🎮</h2>

        <form action="{{ route('games.store') }}" method="POST">
            @csrf
            <input type="hidden" name="opponent_id" x-model="selectedOpponentId">
            <input type="hidden" name="opponent_name" x-model="selectedOpponentName">
            <input type="hidden" name="mode" x-model="selectedMode">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-3xl mx-auto mb-8">
                <button
                    type="submit"
                    @click="selectedMode = '11'"
                    class="group px-6 py-10 bg-gradient-to-br from-green-500/20 to-emerald-600/20 backdrop-blur border-2 border-green-500 hover:border-green-400 rounded-3xl transition-all card-hover"
                >
                    <div class="text-6xl mb-4 group-hover:animate-bounce">🏆</div>
                    <div class="font-display text-4xl text-green-400">FIRST TO 11</div>
                    <div class="text-sm text-white/60 mt-2">Classic mode</div>
                </button>

                <button
                    type="submit"
                    @click="selectedMode = '21'"
                    class="group px-6 py-10 bg-gradient-to-br from-blue-500/20 to-indigo-600/20 backdrop-blur border-2 border-blue-500 hover:border-blue-400 rounded-3xl transition-all card-hover"
                >
                    <div class="text-6xl mb-4 group-hover:animate-bounce">🎖️</div>
                    <div class="font-display text-4xl text-blue-400">FIRST TO 21</div>
                    <div class="text-sm text-white/60 mt-2">Marathon mode</div>
                </button>

                <button
                    type="submit"
                    @click="selectedMode = 'freestyle'"
                    class="group px-6 py-10 bg-gradient-to-br from-purple-500/20 to-pink-600/20 backdrop-blur border-2 border-purple-500 hover:border-purple-400 rounded-3xl transition-all card-hover"
                >
                    <div class="text-6xl mb-4 group-hover:animate-bounce">🎪</div>
                    <div class="font-display text-4xl text-purple-400">FREESTYLE</div>
                    <div class="text-sm text-white/60 mt-2">No rules!</div>
                </button>
            </div>
        </form>

        <button
            @click="selectedOpponent = false; selectedOpponentId = null; selectedOpponentName = ''"
            class="text-white/60 hover:text-white transition-colors"
        >
            ← Pick different opponent
        </button>
    </div>
</div>

<script>
function opponentSelect() {
    return {
        selectedOpponent: false,
        selectedOpponentId: null,
        selectedOpponentName: '',
        selectedMode: '11',
        newOpponentName: '',

        selectOpponent(id, name) {
            this.selectedOpponentId = id;
            this.selectedOpponentName = name;
            this.selectedOpponent = true;
        },

        selectNewOpponent() {
            if (this.newOpponentName.trim().length > 0) {
                this.selectedOpponentId = null;
                this.selectedOpponentName = this.newOpponentName.trim();
                this.selectedOpponent = true;
            }
        }
    }
}
</script>
@endsection
