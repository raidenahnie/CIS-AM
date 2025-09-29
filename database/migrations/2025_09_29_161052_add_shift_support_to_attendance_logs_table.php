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
        Schema::table('attendance_logs', function (Blueprint $table) {
            // Add shift type to track AM/PM sessions
            $table->enum('shift_type', ['am', 'pm', 'full_day'])->default('full_day')->after('action');
            
            // Add sequence number for tracking multiple check-ins in same day
            $table->integer('sequence')->default(1)->after('shift_type');
            
            // Index for efficient queries
            $table->index(['user_id', 'timestamp', 'shift_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'timestamp', 'shift_type']);
            $table->dropColumn(['shift_type', 'sequence']);
        });
    }
};
