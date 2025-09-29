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
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('workplace_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('attendance_id')->nullable()->constrained()->onDelete('cascade');
            
            $table->enum('action', ['check_in', 'check_out', 'break_start', 'break_end']); // Type of action
            $table->timestamp('timestamp'); // When the action occurred
            
            // Location data
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->integer('accuracy')->nullable(); // GPS accuracy in meters
            $table->string('address')->nullable(); // Reverse geocoded address
            
            // Validation data
            $table->boolean('is_valid_location')->default(true); // Within geofence
            $table->decimal('distance_from_workplace', 8, 2)->nullable(); // Distance in meters
            
            // Device and method information
            $table->enum('method', ['gps', 'manual', 'admin', 'api'])->default('gps');
            $table->string('device_info')->nullable(); // Device/browser information
            $table->string('ip_address', 45)->nullable(); // IP address
            $table->string('user_agent')->nullable(); // User agent string
            
            // Additional data
            $table->text('notes')->nullable(); // Notes or reason for manual entry
            $table->json('metadata')->nullable(); // Additional technical data
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'timestamp']);
            $table->index(['workplace_id', 'timestamp']);
            $table->index(['action', 'timestamp']);
            $table->index('timestamp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};
