@extends('layouts.app')

@section('title', 'Ping Pong Tracker')

@section('content')
<div class="flex flex-col items-center justify-center min-h-[85vh]">
    <!-- Animated ping pong ball -->
    <div class="relative mb-6">
        <div class="text-8xl animate-bounce-slow">🏓</div>
    </div>

    <!-- Title -->
    <h1 class="font-display text-6xl md:text-8xl text-center mb-4 rainbow-text">
        PING PONG
    </h1>
    <h2 class="font-display text-4xl md:text-5xl text-center mb-12 text-cyan-400 neon-text">
        TRACKER
    </h2>

    <div class="w-full max-w-2xl">
        <!-- New player input -->
        <form action="{{ route('players.store') }}" method="POST" class="mb-10">
            @csrf
            <div class="flex gap-3">
                <input
                    type="text"
                    name="name"
                    placeholder="🎮 New challenger approaching..."
                    class="flex-1 px-6 py-4 text-lg bg-white/10 backdrop-blur border-3 funky-border rounded-2xl focus:outline-none focus:ring-4 focus:ring-pink-500/50 placeholder-white/50 font-semibold"
                >
                <button
                    type="submit"
                    class="px-8 py-4 text-lg font-bold bg-gradient-to-r from-pink-500 via-purple-500 to-cyan-500 hover:from-pink-600 hover:via-purple-600 hover:to-cyan-600 rounded-2xl transition-all card-hover shadow-lg shadow-purple-500/30"
                >
                    JOIN! 🚀
                </button>
            </div>
        </form>

        <!-- Existing players -->
        @if($players->count() > 0)
            <div class="mb-8">
                <h2 class="font-display text-2xl text-center mb-6 text-yellow-400">
                    ⚡ CHOOSE YOUR FIGHTER ⚡
                </h2>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach($players->sortBy('name') as $index => $player)
                        <form action="{{ route('players.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="name" value="{{ $player->name }}">
                            <button
                                type="submit"
                                class="w-full px-4 py-5 text-lg bg-white/10 backdrop-blur funky-border rounded-2xl card-hover text-center group"
                                style="animation-delay: {{ $index * 0.1 }}s"
                            >
                                <div class="text-3xl mb-2 group-hover:animate-bounce">
                                    {{ ['🎯', '🔥', '⚡', '🌟', '💫', '🎪', '🎨', '🎭'][$index % 8] }}
                                </div>
                                <div class="font-bold text-white truncate">{{ $player->name }}</div>
                                @if($player->total_games > 0)
                                    <div class="text-sm text-cyan-400 font-semibold mt-1">
                                        {{ $player->total_games }} battles
                                    </div>
                                @else
                                    <div class="text-sm text-pink-400 font-semibold mt-1">
                                        Fresh meat! 🍖
                                    </div>
                                @endif
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>
        @else
            <div class="text-center mb-8 p-8 bg-white/5 backdrop-blur rounded-3xl funky-border">
                <div class="text-6xl mb-4 animate-float">🎾</div>
                <p class="text-xl text-white/70">No players yet! Be the first legend!</p>
            </div>
        @endif
    </div>

    <a href="{{ route('stats') }}" class="mt-6 px-8 py-3 bg-white/10 backdrop-blur rounded-full font-bold text-lg hover:bg-white/20 transition-all card-hover funky-border">
        📊 Hall of Fame
    </a>
</div>
@endsection
