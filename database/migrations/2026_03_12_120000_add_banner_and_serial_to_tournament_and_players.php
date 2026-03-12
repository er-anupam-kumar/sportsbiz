<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            if (! Schema::hasColumn('tournaments', 'banner_path')) {
                $table->string('banner_path')->nullable()->after('name');
            }
        });

        Schema::table('players', function (Blueprint $table) {
            if (! Schema::hasColumn('players', 'serial_no')) {
                $table->unsignedInteger('serial_no')->nullable()->after('name');
                $table->unique(['tournament_id', 'serial_no']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            if (Schema::hasColumn('players', 'serial_no')) {
                $table->dropUnique('players_tournament_id_serial_no_unique');
                $table->dropColumn('serial_no');
            }
        });

        Schema::table('tournaments', function (Blueprint $table) {
            if (Schema::hasColumn('tournaments', 'banner_path')) {
                $table->dropColumn('banner_path');
            }
        });
    }
};
