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
        Schema::create('workplaces', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Workplace name (e.g., 'Main Office', 'Branch A')
            $table->text('address')->nullable(); // Full address
            $table->decimal('latitude', 10, 8); // GPS latitude with high precision
            $table->decimal('longitude', 11, 8); // GPS longitude with high precision
            $table->integer('radius')->default(100); // Check-in radius in meters
            $table->boolean('is_active')->default(true); // Active status
            $table->json('additional_settings')->nullable(); // JSON for extra settings
            $table->timestamps();
            
            // Add indexes for better performance
            $table->index(['latitude', 'longitude']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workplaces');
    }
};
