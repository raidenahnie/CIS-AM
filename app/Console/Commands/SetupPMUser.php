<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AttendanceLog;
use App\Models\Attendance;
use Carbon\Carbon;

class SetupPMUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:pm-user {userId=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up a user with a fresh PM check-in for testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('userId');
        $today = now()->format('Y-m-d');
        
        $this->info("Setting up User {$userId} with fresh PM check-in...");
        
        // Delete any existing logs for this user today
        $deleted = AttendanceLog::where('user_id', $userId)
            ->whereDate('timestamp', $today)
            ->delete();
            
        if ($deleted > 0) {
            $this->info("Deleted {$deleted} existing logs for today");
        }
        
        // Delete any existing attendance record
        Attendance::where('user_id', $userId)
            ->where('date', $today)
            ->delete();
        
        // Create a fresh PM check-in (at 1:30 PM)
        $checkInTime = now()->setTime(13, 30, 0);
        
        // Create attendance record
        $attendance = Attendance::create([
            'user_id' => $userId,
            'workplace_id' => 1, // Assuming workplace 1 exists
            'date' => $today,
            'check_in_time' => $checkInTime,
            'status' => 'present'
        ]);
        
        // Create check-in log
        $log = AttendanceLog::create([
            'user_id' => $userId,
            'workplace_id' => 1,
            'attendance_id' => $attendance->id,
            'action' => 'check_in',
            'shift_type' => 'pm',
            'sequence' => 1,
            'timestamp' => $checkInTime,
            'latitude' => 14.5995,
            'longitude' => 120.9842,
            'accuracy' => 5,
            'is_valid_location' => true,
            'distance_from_workplace' => 15,
            'method' => 'gps'
        ]);
        
        $this->info("Created PM check-in for User {$userId} at {$checkInTime}");
        $this->info("User should now show: 'Ready to Check Out (PM Shift)'");
        
        return 0;
    }
}
