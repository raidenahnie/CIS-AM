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
        // MySQL doesn't support adding values to ENUM easily, so we need to use raw SQL
        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('present', 'late', 'absent', 'partial', 'remote', 'special') DEFAULT 'present'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('present', 'late', 'absent', 'partial', 'remote') DEFAULT 'present'");
    }
};
