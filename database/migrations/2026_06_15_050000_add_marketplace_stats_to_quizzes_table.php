<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table): void {
            $table->unsignedInteger('plays_count')->default(0)->after('settings');
            $table->unsignedInteger('duplicates_count')->default(0)->after('plays_count');
            $table->boolean('featured')->default(false)->after('duplicates_count');
        });
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table): void {
            $table->dropColumn(['plays_count', 'duplicates_count', 'featured']);
        });
    }
};
