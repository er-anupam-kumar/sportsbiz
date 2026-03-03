<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->timestamp('trade_window_ends_at')->nullable()->after('starts_at');
            $table->index('trade_window_ends_at');
        });
    }

    public function down(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->dropIndex(['trade_window_ends_at']);
            $table->dropColumn('trade_window_ends_at');
        });
    }
};
