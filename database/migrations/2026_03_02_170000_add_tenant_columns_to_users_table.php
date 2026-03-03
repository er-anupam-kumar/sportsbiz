<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('parent_admin_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            $table->string('phone', 30)->nullable()->after('email');
            $table->enum('status', ['active', 'suspended'])->default('active')->after('password');
            $table->index(['parent_admin_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['parent_admin_id', 'status']);
            $table->dropConstrainedForeignId('parent_admin_id');
            $table->dropColumn(['phone', 'status']);
        });
    }
};
