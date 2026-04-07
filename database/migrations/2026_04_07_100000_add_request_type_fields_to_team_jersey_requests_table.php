<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_jersey_requests', function (Blueprint $table) {
            $table->string('request_for', 20)->default('player')->after('player_id');
            $table->string('staff_name', 120)->nullable()->after('player_name');
        });

        DB::table('team_jersey_requests')
            ->whereNull('request_for')
            ->update(['request_for' => 'player']);
    }

    public function down(): void
    {
        Schema::table('team_jersey_requests', function (Blueprint $table) {
            $table->dropColumn(['request_for', 'staff_name']);
        });
    }
};
