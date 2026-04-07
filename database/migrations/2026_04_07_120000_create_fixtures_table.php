<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixtures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();
            $table->foreignId('home_team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('away_team_id')->constrained('teams')->cascadeOnDelete();
            $table->dateTime('match_at');
            $table->string('venue', 255)->nullable();
            $table->string('match_label', 120)->nullable();
            $table->enum('status', ['scheduled', 'live', 'completed', 'postponed', 'cancelled'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['admin_id', 'tournament_id', 'match_at']);
            $table->index(['home_team_id', 'away_team_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixtures');
    }
};
