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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('workplace_id')->constrained()->onDelete('cascade');
            $table->date('date'); // Date of attendance
            
            // Check-in information
            $table->timestamp('check_in_time')->nullable();
            $table->decimal('check_in_latitude', 10, 8)->nullable();
            $table->decimal('check_in_longitude', 11, 8)->nullable();
            $table->integer('check_in_accuracy')->nullable(); // GPS accuracy in meters
            $table->string('check_in_address')->nullable(); // Reverse geocoded address
            $table->enum('check_in_method', ['gps', 'manual', 'admin'])->default('gps');
            
            // Check-out information
            $table->timestamp('check_out_time')->nullable();
            $table->decimal('check_out_latitude', 10, 8)->nullable();
            $table->decimal('check_out_longitude', 11, 8)->nullable();
            $table->integer('check_out_accuracy')->nullable(); // GPS accuracy in meters
            $table->string('check_out_address')->nullable(); // Reverse geocoded address
            $table->enum('check_out_method', ['gps', 'manual', 'admin'])->default('gps');
            
            // Calculated fields
            $table->integer('total_hours')->nullable(); // Total work hours in minutes
            $table->integer('break_duration')->nullable(); // Break time in minutes
            $table->decimal('distance_from_workplace', 8, 2)->nullable(); // Distance in meters
            
            // Status and validation
            $table->enum('status', ['present', 'late', 'absent', 'partial', 'remote'])->default('present');
            $table->boolean('is_valid_location')->default(true); // Was location within geofence
            $table->boolean('requires_approval')->default(false); // Needs manager approval
            $table->boolean('is_approved')->default(true); // Admin/manager approval status
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            
            // Additional data
            $table->text('notes')->nullable(); // Employee or admin notes
            $table->json('metadata')->nullable(); // Additional data (device info, etc.)
            
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['user_id', 'date']);
            $table->index(['workplace_id', 'date']);
            $table->index(['date', 'status']);
            $table->index('check_in_time');
            $table->unique(['user_id', 'date']); // One attendance record per user per day
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
