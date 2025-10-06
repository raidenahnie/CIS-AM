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
        Schema::create('admin_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->string('action'); // e.g., 'login', 'create_user', 'update_user', 'delete_user', 'create_workplace', etc.
            $table->string('entity_type')->nullable(); // e.g., 'User', 'Workplace', 'AdminAccount'
            $table->unsignedBigInteger('entity_id')->nullable(); // ID of the affected entity
            $table->text('description'); // Human-readable description
            $table->json('changes')->nullable(); // Store before/after changes
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            // Indexes for better query performance
            $table->index('admin_id');
            $table->index('action');
            $table->index(['entity_type', 'entity_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_activity_logs');
    }
};
