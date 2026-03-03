<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tournaments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('sport_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('purse_amount', 14, 2)->default(0);
            $table->unsignedInteger('max_players_per_team')->default(15);
            $table->json('category_limits')->nullable();
            $table->decimal('base_increment', 14, 2)->default(1000);
            $table->unsignedInteger('auction_timer_seconds')->default(30);
            $table->boolean('anti_sniping')->default(true);
            $table->enum('auction_type', ['live', 'silent'])->default('live');
            $table->enum('status', ['draft', 'active', 'paused', 'completed'])->default('draft');
            $table->timestamp('starts_at')->nullable();
            $table->timestamps();

            $table->index(['admin_id', 'status']);
            $table->unique(['admin_id', 'name']);
        });

        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('logo_path')->nullable();
            $table->decimal('wallet_balance', 14, 2)->default(0);
            $table->unsignedInteger('squad_count')->default(0);
            $table->boolean('is_locked')->default(false);
            $table->timestamps();

            $table->index(['admin_id', 'tournament_id']);
            $table->unique(['tournament_id', 'name']);
        });

        Schema::create('player_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('max_per_team')->default(99);
            $table->timestamps();

            $table->unique(['tournament_id', 'name']);
        });

        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('player_categories')->nullOnDelete();
            $table->string('name');
            $table->decimal('base_price', 14, 2)->default(0);
            $table->string('image_path')->nullable();
            $table->json('stats')->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->string('country', 100)->nullable();
            $table->string('previous_team')->nullable();
            $table->enum('status', ['available', 'sold', 'unsold', 'retained', 'withdrawn'])->default('available');
            $table->foreignId('sold_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->decimal('final_price', 14, 2)->nullable();
            $table->timestamps();

            $table->index(['admin_id', 'tournament_id', 'status']);
        });

        Schema::create('auctions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();
            $table->foreignId('current_player_id')->nullable()->constrained('players')->nullOnDelete();
            $table->foreignId('current_highest_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->decimal('current_bid', 14, 2)->default(0);
            $table->boolean('is_paused')->default(false);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('last_bid_at')->nullable();
            $table->timestamps();

            $table->unique('tournament_id');
            $table->index(['tournament_id', 'is_paused', 'ends_at']);
        });

        Schema::create('bids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 14, 2);
            $table->boolean('is_auto_bid')->default(false);
            $table->timestamps();

            $table->index(['tournament_id', 'player_id', 'created_at']);
            $table->index(['team_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bids');
        Schema::dropIfExists('auctions');
        Schema::dropIfExists('players');
        Schema::dropIfExists('player_categories');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('tournaments');
    }
};
