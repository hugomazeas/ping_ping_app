<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player1_id')->constrained('players')->onDelete('cascade');
            $table->foreignId('player2_id')->constrained('players')->onDelete('cascade');
            $table->integer('player1_score')->default(0);
            $table->integer('player2_score')->default(0);
            $table->foreignId('winner_id')->nullable()->constrained('players')->onDelete('cascade');
            $table->enum('mode', ['11', '21', 'freestyle'])->default('11');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
