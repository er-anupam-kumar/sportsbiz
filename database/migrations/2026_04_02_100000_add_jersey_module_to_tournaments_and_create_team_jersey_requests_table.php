<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->boolean('jersey_module_enabled')->default(false)->after('bidding_type');
        });

        Schema::create('team_jersey_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_id')->nullable()->constrained('players')->nullOnDelete();
            $table->string('player_name');
            $table->string('size', 20);
            $table->string('nickname', 60)->nullable();
            $table->string('jersey_number', 20);
            $table->boolean('additional_jersey_required')->default(false);
            $table->timestamps();

            $table->index(['team_id', 'created_at']);
            $table->index(['tournament_id', 'team_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_jersey_requests');

        Schema::table('tournaments', function (Blueprint $table) {
            $table->dropColumn('jersey_module_enabled');
        });
    }
};
