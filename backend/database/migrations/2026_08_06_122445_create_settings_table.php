<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->nullable();
            $table->string('logo')->nullable();
            $table->string('office_location')->nullable();
            $table->string('office_lat')->nullable();
            $table->string('office_long')->nullable();
            $table->integer('attendance_radius')->default(50)->comment('Radius in meters');
            $table->time('default_time_in')->nullable();
            $table->time('default_time_out')->nullable();
            $table->string('timezone')->default('Asia/Jakarta');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
