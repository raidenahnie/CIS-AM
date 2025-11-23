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
            $table->timestamps();
        });
        
        // Insert default settings
        DB::table('system_settings')->insert([
            [
                'key' => 'notification_type',
                'value' => 'email',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'sms_api_url',
                'value' => 'https://sms.cisdepedcavite.org/api/send',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'auto_checkout_time',
                'value' => '18:00',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'reminder_time',
                'value' => '16:30',
                'created_at' => now(),
                'updated_at' => now()
            ]
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
