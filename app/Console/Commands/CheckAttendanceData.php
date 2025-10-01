<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AttendanceLog;
use Carbon\Carbon;

class CheckAttendanceData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:attendance-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check current attendance log data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now()->format('Y-m-d');
        $this->info("Checking attendance data for today: {$today}");
        
        // Get all logs for today
        $todaysLogs = AttendanceLog::whereDate('timestamp', $today)->orderBy('timestamp')->get();
        
        $this->info("Found " . $todaysLogs->count() . " logs for today:");
        
        foreach ($todaysLogs as $log) {
            $this->info("ID: {$log->id}, User: {$log->user_id}, Action: {$log->action}, Shift: {$log->shift_type}, Time: {$log->timestamp}");
        }
        
        // Also check all recent logs
        $this->info("\nAll recent logs (last 10):");
        $recentLogs = AttendanceLog::orderBy('timestamp', 'desc')->limit(10)->get();
        
        foreach ($recentLogs as $log) {
            $date = Carbon::parse($log->timestamp)->format('Y-m-d');
            $this->info("ID: {$log->id}, User: {$log->user_id}, Action: {$log->action}, Shift: {$log->shift_type}, Date: {$date}, Time: {$log->timestamp}");
        }
        
        return 0;
    }
}
