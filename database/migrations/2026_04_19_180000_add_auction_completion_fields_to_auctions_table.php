<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auctions', function (Blueprint $table) {
            $table->boolean('is_completed')->default(false)->after('is_paused');
            $table->timestamp('completed_at')->nullable()->after('last_bid_at');
            $table->index(['tournament_id', 'is_completed']);
        });
    }

    public function down(): void
    {
        Schema::table('auctions', function (Blueprint $table) {
            $table->dropIndex(['tournament_id', 'is_completed']);
            $table->dropColumn(['is_completed', 'completed_at']);
        });
    }
};
