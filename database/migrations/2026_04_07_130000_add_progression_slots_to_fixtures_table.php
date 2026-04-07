<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fixtures', function (Blueprint $table) {
            $table->dropForeign(['home_team_id']);
            $table->dropForeign(['away_team_id']);

            $table->unsignedBigInteger('home_team_id')->nullable()->change();
            $table->unsignedBigInteger('away_team_id')->nullable()->change();

            $table->foreign('home_team_id')->references('id')->on('teams')->nullOnDelete();
            $table->foreign('away_team_id')->references('id')->on('teams')->nullOnDelete();

            $table->enum('home_source_type', ['team', 'winner_of', 'loser_of', 'tbd'])->default('team')->after('away_team_id');
            $table->enum('away_source_type', ['team', 'winner_of', 'loser_of', 'tbd'])->default('team')->after('home_source_type');

            $table->foreignId('home_source_fixture_id')->nullable()->after('away_source_type')->constrained('fixtures')->nullOnDelete();
            $table->foreignId('away_source_fixture_id')->nullable()->after('home_source_fixture_id')->constrained('fixtures')->nullOnDelete();

            $table->string('home_slot_label', 120)->nullable()->after('away_source_fixture_id');
            $table->string('away_slot_label', 120)->nullable()->after('home_slot_label');
        });
    }

    public function down(): void
    {
        Schema::table('fixtures', function (Blueprint $table) {
            $table->dropForeign(['home_source_fixture_id']);
            $table->dropForeign(['away_source_fixture_id']);
            $table->dropColumn([
                'home_source_type',
                'away_source_type',
                'home_source_fixture_id',
                'away_source_fixture_id',
                'home_slot_label',
                'away_slot_label',
            ]);

            $table->dropForeign(['home_team_id']);
            $table->dropForeign(['away_team_id']);
            $table->unsignedBigInteger('home_team_id')->nullable(false)->change();
            $table->unsignedBigInteger('away_team_id')->nullable(false)->change();
            $table->foreign('home_team_id')->references('id')->on('teams')->cascadeOnDelete();
            $table->foreign('away_team_id')->references('id')->on('teams')->cascadeOnDelete();
        });
    }
};
