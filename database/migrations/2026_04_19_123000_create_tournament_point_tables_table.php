<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tournament_point_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->nullable();
            $table->unsignedInteger('played')->default(0);
            $table->unsignedInteger('won')->default(0);
            $table->unsignedInteger('lost')->default(0);
            $table->unsignedInteger('tied')->default(0);
            $table->unsignedInteger('no_result')->default(0);
            $table->unsignedInteger('points')->default(0);
            $table->decimal('net_run_rate', 8, 3)->nullable();
            $table->timestamps();

            $table->unique(['tournament_id', 'team_id']);
            $table->index(['tournament_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournament_point_tables');
    }
};
