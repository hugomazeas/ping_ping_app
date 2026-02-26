<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Player extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'elo_rating'];

    protected $casts = [
        'created_at' => 'datetime',
        'elo_rating' => 'integer',
    ];

    public function gamesAsPlayer1(): HasMany
    {
        return $this->hasMany(Game::class, 'player1_id');
    }

    public function gamesAsPlayer2(): HasMany
    {
        return $this->hasMany(Game::class, 'player2_id');
    }

    public function wins(): HasMany
    {
        return $this->hasMany(Game::class, 'winner_id');
    }

    public function allGames()
    {
        return Game::where('player1_id', $this->id)
            ->orWhere('player2_id', $this->id);
    }

    public function completedGames()
    {
        return $this->allGames()->whereNotNull('ended_at')->where('elo_applied', true);
    }

    public function getTotalGamesAttribute(): int
    {
        return $this->completedGames()->count();
    }

    public function getTotalWinsAttribute(): int
    {
        return $this->wins()->whereNotNull('ended_at')->where('elo_applied', true)->count();
    }

    public function getTotalLossesAttribute(): int
    {
        return $this->total_games - $this->total_wins;
    }

    public function getWinRateAttribute(): float
    {
        if ($this->total_games === 0) {
            return 0;
        }
        return round(($this->total_wins / $this->total_games) * 100, 1);
    }

    public function getAverageGameDurationAttribute(): ?int
    {
        $games = $this->completedGames()->get();
        if ($games->isEmpty()) {
            return null;
        }

        $totalSeconds = $games->sum(function ($game) {
            return $game->started_at->diffInSeconds($game->ended_at);
        });

        return (int) round($totalSeconds / $games->count());
    }

    public function headToHead(Player $opponent): array
    {
        $games = Game::where(function ($query) use ($opponent) {
            $query->where('player1_id', $this->id)
                ->where('player2_id', $opponent->id);
        })->orWhere(function ($query) use ($opponent) {
            $query->where('player1_id', $opponent->id)
                ->where('player2_id', $this->id);
        })->whereNotNull('ended_at')->where('elo_applied', true)->get();

        $wins = $games->where('winner_id', $this->id)->count();
        $losses = $games->where('winner_id', $opponent->id)->count();

        return [
            'wins' => $wins,
            'losses' => $losses,
            'total' => $games->count(),
        ];
    }

    public function getCurrentStreakAttribute(): array
    {
        $games = $this->completedGames()
            ->orderBy('ended_at', 'desc')
            ->get();

        if ($games->isEmpty()) {
            return ['type' => null, 'count' => 0];
        }

        $firstGame = $games->first();
        $isWinning = $firstGame->winner_id === $this->id;
        $count = 0;

        foreach ($games as $game) {
            $gameIsWin = $game->winner_id === $this->id;
            if ($gameIsWin === $isWinning) {
                $count++;
            } else {
                break;
            }
        }

        return [
            'type' => $isWinning ? 'win' : 'loss',
            'count' => $count,
        ];
    }

    /**
     * Calculate ELO rating change based on game result
     *
     * @param Player $opponent
     * @param float $score (1 for win, 0.5 for draw, 0 for loss)
     * @param int $kFactor (default 32)
     * @return int The rating change
     */
    public function calculateEloChange(Player $opponent, float $score, int $kFactor = 32): int
    {
        // Expected score formula: 1 / (1 + 10^((opponentRating - playerRating) / 400))
        $expectedScore = 1 / (1 + pow(10, ($opponent->elo_rating - $this->elo_rating) / 400));

        // Rating change: K * (actual score - expected score)
        $ratingChange = round($kFactor * ($score - $expectedScore));

        return (int) $ratingChange;
    }

    /**
     * Update ELO rating after a game
     *
     * @param Player $opponent
     * @param float $score (1 for win, 0.5 for draw, 0 for loss)
     */
    public function updateEloRating(Player $opponent, float $score): void
    {
        $change = $this->calculateEloChange($opponent, $score);
        $this->elo_rating += $change;
        $this->save();
    }
}
