<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->string('category')->nullable()->after('title');
            $table->foreignId('division_id')->nullable()->after('category')->constrained('divisions')->nullOnDelete();
        });

        Schema::table('overtime_requests', function (Blueprint $table) {
            $table->text('output')->nullable()->after('reason');
            $table->string('status_pengerjaan')->default('pending')->after('status');
        });

        // The 'type' in leave_requests is a simple string. We don't need to alter enum unless it was enum. Let's check original migration.
        // Original migration had: $table->string('type')->comment('annual, sick, permission');
        // No enum constraint at DB level. So we don't need to alter it here, just in validation.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropForeign(['division_id']);
            $table->dropColumn(['category', 'division_id']);
        });

        Schema::table('overtime_requests', function (Blueprint $table) {
            $table->dropColumn(['output', 'status_pengerjaan']);
        });
    }
};
