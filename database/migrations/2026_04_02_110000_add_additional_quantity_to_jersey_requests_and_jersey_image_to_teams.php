<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_jersey_requests', function (Blueprint $table) {
            $table->unsignedSmallInteger('additional_jersey_quantity')->nullable()->after('additional_jersey_required');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->string('jersey_image_path')->nullable()->after('logo_path');
        });
    }

    public function down(): void
    {
        Schema::table('team_jersey_requests', function (Blueprint $table) {
            $table->dropColumn('additional_jersey_quantity');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn('jersey_image_path');
        });
    }
};
