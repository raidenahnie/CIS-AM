<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, boolean, integer, json
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index('key');
        });
        
        // Insert default settings
        DB::table('system_settings')->insert([
            // Security Settings
            ['key' => 'security_password_expiry', 'value' => '1', 'type' => 'boolean', 'description' => 'Require password change every 90 days', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'security_2fa', 'value' => '0', 'type' => 'boolean', 'description' => 'Two-factor authentication', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'security_session_timeout', 'value' => '1', 'type' => 'boolean', 'description' => 'Session timeout (30 mins)', 'created_at' => now(), 'updated_at' => now()],
            
            // Location Settings
            ['key' => 'location_gps_accuracy', 'value' => '1', 'type' => 'boolean', 'description' => 'High accuracy GPS', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'location_manual_entry', 'value' => '0', 'type' => 'boolean', 'description' => 'Allow manual location entry', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'location_default_radius', 'value' => '100', 'type' => 'integer', 'description' => 'Default radius (meters)', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
