<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->foreignId('captain_player_id')
                ->nullable()
                ->after('secondary_color')
                ->constrained('players')
                ->nullOnDelete();

            $table->foreignId('wicketkeeper_player_id')
                ->nullable()
                ->after('captain_player_id')
                ->constrained('players')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropConstrainedForeignId('captain_player_id');
            $table->dropConstrainedForeignId('wicketkeeper_player_id');
        });
    }
};
