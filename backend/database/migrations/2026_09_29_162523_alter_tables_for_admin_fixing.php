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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'employment_status')) {
                $table->string('employment_status')->nullable()->comment('Karyawan Tetap, Kontrak, Bantu')->after('role');
            }
        });

        Schema::table('overtime_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('overtime_requests', 'proof')) {
                $table->string('proof')->nullable()->after('output');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['employment_status']);
        });

        Schema::table('overtime_requests', function (Blueprint $table) {
            $table->dropColumn(['proof']);
        });
    }
};
