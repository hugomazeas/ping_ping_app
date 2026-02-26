@extends('layouts.app')

@section('title', 'Game On! - Ping Pong Tracker')

@section('content')
<div x-data="gameTracker()" x-init="init()" @keydown.window="handleKeydown($event)" class="h-full flex flex-col">
    <!-- Game In Progress -->
    <div x-show="!isComplete" x-cloak class="h-full flex flex-col">
        <!-- Top Bar: Timer, Mode, Current Time -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-4">
                <div class="text-3xl text-white/60 font-semibold">
                    @if($game->mode === 'freestyle')
                        🎪 FREESTYLE
                    @else
                        🏆 FIRST TO {{ $game->mode }}
                    @endif
                </div>
            </div>

            <!-- Center: Timer and Current Time -->
            <div class="flex flex-col items-center gap-3">
                <div class="px-10 py-4 bg-black/30 backdrop-blur rounded-2xl funky-border">
                    <div class="text-7xl font-mono font-bold rainbow-text" x-text="timerDisplay"></div>
                </div>
                <div class="text-4xl text-white/60 font-mono font-bold" x-text="currentTime"></div>
            </div>

            <!-- Keyboard Shortcuts Help -->
            <div class="bg-white/10 backdrop-blur rounded-2xl px-6 py-3 funky-border">
                <div class="text-lg font-bold text-yellow-400 mb-1">⌨️ KEYBOARD</div>
                <div class="text-base text-white/80 space-y-1">
                    <div><kbd class="px-2 py-1 bg-white/20 rounded">↑</kbd> Player 1 +1</div>
                    <div><kbd class="px-2 py-1 bg-white/20 rounded">↓</kbd> Player 1 -1</div>
                    <div><kbd class="px-2 py-1 bg-white/20 rounded">{{ $reverseControls ? '←' : '→' }}</kbd> Player 2 +1</div>
                    <div><kbd class="px-2 py-1 bg-white/20 rounded">{{ $reverseControls ? '→' : '←' }}</kbd> Player 2 -1</div>
                    @if($game->mode === 'freestyle')
                        <div><kbd class="px-2 py-1 bg-white/20 rounded">0</kbd> End Game</div>
                    @endif
                    <div><kbd class="px-2 py-1 bg-white/20 rounded">Backspace</kbd> Quit</div>
                </div>
            </div>
        </div>

        <!-- Scores - Full Height -->
        <div class="flex-1 grid grid-cols-2 gap-6">
            <!-- Player 1 -->
            <div class="bg-gradient-to-br from-pink-500/20 to-rose-600/20 backdrop-blur border-2 border-pink-500 rounded-3xl p-8 flex flex-col items-center justify-between relative">
                <!-- Serve Indicator -->
                <div x-show="currentServerId === player1Id" class="absolute top-8 right-8 text-8xl animate-bounce-slow">
                    🏓
                </div>

                <div class="text-6xl font-display text-pink-400 neon-text truncate max-w-full">
                    {{ strtoupper($game->player1->name) }}
                </div>
                <div class="text-[20rem] leading-none font-bold text-white" x-text="player1Score"></div>
                <div class="flex justify-center gap-8">
                    <button
                        @click="updateScore(1, 'decrement')"
                        class="w-40 h-28 text-8xl bg-red-500/80 hover:bg-red-500 rounded-3xl transition-all card-hover font-bold disabled:opacity-30 focus:outline-none focus:ring-4 focus:ring-red-400"
                        :disabled="player1Score <= 0 || isUpdating"
                        tabindex="-1"
                    >−</button>
                    <button
                        @click="updateScore(1, 'increment')"
                        class="w-48 h-28 text-8xl bg-green-500/80 hover:bg-green-500 rounded-3xl transition-all card-hover font-bold disabled:opacity-30 focus:outline-none focus:ring-4 focus:ring-green-400"
                        :disabled="isUpdating"
                        tabindex="-1"
                    >+</button>
                </div>
            </div>

            <!-- Player 2 -->
            <div class="bg-gradient-to-br from-cyan-500/20 to-blue-600/20 backdrop-blur border-2 border-cyan-500 rounded-3xl p-8 flex flex-col items-center justify-between relative">
                <!-- Serve Indicator -->
                <div x-show="currentServerId === player2Id" class="absolute top-8 right-8 text-8xl animate-bounce-slow">
                    🏓
                </div>

                <div class="text-6xl font-display text-cyan-400 neon-text truncate max-w-full">
                    {{ strtoupper($game->player2->name) }}
                </div>
                <div class="text-[20rem] leading-none font-bold text-white" x-text="player2Score"></div>
                <div class="flex justify-center gap-8">
                    <button
                        @click="updateScore(2, 'decrement')"
                        class="w-40 h-28 text-8xl bg-red-500/80 hover:bg-red-500 rounded-3xl transition-all card-hover font-bold disabled:opacity-30 focus:outline-none focus:ring-4 focus:ring-red-400"
                        :disabled="player2Score <= 0 || isUpdating"
                        tabindex="-1"
                    >−</button>
                    <button
                        @click="updateScore(2, 'increment')"
                        class="w-48 h-28 text-8xl bg-green-500/80 hover:bg-green-500 rounded-3xl transition-all card-hover font-bold disabled:opacity-30 focus:outline-none focus:ring-4 focus:ring-green-400"
                        :disabled="isUpdating"
                        tabindex="-1"
                    >+</button>
                </div>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="flex items-center justify-center gap-6 mt-6">
            @if($game->mode === 'freestyle')
                <button
                    @click="endGame()"
                    class="px-12 py-4 text-3xl font-bold bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 rounded-2xl transition-all card-hover shadow-lg shadow-orange-500/30 focus:outline-none focus:ring-4 focus:ring-orange-400"
                    :disabled="isUpdating"
                    tabindex="-1"
                >
                    🏁 END GAME (Press <kbd class="px-2 py-1 bg-white/20 rounded">0</kbd>)
                </button>
            @endif
            <a href="{{ route('home') }}" class="text-white/40 hover:text-white/70 transition-colors text-2xl focus:outline-none focus:ring-4 focus:ring-pink-500 rounded px-2" tabindex="-1">
                🚪 Abandon (Press <kbd class="px-2 py-1 bg-white/20 rounded">Backspace</kbd>)
            </a>
        </div>
    </div>

    <!-- Game Over -->
    <div x-show="isComplete" x-cloak class="h-full flex flex-col items-center justify-center">
        <template x-if="winnerId">
            <div class="text-center">
                <div class="text-[10rem] mb-6 animate-bounce-slow">🏆</div>
                <div class="font-display text-[8rem] mb-6 rainbow-text" x-text="winnerName.toUpperCase() + ' WINS!'"></div>
                <div class="text-8xl mb-8">🎉🎊🎉</div>
            </div>
        </template>

        <template x-if="!winnerId">
            <div class="text-center">
                <div class="text-[10rem] mb-6">🤝</div>
                <div class="font-display text-[8rem] mb-8 text-yellow-400">IT'S A DRAW!</div>
            </div>
        </template>

        <div class="inline-block px-12 py-8 bg-white/10 backdrop-blur rounded-3xl funky-border mb-8">
            <div class="text-7xl font-bold mb-3">
                <span class="text-pink-400" x-text="player1Score"></span>
                <span class="text-white/40 mx-4">-</span>
                <span class="text-cyan-400" x-text="player2Score"></span>
            </div>
            <div class="text-3xl text-white/60 mb-4">
                ⏱️ <span x-text="timerDisplay"></span>
            </div>

            <!-- ELO Changes -->
            <template x-if="player1EloChange !== null && player2EloChange !== null">
                <div class="border-t border-white/20 pt-4">
                    <div class="text-2xl text-yellow-400 font-bold mb-3">⭐ ELO CHANGES</div>
                    <div class="flex justify-center gap-8">
                        <div class="text-center">
                            <div class="text-xl text-pink-400 mb-1" x-text="player1Name"></div>
                            <div class="text-2xl text-white/60 mb-1">
                                <span x-text="player1InitialElo"></span> →
                                <span x-text="player1InitialElo + player1EloChange"></span>
                            </div>
                            <div class="text-4xl font-bold" :class="player1EloChange >= 0 ? 'text-green-400' : 'text-red-400'">
                                <span x-text="(player1EloChange >= 0 ? '+' : '') + player1EloChange"></span>
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="text-xl text-cyan-400 mb-1" x-text="player2Name"></div>
                            <div class="text-2xl text-white/60 mb-1">
                                <span x-text="player2InitialElo"></span> →
                                <span x-text="player2InitialElo + player2EloChange"></span>
                            </div>
                            <div class="text-4xl font-bold" :class="player2EloChange >= 0 ? 'text-green-400' : 'text-red-400'">
                                <span x-text="(player2EloChange >= 0 ? '+' : '') + player2EloChange"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div class="flex justify-center gap-6">
            <form action="{{ route('game.rematch', $game) }}" method="POST">
                @csrf
                <button type="submit" class="px-12 py-6 text-4xl font-bold bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 rounded-3xl transition-all card-hover shadow-lg shadow-green-500/30 focus:outline-none focus:ring-4 focus:ring-green-400" autofocus>
                    🔄 REMATCH! (Press <kbd class="px-2 py-1 bg-white/20 rounded">Enter</kbd>)
                </button>
            </form>

            <a href="{{ route('home') }}" class="px-12 py-6 text-4xl font-bold bg-white/10 backdrop-blur hover:bg-white/20 rounded-3xl transition-all card-hover funky-border focus:outline-none focus:ring-4 focus:ring-pink-500">
                🏠 Back to Lobby (Press <kbd class="px-2 py-1 bg-white/20 rounded">Backspace</kbd>)
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
        @if($game->ended_at)
        endedAt: new Date('{{ $game->ended_at->toIso8601String() }}'),
        @else
        endedAt: null,
        @endif
        elapsedSeconds: 0,
        timerInterval: null,
        isUpdating: false,
        currentServerId: {{ $game->current_server_id ?? $game->player1_id }},
        serveNumber: {{ $game->serve_number ?? 1 }},
        reverseControls: {{ env('REVERSE_CONTROLS', false) ? 'true' : 'false' }},
        gameMode: '{{ $game->mode }}',
        currentTime: '',
        player1EloChange: null,
        player2EloChange: null,
        player1InitialElo: null,
        player2InitialElo: null,

        get timerDisplay() {
            const mins = Math.floor(this.elapsedSeconds / 60);
            const secs = this.elapsedSeconds % 60;
            return String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
        },

        updateCurrentTime() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            this.currentTime = hours + ':' + minutes + ':' + seconds;
        },

        get winnerName() {
            if (this.winnerId === this.player1Id) return this.player1Name;
            if (this.winnerId === this.player2Id) return this.player2Name;
            return '';
        },

        get currentServerName() {
            if (this.currentServerId === this.player1Id) return this.player1Name;
            if (this.currentServerId === this.player2Id) return this.player2Name;
            return '';
        },

        init() {
            this.updateElapsed();
            this.updateCurrentTime();
            if (!this.isComplete) {
                this.timerInterval = setInterval(() => {
                    this.updateElapsed();
                    this.updateCurrentTime();
                }, 1000);
            } else {
                // Update current time even when game is complete
                setInterval(() => {
                    this.updateCurrentTime();
                }, 1000);
            }
        },

        updateElapsed() {
            // If game is complete, use the ended_at time, otherwise use current time
            if (this.isComplete && this.endedAt) {
                this.elapsedSeconds = Math.floor((this.endedAt - this.startedAt) / 1000);
            } else {
                const now = new Date();
                this.elapsedSeconds = Math.floor((now - this.startedAt) / 1000);
            }
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
                    this.currentServerId = data.current_server_id;
                    this.serveNumber = data.serve_number;

                    if (data.is_complete) {
                        // Capture the end time before marking as complete
                        this.endedAt = new Date();
                        this.updateElapsed(); // Update one final time
                        this.isComplete = true;
                        this.winnerId = data.winner_id;
                        this.player1EloChange = data.player1_elo_change || null;
                        this.player2EloChange = data.player2_elo_change || null;
                        this.player1InitialElo = data.player1_initial_elo || null;
                        this.player2InitialElo = data.player2_initial_elo || null;
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
                    // Capture the end time before marking as complete
                    this.endedAt = new Date();
                    this.updateElapsed(); // Update one final time
                    this.isComplete = true;
                    this.winnerId = data.winner_id;
                    this.player1EloChange = data.player1_elo_change || null;
                    this.player2EloChange = data.player2_elo_change || null;
                    this.player1InitialElo = data.player1_initial_elo || null;
                    this.player2InitialElo = data.player2_initial_elo || null;
                    clearInterval(this.timerInterval);
                }
            } catch (e) {
                console.error('Failed to end game:', e);
            }

            this.isUpdating = false;
        },

        handleKeydown(event) {
            // Backspace key - go back to home
            if (event.key === 'Backspace') {
                event.preventDefault();
                if (this.isComplete) {
                    // Game over - go to home
                    window.location.href = '{{ route('home') }}';
                } else {
                    // Game in progress - confirm abandon
                    if (confirm('Are you sure you want to abandon this game?')) {
                        window.location.href = '{{ route('home') }}';
                    }
                }
                return;
            }

            // Enter key when game is complete - rematch
            if (event.key === 'Enter' && this.isComplete) {
                event.preventDefault();
                const rematchButton = document.querySelector('button[type="submit"]');
                if (rematchButton) {
                    rematchButton.click();
                }
                return;
            }

            if (this.isComplete) return;

            // Prevent default browser behavior
            if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', '0'].includes(event.key)) {
                event.preventDefault();
            }

            // Determine left/right keys based on reverseControls
            const leftKey = this.reverseControls ? 'ArrowRight' : 'ArrowLeft';
            const rightKey = this.reverseControls ? 'ArrowLeft' : 'ArrowRight';

            switch(event.key) {
                case 'ArrowUp':
                    this.updateScore(1, 'increment');
                    break;
                case 'ArrowDown':
                    this.updateScore(1, 'decrement');
                    break;
                case rightKey:
                    this.updateScore(2, 'increment');
                    break;
                case leftKey:
                    this.updateScore(2, 'decrement');
                    break;
                case '0':
                    if (this.gameMode === 'freestyle') {
                        this.endGame();
                    }
                    break;
            }
        }
    }
}
</script>
@endsection
