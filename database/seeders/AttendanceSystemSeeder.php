<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AttendanceSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample users
        $userId = DB::table('users')->insertGetId([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Create sample workplace
        $workplaceId = DB::table('workplaces')->insertGetId([
            'name' => 'Main Office',
            'address' => '123 Business Street, Makati City, Philippines',
            'latitude' => 14.5995,
            'longitude' => 120.9842,
            'radius' => 100,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Create user-workplace assignment
        DB::table('user_workplaces')->insert([
            'user_id' => $userId,
            'workplace_id' => $workplaceId,
            'role' => 'employee',
            'is_primary' => true,
            'assigned_at' => now(),
            'effective_from' => now()->subDays(30),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Create work schedule
        DB::table('work_schedules')->insert([
            'user_id' => $userId,
            'workplace_id' => $workplaceId,
            'name' => 'Regular Schedule',
            'type' => 'fixed',
            'weekly_schedule' => json_encode([
                'monday' => ['start' => '08:00', 'end' => '17:00'],
                'tuesday' => ['start' => '08:00', 'end' => '17:00'],
                'wednesday' => ['start' => '08:00', 'end' => '17:00'],
                'thursday' => ['start' => '08:00', 'end' => '17:00'],
                'friday' => ['start' => '08:00', 'end' => '17:00'],
                'saturday' => ['start' => '08:00', 'end' => '12:00'],
                'sunday' => null
            ]),
            'monday_start' => '08:00',
            'monday_end' => '17:00',
            'tuesday_start' => '08:00',
            'tuesday_end' => '17:00',
            'wednesday_start' => '08:00',
            'wednesday_end' => '17:00',
            'thursday_start' => '08:00',
            'thursday_end' => '17:00',
            'friday_start' => '08:00',
            'friday_end' => '17:00',
            'saturday_start' => '08:00',
            'saturday_end' => '12:00',
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
            'is_active' => true,
            'effective_from' => now()->subDays(30),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Create sample attendance records for the past week
        for ($i = 7; $i >= 1; $i--) {
            $date = now()->subDays($i);
            
            // Skip weekends for this example
            if ($date->isWeekend()) {
                continue;
            }

            $checkInTime = $date->clone()->setTime(8, rand(0, 30), 0); // Check in between 8:00-8:30
            $checkOutTime = $checkInTime->clone()->addHours(8)->addMinutes(rand(30, 60)); // Work 8.5-9 hours
            
            $attendanceId = DB::table('attendances')->insertGetId([
                'user_id' => $userId,
                'workplace_id' => $workplaceId,
                'date' => $date->format('Y-m-d'),
                'check_in_time' => $checkInTime,
                'check_in_latitude' => 14.5995 + (rand(-5, 5) * 0.0001), // Small variation
                'check_in_longitude' => 120.9842 + (rand(-5, 5) * 0.0001),
                'check_in_accuracy' => rand(3, 10),
                'check_in_address' => 'Main Office Building',
                'check_in_method' => 'gps',
                'check_out_time' => $checkOutTime,
                'check_out_latitude' => 14.5995 + (rand(-5, 5) * 0.0001),
                'check_out_longitude' => 120.9842 + (rand(-5, 5) * 0.0001),
                'check_out_accuracy' => rand(3, 10),
                'check_out_address' => 'Main Office Building',
                'check_out_method' => 'gps',
                'total_hours' => $checkOutTime->diffInMinutes($checkInTime) - 60, // Minus lunch break
                'break_duration' => 60,
                'distance_from_workplace' => rand(5, 25),
                'status' => $checkInTime->format('H:i') > '08:15' ? 'late' : 'present',
                'is_valid_location' => true,
                'is_approved' => true,
                'created_at' => $checkInTime,
                'updated_at' => $checkOutTime
            ]);

            // Create attendance logs for check-in and check-out
            DB::table('attendance_logs')->insert([
                [
                    'user_id' => $userId,
                    'workplace_id' => $workplaceId,
                    'attendance_id' => $attendanceId,
                    'action' => 'check_in',
                    'timestamp' => $checkInTime,
                    'latitude' => 14.5995 + (rand(-5, 5) * 0.0001),
                    'longitude' => 120.9842 + (rand(-5, 5) * 0.0001),
                    'accuracy' => rand(3, 10),
                    'address' => 'Main Office Building',
                    'is_valid_location' => true,
                    'distance_from_workplace' => rand(5, 25),
                    'method' => 'gps',
                    'ip_address' => '192.168.1.' . rand(1, 254),
                    'created_at' => $checkInTime,
                    'updated_at' => $checkInTime
                ],
                [
                    'user_id' => $userId,
                    'workplace_id' => $workplaceId,
                    'attendance_id' => $attendanceId,
                    'action' => 'check_out',
                    'timestamp' => $checkOutTime,
                    'latitude' => 14.5995 + (rand(-5, 5) * 0.0001),
                    'longitude' => 120.9842 + (rand(-5, 5) * 0.0001),
                    'accuracy' => rand(3, 10),
                    'address' => 'Main Office Building',
                    'is_valid_location' => true,
                    'distance_from_workplace' => rand(5, 25),
                    'method' => 'gps',
                    'ip_address' => '192.168.1.' . rand(1, 254),
                    'created_at' => $checkOutTime,
                    'updated_at' => $checkOutTime
                ]
            ]);
        }

        // Add today's check-in (but no check-out yet)
        $todayCheckIn = now()->setTime(8, 15, 0);
        $todayAttendanceId = DB::table('attendances')->insertGetId([
            'user_id' => $userId,
            'workplace_id' => $workplaceId,
            'date' => now()->format('Y-m-d'),
            'check_in_time' => $todayCheckIn,
            'check_in_latitude' => 14.5995,
            'check_in_longitude' => 120.9842,
            'check_in_accuracy' => 5,
            'check_in_address' => 'Main Office Building',
            'check_in_method' => 'gps',
            'distance_from_workplace' => 15,
            'status' => 'present',
            'is_valid_location' => true,
            'is_approved' => true,
            'created_at' => $todayCheckIn,
            'updated_at' => $todayCheckIn
        ]);

        // Add today's check-in log
        DB::table('attendance_logs')->insert([
            'user_id' => $userId,
            'workplace_id' => $workplaceId,
            'attendance_id' => $todayAttendanceId,
            'action' => 'check_in',
            'timestamp' => $todayCheckIn,
            'latitude' => 14.5995,
            'longitude' => 120.9842,
            'accuracy' => 5,
            'address' => 'Main Office Building',
            'is_valid_location' => true,
            'distance_from_workplace' => 15,
            'method' => 'gps',
            'ip_address' => '192.168.1.100',
            'created_at' => $todayCheckIn,
            'updated_at' => $todayCheckIn
        ]);
    }
}
