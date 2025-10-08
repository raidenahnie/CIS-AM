<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SystemSetting;

class ManualEntryCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Set default manual entry code if it doesn't exist
        $existingCode = SystemSetting::where('key', 'manual_entry_code')->first();
        
        if (!$existingCode) {
            SystemSetting::create([
                'key' => 'manual_entry_code',
                'value' => 'DEPED2025',
                'type' => 'string',
                'description' => 'Access code required for manual GPS location entry'
            ]);
            
            $this->command->info('Manual entry code set to: DEPED2025');
        } else {
            $this->command->info('Manual entry code already exists: ' . $existingCode->value);
        }
    }
}
