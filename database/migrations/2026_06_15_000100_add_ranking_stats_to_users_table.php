<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->unsignedBigInteger('total_xp')->default(0)->after('is_admin');
            $table->unsignedInteger('games_played')->default(0)->after('total_xp');
            $table->unsignedInteger('games_won')->default(0)->after('games_played');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['total_xp', 'games_played', 'games_won']);
        });
    }
};
