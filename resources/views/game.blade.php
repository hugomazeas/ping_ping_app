@extends('layouts.app')

@section('title', 'Game On! - Ping Pong Tracker')

@section('content')
<div x-data="gameTracker()" x-init="init()">
    <!-- Game In Progress -->
    <div x-show="!isComplete" x-cloak>
        <!-- Timer -->
        <div class="text-center mb-8">
            <div class="inline-block px-8 py-4 bg-black/30 backdrop-blur rounded-2xl funky-border">
                <div class="text-6xl md:text-8xl font-mono font-bold rainbow-text" x-text="timerDisplay"></div>
            </div>
            <div class="mt-4 text-xl text-white/60 font-semibold">
                @if($game->mode === 'freestyle')
                    🎪 FREESTYLE MODE
                @else
                    🏆 FIRST TO {{ $game->mode }}
                @endif
            </div>
        </div>

        <!-- VS Banner -->
        <div class="text-center mb-6 font-display text-3xl text-yellow-400 animate-pulse">
            ⚔️ FIGHT! ⚔️
        </div>

        <!-- Scores -->
        <div class="grid grid-cols-2 gap-4 md:gap-8 mb-8">
            <!-- Player 1 -->
            <div class="bg-gradient-to-br from-pink-500/20 to-rose-600/20 backdrop-blur border-2 border-pink-500 rounded-3xl p-6 text-center">
                <div class="text-2xl md:text-3xl font-display mb-4 text-pink-400 neon-text truncate">
                    {{ strtoupper($game->player1->name) }}
                </div>
                <div class="text-8xl md:text-9xl font-bold mb-6 text-white" x-text="player1Score"></div>
                <div class="flex justify-center gap-3">
                    <button
                        @click="updateScore(1, 'decrement')"
                        class="w-16 h-16 text-4xl bg-red-500/80 hover:bg-red-500 rounded-2xl transition-all card-hover font-bold disabled:opacity-30"
                        :disabled="player1Score <= 0 || isUpdating"
                    >−</button>
                    <button
                        @click="updateScore(1, 'increment')"
                        class="w-20 h-16 text-4xl bg-green-500/80 hover:bg-green-500 rounded-2xl transition-all card-hover font-bold disabled:opacity-30"
                        :disabled="isUpdating"
                    >+</button>
                </div>
            </div>

            <!-- Player 2 -->
            <div class="bg-gradient-to-br from-cyan-500/20 to-blue-600/20 backdrop-blur border-2 border-cyan-500 rounded-3xl p-6 text-center">
                <div class="text-2xl md:text-3xl font-display mb-4 text-cyan-400 neon-text truncate">
                    {{ strtoupper($game->player2->name) }}
                </div>
                <div class="text-8xl md:text-9xl font-bold mb-6 text-white" x-text="player2Score"></div>
                <div class="flex justify-center gap-3">
                    <button
                        @click="updateScore(2, 'decrement')"
                        class="w-16 h-16 text-4xl bg-red-500/80 hover:bg-red-500 rounded-2xl transition-all card-hover font-bold disabled:opacity-30"
                        :disabled="player2Score <= 0 || isUpdating"
                    >−</button>
                    <button
                        @click="updateScore(2, 'increment')"
                        class="w-20 h-16 text-4xl bg-green-500/80 hover:bg-green-500 rounded-2xl transition-all card-hover font-bold disabled:opacity-30"
                        :disabled="isUpdating"
                    >+</button>
                </div>
            </div>
        </div>

        <!-- Freestyle End Game Button -->
        @if($game->mode === 'freestyle')
            <div class="text-center mb-8">
                <button
                    @click="endGame()"
                    class="px-10 py-4 text-xl font-bold bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 rounded-2xl transition-all card-hover shadow-lg shadow-orange-500/30"
                    :disabled="isUpdating"
                >
                    🏁 END GAME
                </button>
            </div>
        @endif

        <div class="text-center">
            <a href="{{ route('home') }}" class="text-white/40 hover:text-white/70 transition-colors text-sm">
                🚪 Abandon Battle
            </a>
        </div>
    </div>

    <!-- Game Over -->
    <div x-show="isComplete" x-cloak class="text-center py-8">
        <template x-if="winnerId">
            <div>
                <div class="text-8xl mb-6 animate-bounce-slow">🏆</div>
                <div class="font-display text-5xl md:text-7xl mb-4 rainbow-text" x-text="winnerName.toUpperCase() + ' WINS!'"></div>
                <div class="text-6xl mb-8">🎉🎊🎉</div>
            </div>
        </template>

        <template x-if="!winnerId">
            <div>
                <div class="text-8xl mb-6">🤝</div>
                <div class="font-display text-5xl md:text-7xl mb-4 text-yellow-400">IT'S A DRAW!</div>
            </div>
        </template>

        <div class="inline-block px-10 py-6 bg-white/10 backdrop-blur rounded-3xl funky-border mb-8">
            <div class="text-4xl font-bold mb-2">
                <span class="text-pink-400" x-text="player1Score"></span>
                <span class="text-white/40 mx-3">-</span>
                <span class="text-cyan-400" x-text="player2Score"></span>
            </div>
            <div class="text-white/60">
                ⏱️ <span x-text="timerDisplay"></span>
            </div>
        </div>

        <div class="flex flex-col md:flex-row justify-center gap-4">
            <form action="{{ route('game.rematch', $game) }}" method="POST">
                @csrf
                <button type="submit" class="w-full md:w-auto px-10 py-4 text-xl font-bold bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 rounded-2xl transition-all card-hover shadow-lg shadow-green-500/30">
                    🔄 REMATCH!
                </button>
            </form>

            <a href="{{ route('home') }}" class="px-10 py-4 text-xl font-bold bg-white/10 backdrop-blur hover:bg-white/20 rounded-2xl transition-all card-hover funky-border">
                🏠 Back to Lobby
            </a>
        </div>
    </div>
</div>

<script>
function gameTracker() {
    return {
        gameId: {{ $game->id }},
        player1Score: {{ $game->player1_score }},
        player2Score: {{ $game->player2_score }},
        isComplete: {{ $game->isComplete() ? 'true' : 'false' }},
        winnerId: {{ $game->winner_id ?? 'null' }},
        player1Id: {{ $game->player1_id }},
        player2Id: {{ $game->player2_id }},
        player1Name: '{{ addslashes($game->player1->name) }}',
        player2Name: '{{ addslashes($game->player2->name) }}',
        startedAt: new Date('{{ $game->started_at->toIso8601String() }}'),
        elapsedSeconds: 0,
        timerInterval: null,
        isUpdating: false,

        get timerDisplay() {
            const mins = Math.floor(this.elapsedSeconds / 60);
            const secs = this.elapsedSeconds % 60;
            return String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
        },

        get winnerName() {
            if (this.winnerId === this.player1Id) return this.player1Name;
            if (this.winnerId === this.player2Id) return this.player2Name;
            return '';
        },

        init() {
            this.updateElapsed();
            if (!this.isComplete) {
                this.timerInterval = setInterval(() => {
                    this.updateElapsed();
                }, 1000);
            }
        },

        updateElapsed() {
            const now = new Date();
            this.elapsedSeconds = Math.floor((now - this.startedAt) / 1000);
        },

        async updateScore(player, action) {
            if (this.isUpdating || this.isComplete) return;
            this.isUpdating = true;

            try {
                const response = await fetch('/game/' + this.gameId, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ player, action })
                });

                const data = await response.json();

                if (response.ok) {
                    this.player1Score = data.player1_score;
                    this.player2Score = data.player2_score;

                    if (data.is_complete) {
                        this.isComplete = true;
                        this.winnerId = data.winner_id;
                        clearInterval(this.timerInterval);
                    }
                }
            } catch (e) {
                console.error('Failed to update score:', e);
            }

            this.isUpdating = false;
        },

        async endGame() {
            if (this.isUpdating || this.isComplete) return;
            this.isUpdating = true;

            try {
                const response = await fetch('/game/' + this.gameId + '/end', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    this.isComplete = true;
                    this.winnerId = data.winner_id;
                    clearInterval(this.timerInterval);
                }
            } catch (e) {
                console.error('Failed to end game:', e);
            }

            this.isUpdating = false;
        }
    }
}
</script>
@endsection
