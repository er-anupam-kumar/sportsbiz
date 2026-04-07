<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fixtures', function (Blueprint $table) {
            $table->unsignedInteger('home_points')->nullable()->after('status');
            $table->unsignedInteger('away_points')->nullable()->after('home_points');
            $table->unsignedTinyInteger('current_innings')->nullable()->after('away_points');
            $table->json('score_payload')->nullable()->after('current_innings');
            $table->string('result_text', 255)->nullable()->after('score_payload');
        });
    }

    public function down(): void
    {
        Schema::table('fixtures', function (Blueprint $table) {
            $table->dropColumn([
                'home_points',
                'away_points',
                'current_innings',
                'score_payload',
                'result_text',
            ]);
        });
    }
};
