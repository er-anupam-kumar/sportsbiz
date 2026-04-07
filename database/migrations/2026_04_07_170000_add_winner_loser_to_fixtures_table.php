<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fixtures', function (Blueprint $table) {
            $table->foreignId('winner_team_id')
                ->nullable()
                ->after('away_team_id')
                ->constrained('teams')
                ->nullOnDelete();

            $table->foreignId('loser_team_id')
                ->nullable()
                ->after('winner_team_id')
                ->constrained('teams')
                ->nullOnDelete();

            $table->index(['winner_team_id', 'loser_team_id']);
        });
    }

    public function down(): void
    {
        Schema::table('fixtures', function (Blueprint $table) {
            $table->dropIndex(['winner_team_id', 'loser_team_id']);
            $table->dropConstrainedForeignId('winner_team_id');
            $table->dropConstrainedForeignId('loser_team_id');
        });
    }
};
