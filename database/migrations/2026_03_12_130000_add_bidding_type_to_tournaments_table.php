<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            if (! Schema::hasColumn('tournaments', 'bidding_type')) {
                $table->enum('bidding_type', ['admin_only', 'team_open'])
                    ->default('admin_only')
                    ->after('auction_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            if (Schema::hasColumn('tournaments', 'bidding_type')) {
                $table->dropColumn('bidding_type');
            }
        });
    }
};
