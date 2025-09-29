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
        Schema::create('work_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('workplace_id')->nullable()->constrained()->onDelete('set null');
            
            $table->string('name')->default('Regular Schedule'); // Schedule name
            $table->enum('type', ['fixed', 'flexible', 'shift'])->default('fixed');
            
            // Daily schedule (JSON format for flexibility)
            // Example: {"monday": {"start": "08:00", "end": "17:00", "break_start": "12:00", "break_end": "13:00"}}
            $table->json('weekly_schedule'); 
            
            // Alternative: Individual day columns
            $table->time('monday_start')->nullable();
            $table->time('monday_end')->nullable();
            $table->time('tuesday_start')->nullable();
            $table->time('tuesday_end')->nullable();
            $table->time('wednesday_start')->nullable();
            $table->time('wednesday_end')->nullable();
            $table->time('thursday_start')->nullable();
            $table->time('thursday_end')->nullable();
            $table->time('friday_start')->nullable();
            $table->time('friday_end')->nullable();
            $table->time('saturday_start')->nullable();
            $table->time('saturday_end')->nullable();
            $table->time('sunday_start')->nullable();
            $table->time('sunday_end')->nullable();
            
            // Break times
            $table->time('break_start')->default('12:00:00');
            $table->time('break_end')->default('13:00:00');
            $table->integer('break_duration')->default(60); // Break duration in minutes
            
            // Overtime and flexibility settings
            $table->integer('overtime_threshold')->default(480); // Minutes before overtime kicks in (8 hours)
            $table->integer('late_tolerance')->default(15); // Grace period for late arrival in minutes
            $table->integer('early_departure_tolerance')->default(15); // Grace period for early departure
            
            // Status and dates
            $table->boolean('is_active')->default(true);
            $table->date('effective_from')->useCurrent();
            $table->date('effective_until')->nullable();
            
            // Timezone support
            $table->string('timezone')->default('Asia/Manila');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'is_active']);
            $table->index(['workplace_id', 'effective_from']);
            $table->index(['effective_from', 'effective_until']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_schedules');
    }
};
