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
        Schema::create('user_workplaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('workplace_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['employee', 'admin', 'manager'])->default('employee');
            $table->boolean('is_primary')->default(false); // Is this the user's primary workplace
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('effective_from')->nullable(); // When assignment becomes effective
            $table->timestamp('effective_until')->nullable(); // When assignment expires
            $table->timestamps();
            
            // Ensure one user can't have multiple primary workplaces
            $table->unique(['user_id', 'is_primary']);
            // Prevent duplicate user-workplace assignments
            $table->unique(['user_id', 'workplace_id']);
            
            // Add indexes
            $table->index(['user_id', 'is_primary']);
            $table->index('workplace_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_workplaces');
    }
};
