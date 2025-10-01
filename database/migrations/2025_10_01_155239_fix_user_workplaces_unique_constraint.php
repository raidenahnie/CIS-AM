<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Fix the flawed unique constraint that prevents multiple non-primary workplaces.
     * The current constraint unique(['user_id', 'is_primary']) is wrong because it prevents
     * having multiple workplaces with is_primary = false for the same user.
     * 
     * We need to allow multiple (user_id, false) combinations but only one (user_id, true).
     */
    public function up(): void
    {
        Schema::table('user_workplaces', function (Blueprint $table) {
            // First, drop the problematic unique constraint
            $table->dropUnique(['user_id', 'is_primary']);
            
            // Add a helper column that will be NULL for non-primary workplaces
            // and user_id for primary workplaces
            $table->integer('primary_user_id')->nullable()->after('is_primary');
        });

        // Update existing data: set primary_user_id = user_id where is_primary = 1
        if (Schema::hasTable('user_workplaces')) {
            DB::statement('UPDATE user_workplaces SET primary_user_id = user_id WHERE is_primary = 1');
            DB::statement('UPDATE user_workplaces SET primary_user_id = NULL WHERE is_primary = 0');
            
            // Create unique index on the helper column (allows multiple NULLs)
            DB::statement('CREATE UNIQUE INDEX user_workplaces_primary_unique ON user_workplaces (primary_user_id)');
            
            // Create triggers to maintain the helper column
            DB::statement('
                CREATE TRIGGER user_workplaces_before_insert 
                BEFORE INSERT ON user_workplaces 
                FOR EACH ROW 
                BEGIN 
                    SET NEW.primary_user_id = CASE WHEN NEW.is_primary = 1 THEN NEW.user_id ELSE NULL END; 
                END
            ');
            
            DB::statement('
                CREATE TRIGGER user_workplaces_before_update 
                BEFORE UPDATE ON user_workplaces 
                FOR EACH ROW 
                BEGIN 
                    SET NEW.primary_user_id = CASE WHEN NEW.is_primary = 1 THEN NEW.user_id ELSE NULL END; 
                END
            ');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback: remove triggers, index, and helper column, restore the original constraint
        if (Schema::hasTable('user_workplaces')) {
            DB::statement('DROP TRIGGER IF EXISTS user_workplaces_before_insert');
            DB::statement('DROP TRIGGER IF EXISTS user_workplaces_before_update');
            DB::statement('DROP INDEX user_workplaces_primary_unique ON user_workplaces');
        }
        
        Schema::table('user_workplaces', function (Blueprint $table) {
            $table->dropColumn('primary_user_id');
            $table->unique(['user_id', 'is_primary'], 'user_workplaces_user_id_is_primary_unique');
        });
    }
};
