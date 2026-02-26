<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->unsignedBigInteger('current_server_id')->nullable()->after('winner_id');
            $table->foreign('current_server_id')->references('id')->on('players')->onDelete('set null');
            $table->integer('serve_count')->default(0)->after('current_server_id');
        });
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropForeign(['current_server_id']);
            $table->dropColumn(['current_server_id', 'serve_count']);
        });
    }
};
