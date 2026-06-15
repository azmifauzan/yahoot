<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('player_answers', function (Blueprint $table) {
            $table->string('powerup_used')->nullable()->after('streak_bonus');
        });
    }

    public function down(): void
    {
        Schema::table('player_answers', function (Blueprint $table) {
            $table->dropColumn('powerup_used');
        });
    }
};
