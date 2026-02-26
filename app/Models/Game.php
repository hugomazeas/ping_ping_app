<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Game extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'player1_id',
        'player2_id',
        'player1_score',
        'player2_score',
        'winner_id',
        'mode',
        'started_at',
        'ended_at',
        'current_server_id',
        'serve_count',
        'elo_applied',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'player1_score' => 'integer',
        'player2_score' => 'integer',
        'serve_count' => 'integer',
        'elo_applied' => 'boolean',
    ];

    public function player1(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player1_id');
    }

    public function player2(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player2_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'winner_id');
    }

    public function currentServer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'current_server_id');
    }

    public function getLoserAttribute(): ?Player
    {
        if (!$this->winner_id) {
            return null;
        }
        return $this->winner_id === $this->player1_id ? $this->player2 : $this->player1;
    }

    public function getDurationAttribute(): ?int
    {
        if (!$this->ended_at) {
            return null;
        }
        return $this->started_at->diffInSeconds($this->ended_at);
    }

    public function getDurationFormattedAttribute(): ?string
    {
        $duration = $this->duration;
        if ($duration === null) {
            return null;
        }

        $minutes = floor($duration / 60);
        $seconds = $duration % 60;

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    public function getServeNumberAttribute(): int
    {
        return ($this->serve_count % 2) + 1; // Returns 1 or 2
    }

    public function updateServerBasedOnScore(): void
    {
        $totalScore = $this->player1_score + $this->player2_score;

        // Check if we're in deuce (both players at 10 or above)
        $inDeuce = false;
        if ($this->mode !== 'freestyle') {
            $inDeuce = $this->player1_score >= 10 && $this->player2_score >= 10;
        }

        // In deuce, serve changes every point
        // Otherwise, serve changes every 2 points
        $serveInterval = $inDeuce ? 1 : 2;

        // Determine who should be serving based on total score
        $serverIndex = floor($totalScore / $serveInterval) % 2;
        $this->current_server_id = $serverIndex === 0 ? $this->player1_id : $this->player2_id;

        // Update serve count for display (1st or 2nd serve)
        $this->serve_count = $totalScore % $serveInterval;
    }

    public function isComplete(): bool
    {
        return $this->ended_at !== null;
    }

    public function getWinningScoreAttribute(): int
    {
        return match ($this->mode) {
            '11' => 11,
            '21' => 21,
            default => 0,
        };
    }

    public function checkWinCondition(): ?int
    {
        if ($this->mode === 'freestyle') {
            return null;
        }

        $winScore = $this->winning_score;
        $p1 = $this->player1_score;
        $p2 = $this->player2_score;

        // Check if either player has reached winning score
        if ($p1 >= $winScore || $p2 >= $winScore) {
            // Must win by 2 points (deuce rule)
            $diff = abs($p1 - $p2);
            if ($diff >= 2) {
                return $p1 > $p2 ? $this->player1_id : $this->player2_id;
            }
        }

        return null;
    }

    public function getScoreForPlayer(Player $player): int
    {
        if ($player->id === $this->player1_id) {
            return $this->player1_score;
        }
        if ($player->id === $this->player2_id) {
            return $this->player2_score;
        }
        return 0;
    }

    public function getOpponent(Player $player): ?Player
    {
        if ($player->id === $this->player1_id) {
            return $this->player2;
        }
        if ($player->id === $this->player2_id) {
            return $this->player1;
        }
        return null;
    }
}
