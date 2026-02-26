@extends('layouts.app')

@section('title', 'Settings - Ping Pong Tracker')

@section('content')
<div class="h-full flex flex-col" x-data="{}" @keydown.window.backspace="window.location.href = '{{ route('home') }}'">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('home') }}" class="text-2xl text-white/60 hover:text-white transition-colors inline-block mb-3 focus:outline-none focus:ring-4 focus:ring-pink-500 rounded px-2">
                ← Back to Home (Press <kbd class="px-2 py-1 bg-white/20 rounded">Backspace</kbd>)
            </a>
            <h1 class="font-display text-7xl text-yellow-400 neon-text">
                ⚙️ SETTINGS
            </h1>
        </div>

        <!-- Keyboard Shortcuts Help -->
        <div class="bg-white/10 backdrop-blur rounded-2xl px-6 py-3 funky-border">
            <div class="text-lg font-bold text-yellow-400 mb-1">⌨️ KEYBOARD</div>
            <div class="text-base text-white/80">
                <div><kbd class="px-2 py-1 bg-white/20 rounded">Backspace</kbd> Back to Home</div>
            </div>
        </div>
    </div>

    <!-- Settings Content -->
    <div class="flex-1 overflow-y-auto">
        <div class="max-w-4xl">
            <!-- Database Management Section -->
            <div class="bg-white/10 backdrop-blur rounded-3xl p-8 mb-6 funky-border">
                <h2 class="font-display text-4xl text-cyan-400 mb-6">🗄️ DATABASE MANAGEMENT</h2>

                <div class="space-y-4">
                    <!-- Cleanup Incomplete Games -->
                    @if($incompleteCount > 0)
                        <div class="bg-white/5 rounded-2xl p-6 border-2 border-yellow-500/50">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-3xl font-bold text-white mb-2">Cleanup Incomplete Games</h3>
                                    <p class="text-xl text-white/60">Found {{ $incompleteCount }} abandoned/incomplete game(s)</p>
                                </div>
                                <form action="{{ route('settings.cleanup-incomplete') }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="px-8 py-4 text-3xl font-bold bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-yellow-600 hover:to-orange-600 rounded-2xl transition-all card-hover shadow-lg shadow-yellow-500/30 focus:outline-none focus:ring-4 focus:ring-yellow-400"
                                    >
                                        🧹 CLEANUP
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif

                    <!-- Clear All Games -->
                    <div class="bg-white/5 rounded-2xl p-6 border-2 border-red-500/50">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-3xl font-bold text-white mb-2">Clear All Games</h3>
                                <p class="text-xl text-white/60">Delete all game history. Players will be preserved.</p>
                            </div>
                            <form action="{{ route('settings.clear-games') }}" method="POST" onsubmit="return confirm('⚠️ Are you SURE you want to delete ALL games?\n\nThis will:\n✓ Delete all game history\n✓ Reset all player stats\n✓ Keep all players\n\nThis action CANNOT be undone!');">
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="px-8 py-4 text-3xl font-bold bg-gradient-to-r from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700 rounded-2xl transition-all card-hover shadow-lg shadow-red-500/30 focus:outline-none focus:ring-4 focus:ring-red-400"
                                >
                                    🗑️ CLEAR GAMES
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Clear All Players -->
                    <div class="bg-white/5 rounded-2xl p-6 border-2 border-orange-500/50">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-3xl font-bold text-white mb-2">Clear All Players & Games</h3>
                                <p class="text-xl text-white/60">Delete everything and start fresh.</p>
                            </div>
                            <form action="{{ route('settings.clear-all') }}" method="POST" onsubmit="return confirm('🚨 DANGER! Are you ABSOLUTELY SURE?\n\nThis will DELETE EVERYTHING:\n✗ All players\n✗ All games\n✗ All statistics\n\nThis action CANNOT be undone!');">
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="px-8 py-4 text-3xl font-bold bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 rounded-2xl transition-all card-hover shadow-lg shadow-orange-500/30 focus:outline-none focus:ring-4 focus:ring-orange-400"
                                >
                                    ⚠️ CLEAR ALL
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="bg-white/10 backdrop-blur rounded-3xl p-8 funky-border">
                <h2 class="font-display text-4xl text-purple-400 mb-6">📊 DATABASE STATS</h2>

                <div class="grid grid-cols-2 gap-6">
                    <div class="bg-white/5 rounded-2xl p-6 text-center">
                        <div class="text-6xl mb-2">👥</div>
                        <div class="text-5xl font-bold text-white">{{ $playerCount }}</div>
                        <div class="text-xl text-white/60 mt-2">Total Players</div>
                    </div>

                    <div class="bg-white/5 rounded-2xl p-6 text-center">
                        <div class="text-6xl mb-2">🎮</div>
                        <div class="text-5xl font-bold text-white">{{ $gameCount }}</div>
                        <div class="text-xl text-white/60 mt-2">Total Games</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
