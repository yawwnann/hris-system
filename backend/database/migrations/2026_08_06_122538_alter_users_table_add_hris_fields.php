<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nik')->unique()->nullable()->after('id');
            $table->string('phone')->nullable()->after('email');
            $table->text('address')->nullable()->after('phone');
            $table->date('dob')->nullable()->after('address');
            $table->string('gender')->nullable()->after('dob');
            $table->foreignId('division_id')->nullable()->constrained()->nullOnDelete()->after('gender');
            $table->foreignId('position_id')->nullable()->constrained()->nullOnDelete()->after('division_id');
            $table->foreignId('shift_id')->nullable()->constrained()->nullOnDelete()->after('position_id');
            $table->string('status')->default('active')->comment('active, inactive')->after('shift_id');
            $table->date('join_date')->nullable()->after('status');
            $table->string('photo')->nullable()->after('join_date');
            $table->string('role')->default('employee')->comment('admin, employee')->after('photo');
            $table->integer('leave_quota')->default(12)->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['division_id']);
            $table->dropForeign(['position_id']);
            $table->dropForeign(['shift_id']);
            $table->dropColumn([
                'nik', 'phone', 'address', 'dob', 'gender', 
                'division_id', 'position_id', 'shift_id', 
                'status', 'join_date', 'photo', 'role', 'leave_quota'
            ]);
        });
    }
};
