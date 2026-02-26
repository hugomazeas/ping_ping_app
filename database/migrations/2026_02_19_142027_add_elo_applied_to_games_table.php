<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->boolean('elo_applied')->default(false)->after('serve_count');
        });

        // Backfill: Mark all games completed on or after ELO migration as elo_applied
        // ELO system was added on 2026-02-16
        DB::table('games')
            ->whereNotNull('ended_at')
            ->where('ended_at', '>=', '2026-02-16 00:00:00')
            ->update(['elo_applied' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn('elo_applied');
        });
    }
};
