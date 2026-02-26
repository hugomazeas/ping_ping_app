@extends('layouts.app')

@section('title', 'Select Opponent - Ping Pong Tracker')

@section('content')
<div x-data="opponentSelect()" @keydown.window="handleKeydown($event)" class="h-full flex flex-col">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div class="text-center flex-1">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-2xl text-white/60 hover:text-white transition-colors mb-3 focus:outline-none focus:ring-4 focus:ring-pink-500 rounded px-2">
                ← Back to lobby
            </a>
            <h1 class="font-display text-6xl text-pink-400 neon-text">
                👋 READY {{ strtoupper($player->name) }}?
            </h1>
            <p class="text-2xl text-white/60 mt-2">Pick your opponent wisely...</p>
        </div>

        <!-- Keyboard Shortcuts Help -->
        <div class="bg-white/10 backdrop-blur rounded-2xl px-6 py-3 funky-border">
            <div class="text-lg font-bold text-yellow-400 mb-1">⌨️ KEYBOARD SHORTCUTS</div>
            <div x-show="!selectedOpponent" class="text-base text-white/80 space-y-1">
                <div><kbd class="px-2 py-1 bg-white/20 rounded">↑↓←→</kbd> Navigate</div>
                <div><kbd class="px-2 py-1 bg-white/20 rounded">Enter</kbd> Select</div>
                <div><kbd class="px-2 py-1 bg-white/20 rounded">Tab</kbd> New Opponent</div>
                <div><kbd class="px-2 py-1 bg-white/20 rounded">Backspace</kbd> Back</div>
            </div>
            <div x-show="selectedOpponent" class="text-base text-white/80 space-y-1">
                <div><kbd class="px-2 py-1 bg-white/20 rounded">1</kbd> First to 11</div>
                <div><kbd class="px-2 py-1 bg-white/20 rounded">2</kbd> First to 21</div>
                <div><kbd class="px-2 py-1 bg-white/20 rounded">3</kbd> Freestyle</div>
                <div><kbd class="px-2 py-1 bg-white/20 rounded">Backspace</kbd> Back</div>
            </div>
        </div>
    </div>

    <div class="flex-1 overflow-hidden">

        <!-- Step 1: Select Opponent -->
        <div x-show="!selectedOpponent" x-cloak class="h-full flex flex-col">
            <!-- New opponent input -->
            <div class="mb-4">
                <div class="relative flex gap-4">
                    <input
                        id="newOpponentInput"
                        type="text"
                        x-model="newOpponentName"
                        @keydown.enter.prevent="newOpponentName.length > 0 && selectNewOpponent()"
                        placeholder="🆕 Challenge someone new..."
                        class="flex-1 px-8 py-4 text-3xl bg-white/10 backdrop-blur funky-border rounded-2xl focus:outline-none focus:ring-4 focus:ring-cyan-500 placeholder-white/50 font-semibold"
                    >
                    <button
                        x-show="newOpponentName.length > 0"
                        @click="selectNewOpponent()"
                        class="px-10 py-4 text-3xl bg-gradient-to-r from-cyan-500 to-blue-500 hover:from-cyan-600 hover:to-blue-600 rounded-2xl font-bold card-hover shadow-lg shadow-cyan-500/30 focus:outline-none focus:ring-4 focus:ring-cyan-500"
                    >
                        FIGHT! 🥊
                    </button>
                </div>
            </div>

            <div class="text-center mb-4">
                <span class="px-8 py-2 bg-white/10 rounded-full text-2xl text-white/60 font-semibold">
                    ⚔️ OR PICK A RIVAL ⚔️
                </span>
            </div>

            <!-- Existing opponents -->
            @if($opponents->count() > 0)
                <div class="flex-1 overflow-y-auto focus:outline-none focus:ring-4 focus:ring-cyan-500 rounded-2xl" tabindex="0">
                    <div class="grid grid-cols-3 gap-4 pr-2">
                        @foreach($opponents as $index => $opponent)
                            <button
                                @click="selectOpponent({{ $opponent->id }}, '{{ addslashes($opponent->name) }}')"
                                data-opponent-button
                                data-opponent-index="{{ $index }}"
                                :class="{'ring-4 ring-yellow-400 scale-105 bg-white/20': focusedOpponentIndex === {{ $index }}}"
                                class="relative px-6 py-6 text-left bg-white/10 backdrop-blur funky-border rounded-2xl card-hover group overflow-hidden focus:outline-none focus:ring-4 focus:ring-cyan-500"
                            >
                                <div class="absolute top-0 right-0 text-6xl opacity-20 transform translate-x-2 -translate-y-1 group-hover:scale-110 transition-transform">
                                    {{ ['👊', '🎯', '🔥', '💪', '⚡', '🌪️', '🎲', '🃏'][$index % 8] }}
                                </div>
                                <div class="relative">
                                    <span class="font-bold text-3xl text-white block truncate">{{ $opponent->name }}</span>
                                    <div class="text-2xl text-yellow-400 font-bold mt-1">
                                        ⭐ {{ $opponent->elo_rating }}
                                    </div>
                                    @if($opponent->total_games > 0)
                                        <div class="flex gap-3 mt-1 text-lg">
                                            <span class="text-green-400">{{ $opponent->total_wins }}W</span>
                                            <span class="text-red-400">{{ $opponent->total_losses }}L</span>
                                        </div>
                                    @else
                                        <div class="text-lg text-purple-400 mt-1">New! 🌟</div>
                                    @endif
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="flex-1 flex items-center justify-center">
                    <div class="text-center p-12 bg-white/5 backdrop-blur rounded-3xl funky-border">
                        <div class="text-8xl mb-4">🦗</div>
                        <p class="text-3xl text-white/60">No opponents yet!</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Step 2: Select Game Mode -->
        <div x-show="selectedOpponent" x-cloak class="h-full flex flex-col justify-center">
            <div class="mb-8 p-8 bg-white/10 backdrop-blur rounded-3xl funky-border text-center">
                <div class="text-4xl mb-4">⚔️ BATTLE CONFIRMED ⚔️</div>
                <div class="flex items-center justify-center gap-6 text-5xl font-display">
                    <span class="text-pink-400 neon-text">{{ strtoupper($player->name) }}</span>
                    <span class="text-yellow-400 animate-pulse">VS</span>
                    <span class="text-cyan-400 neon-text" x-text="selectedOpponentName.toUpperCase()"></span>
                </div>
            </div>

            <h2 class="font-display text-5xl mb-8 text-yellow-400 text-center">🎮 SELECT MODE 🎮</h2>

            <form action="{{ route('games.store') }}" method="POST">
                @csrf
                <input type="hidden" name="opponent_id" x-model="selectedOpponentId">
                <input type="hidden" name="opponent_name" x-model="selectedOpponentName">
                <input type="hidden" name="mode" x-model="selectedMode">

                <div class="grid grid-cols-3 gap-8 mb-8">
                    <button
                        type="submit"
                        @click="selectedMode = '11'"
                        data-mode-button
                        data-mode-index="0"
                        :class="{'ring-4 ring-yellow-400 scale-105': focusedModeIndex === 0}"
                        class="group px-8 py-12 bg-gradient-to-br from-green-500/20 to-emerald-600/20 backdrop-blur border-2 border-green-500 hover:border-green-400 rounded-3xl transition-all card-hover focus:outline-none focus:ring-4 focus:ring-green-400"
                    >
                        <div class="text-8xl mb-4 group-hover:animate-bounce">🏆</div>
                        <div class="font-display text-5xl text-green-400">FIRST TO 11</div>
                        <div class="text-xl text-white/60 mt-2">Classic · Press <kbd class="px-2 py-1 bg-white/20 rounded">1</kbd></div>
                    </button>

                    <button
                        type="submit"
                        @click="selectedMode = '21'"
                        data-mode-button
                        data-mode-index="1"
                        :class="{'ring-4 ring-yellow-400 scale-105': focusedModeIndex === 1}"
                        class="group px-8 py-12 bg-gradient-to-br from-blue-500/20 to-indigo-600/20 backdrop-blur border-2 border-blue-500 hover:border-blue-400 rounded-3xl transition-all card-hover focus:outline-none focus:ring-4 focus:ring-blue-400"
                    >
                        <div class="text-8xl mb-4 group-hover:animate-bounce">🎖️</div>
                        <div class="font-display text-5xl text-blue-400">FIRST TO 21</div>
                        <div class="text-xl text-white/60 mt-2">Marathon · Press <kbd class="px-2 py-1 bg-white/20 rounded">2</kbd></div>
                    </button>

                    <button
                        type="submit"
                        @click="selectedMode = 'freestyle'"
                        data-mode-button
                        data-mode-index="2"
                        :class="{'ring-4 ring-yellow-400 scale-105': focusedModeIndex === 2}"
                        class="group px-8 py-12 bg-gradient-to-br from-purple-500/20 to-pink-600/20 backdrop-blur border-2 border-purple-500 hover:border-purple-400 rounded-3xl transition-all card-hover focus:outline-none focus:ring-4 focus:ring-purple-400"
                    >
                        <div class="text-8xl mb-4 group-hover:animate-bounce">🎪</div>
                        <div class="font-display text-5xl text-purple-400">FREESTYLE</div>
                        <div class="text-xl text-white/60 mt-2">No rules! · Press <kbd class="px-2 py-1 bg-white/20 rounded">3</kbd></div>
                    </button>
                </div>
            </form>

            <div class="text-center">
                <button
                    @click="selectedOpponent = false; selectedOpponentId = null; selectedOpponentName = ''; focusedOpponentIndex = 0;"
                    class="text-2xl text-white/60 hover:text-white transition-colors focus:outline-none focus:ring-4 focus:ring-pink-500 rounded px-3 py-1"
                >
                    ← Pick different opponent
                </button>
            </div>
        </div>
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
        focusedOpponentIndex: 0,
        focusedModeIndex: 0,
        opponentCount: {{ $opponents->count() }},

        selectOpponent(id, name) {
            this.selectedOpponentId = id;
            this.selectedOpponentName = name;
            this.selectedOpponent = true;
            this.focusedModeIndex = 0;
        },

        selectNewOpponent() {
            if (this.newOpponentName.trim().length > 0) {
                this.selectedOpponentId = null;
                this.selectedOpponentName = this.newOpponentName.trim();
                this.selectedOpponent = true;
                this.focusedModeIndex = 0;
            }
        },

        handleKeydown(event) {
            // Tab - focus input
            if (event.key === 'Tab' && !this.selectedOpponent) {
                const input = document.getElementById('newOpponentInput');
                if (input && document.activeElement !== input) {
                    event.preventDefault();
                    input.focus();
                    return;
                }
            }

            // Backspace key
            if (event.key === 'Backspace') {
                event.preventDefault();
                if (document.activeElement === document.getElementById('newOpponentInput')) {
                    document.activeElement.blur();
                } else if (this.selectedOpponent) {
                    this.selectedOpponent = false;
                    this.selectedOpponentId = null;
                    this.selectedOpponentName = '';
                    this.focusedOpponentIndex = 0;
                } else {
                    window.location.href = '{{ route('home') }}';
                }
                return;
            }

            // Opponent selection mode
            if (!this.selectedOpponent) {
                if (!this.opponentCount) return;

                if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'Enter'].includes(event.key)) {
                    event.preventDefault();
                }

                switch(event.key) {
                    case 'ArrowDown':
                        this.focusedOpponentIndex = Math.min(this.focusedOpponentIndex + 3, this.opponentCount - 1);
                        this.scrollToFocusedOpponent();
                        break;
                    case 'ArrowUp':
                        this.focusedOpponentIndex = Math.max(this.focusedOpponentIndex - 3, 0);
                        this.scrollToFocusedOpponent();
                        break;
                    case 'ArrowRight':
                        this.focusedOpponentIndex = (this.focusedOpponentIndex + 1) % this.opponentCount;
                        this.scrollToFocusedOpponent();
                        break;
                    case 'ArrowLeft':
                        this.focusedOpponentIndex = (this.focusedOpponentIndex - 1 + this.opponentCount) % this.opponentCount;
                        this.scrollToFocusedOpponent();
                        break;
                    case 'Enter':
                        const opponentButtons = document.querySelectorAll('[data-opponent-button]');
                        if (opponentButtons[this.focusedOpponentIndex]) {
                            opponentButtons[this.focusedOpponentIndex].click();
                        }
                        break;
                }
            }
            // Mode selection
            else {
                if (['ArrowLeft', 'ArrowRight', 'Enter', '1', '2', '3'].includes(event.key)) {
                    event.preventDefault();
                }

                switch(event.key) {
                    case 'ArrowLeft':
                        this.focusedModeIndex = (this.focusedModeIndex - 1 + 3) % 3;
                        break;
                    case 'ArrowRight':
                        this.focusedModeIndex = (this.focusedModeIndex + 1) % 3;
                        break;
                    case 'Enter':
                        const modeButtons = document.querySelectorAll('[data-mode-button]');
                        if (modeButtons[this.focusedModeIndex]) {
                            modeButtons[this.focusedModeIndex].click();
                        }
                        break;
                    case '1':
                        this.selectedMode = '11';
                        document.querySelector('[data-mode-index="0"]').click();
                        break;
                    case '2':
                        this.selectedMode = '21';
                        document.querySelector('[data-mode-index="1"]').click();
                        break;
                    case '3':
                        this.selectedMode = 'freestyle';
                        document.querySelector('[data-mode-index="2"]').click();
                        break;
                }
            }
        },

        scrollToFocusedOpponent() {
            const buttons = document.querySelectorAll('[data-opponent-button]');
            if (buttons[this.focusedOpponentIndex]) {
                buttons[this.focusedOpponentIndex].scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            }
        }
    }
}
</script>
@endsection
