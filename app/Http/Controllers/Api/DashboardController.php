<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Workplace;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function getUserStats($userId = 1)
    {
        $user = User::find($userId);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $currentMonth = now()->startOfMonth();
        $today = now()->format('Y-m-d');

        // Get user's attendances for current month
        $attendances = Attendance::where('user_id', $userId)
            ->where('date', '>=', $currentMonth)
            ->get();

        $totalDays = $attendances->count();
        $presentDays = $attendances->where('status', '!=', 'absent')->count();
        $attendanceRate = $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) : 0;

        // Calculate average check-in time
        $checkInTimes = $attendances->whereNotNull('check_in_time');
        $avgCheckInMinutes = $checkInTimes->avg(function ($attendance) {
            return $attendance->check_in_time ? $attendance->check_in_time->hour * 60 + $attendance->check_in_time->minute : null;
        });
        
        $avgCheckIn = $avgCheckInMinutes ? sprintf('%02d:%02d AM', floor($avgCheckInMinutes / 60), $avgCheckInMinutes % 60) : '8:00 AM';

        // Get today's attendance
        $todayAttendance = Attendance::where('user_id', $userId)
            ->where('date', $today)
            ->first();

        $todayHours = '0.0 hrs';
        $currentStatus = 'Not checked in';
        
        if ($todayAttendance) {
            if ($todayAttendance->check_out_time) {
                $hours = $todayAttendance->total_hours ? round($todayAttendance->total_hours / 60, 1) : 0;
                $todayHours = $hours . ' hrs';
                $currentStatus = 'Completed';
            } elseif ($todayAttendance->check_in_time) {
                $minutes = now()->diffInMinutes($todayAttendance->check_in_time);
                $hours = round($minutes / 60, 1);
                $todayHours = $hours . ' hrs';
                $currentStatus = 'Currently checked in';
            }
        }

        return response()->json([
            'days_present_this_month' => $presentDays,
            'attendance_rate' => $attendanceRate,
            'average_checkin_time' => $avgCheckIn,
            'today_hours' => $todayHours,
            'current_status' => $currentStatus,
            'user' => $user->name
        ]);
    }

    public function getAttendanceHistory($userId = 1)
    {
        $attendances = Attendance::where('user_id', $userId)
            ->with('workplace')
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($attendance) {
                return [
                    'id' => $attendance->id,
                    'date' => $attendance->date->format('M j, Y'),
                    'date_raw' => $attendance->date->format('Y-m-d'),
                    'check_in' => $attendance->check_in_time ? $attendance->check_in_time->format('g:i A') : null,
                    'check_out' => $attendance->check_out_time ? $attendance->check_out_time->format('g:i A') : 'Still working',
                    'total_hours' => $attendance->total_hours ? round($attendance->total_hours / 60, 1) . ' hrs' : '0 hrs',
                    'location' => $attendance->workplace ? $attendance->workplace->name : 'Unknown',
                    'status' => ucfirst($attendance->status),
                    'status_class' => $this->getStatusClass($attendance->status)
                ];
            });

        return response()->json($attendances);
    }

    public function getUserWorkplace($userId = 1)
    {
        $userWorkplace = DB::table('user_workplaces')
            ->join('workplaces', 'user_workplaces.workplace_id', '=', 'workplaces.id')
            ->where('user_workplaces.user_id', $userId)
            ->where('user_workplaces.is_primary', true)
            ->select('workplaces.*')
            ->first();

        if (!$userWorkplace) {
            return response()->json(['error' => 'No workplace configured'], 404);
        }

        return response()->json([
            'id' => $userWorkplace->id,
            'name' => $userWorkplace->name,
            'address' => $userWorkplace->address,
            'latitude' => (float)$userWorkplace->latitude,
            'longitude' => (float)$userWorkplace->longitude,
            'radius' => $userWorkplace->radius
        ]);
    }

    public function checkIn(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'accuracy' => 'nullable|numeric'
        ]);

        $userId = $request->user_id;
        $today = now()->format('Y-m-d');
        $currentTime = now();

        // Get user's workplace
        $workplace = DB::table('user_workplaces')
            ->join('workplaces', 'user_workplaces.workplace_id', '=', 'workplaces.id')
            ->where('user_workplaces.user_id', $userId)
            ->where('user_workplaces.is_primary', true)
            ->select('workplaces.*')
            ->first();

        if (!$workplace) {
            return response()->json([
                'error' => 'No workplace configured. Please set up your workplace first.',
                'redirect' => 'workplace-setup'
            ], 400);
        }

        // Calculate distance
        $distance = $this->calculateDistance(
            $request->latitude, 
            $request->longitude,
            $workplace->latitude,
            $workplace->longitude
        );

        $isValidLocation = $distance <= $workplace->radius;
        
        if (!$isValidLocation) {
            return response()->json([
                'error' => "You are {$distance}m away from your workplace. You must be within {$workplace->radius}m to check in.",
                'distance' => round($distance),
                'required_radius' => $workplace->radius
            ], 400);
        }

        // Get today's attendance logs to determine current status
        $todaysLogs = AttendanceLog::forUser($userId)
            ->forDate($today)
            ->orderBy('timestamp')
            ->get();

        // Determine what action to take based on current logs
        $actionResult = $this->determineNextAction($todaysLogs);
        
        if ($actionResult['error']) {
            return response()->json(['error' => $actionResult['error']], 400);
        }

        // Create or get today's attendance record
        $attendance = Attendance::firstOrCreate(
            ['user_id' => $userId, 'date' => $today],
            [
                'workplace_id' => $workplace->id,
                'status' => 'present'
            ]
        );

        // Create attendance log entry
        $log = AttendanceLog::create([
            'user_id' => $userId,
            'workplace_id' => $workplace->id,
            'attendance_id' => $attendance->id,
            'action' => $actionResult['action'],
            'shift_type' => $actionResult['shift_type'],
            'sequence' => $actionResult['sequence'],
            'timestamp' => $currentTime,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'accuracy' => $request->accuracy,
            'is_valid_location' => $isValidLocation,
            'distance_from_workplace' => round($distance),
            'method' => 'gps',
            'ip_address' => $request->ip()
        ]);

        // Update attendance record with first check-in time if this is the first action
        if ($actionResult['action'] === 'check_in' && !$attendance->check_in_time) {
            $attendance->update(['check_in_time' => $currentTime]);
        }

        return response()->json([
            'message' => $this->getActionMessage($actionResult['action'], $actionResult['shift_type']),
            'action' => $actionResult['action'],
            'shift_type' => $actionResult['shift_type'],
            'sequence' => $actionResult['sequence'],
            'next_action' => $this->getNextActionText($todaysLogs, $actionResult),
            'attendance_log' => $log,
            'distance' => round($distance),
            'is_valid_location' => $isValidLocation
        ]);
    }

    private function getStatusClass($status)
    {
        return match($status) {
            'present' => 'bg-green-100 text-green-800',
            'late' => 'bg-yellow-100 text-yellow-800', 
            'absent' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng/2) * sin($dLng/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $earthRadius * $c;
    }

    private function determineNextAction($todaysLogs)
    {
        $logCount = $todaysLogs->count();
        
        // No logs yet - first check-in of the day (AM shift start)
        if ($logCount === 0) {
            return [
                'action' => 'check_in',
                'shift_type' => 'am',
                'sequence' => 1,
                'error' => false
            ];
        }

        $lastLog = $todaysLogs->last();
        
        // Determine next action based on sequence
        switch ($logCount) {
            case 1: // After first check-in
                if ($lastLog->action === 'check_in') {
                    return [
                        'action' => 'break_start',
                        'shift_type' => 'am',
                        'sequence' => 2,
                        'error' => false
                    ];
                }
                break;
                
            case 2: // After lunch break start
                if ($lastLog->action === 'break_start') {
                    return [
                        'action' => 'break_end',
                        'shift_type' => 'pm',
                        'sequence' => 3,
                        'error' => false
                    ];
                }
                break;
                
            case 3: // After lunch break end
                if ($lastLog->action === 'break_end') {
                    return [
                        'action' => 'check_out',
                        'shift_type' => 'pm',
                        'sequence' => 4,
                        'error' => false
                    ];
                }
                break;
                
            default: // Already completed full cycle
                return [
                    'error' => 'You have already completed your full work day (checked in, lunch break, and checked out).'
                ];
        }
        
        return [
            'error' => 'Invalid attendance sequence detected. Please contact administrator.'
        ];
    }

    private function getActionMessage($action, $shiftType)
    {
        $messages = [
            'check_in' => [
                'am' => 'Checked in for morning shift',
                'pm' => 'Checked in for afternoon shift'
            ],
            'break_start' => [
                'am' => 'Started lunch break',
                'pm' => 'Started lunch break'
            ],
            'break_end' => [
                'am' => 'Lunch break ended, afternoon shift started',
                'pm' => 'Lunch break ended, afternoon shift started'
            ],
            'check_out' => [
                'am' => 'Checked out from morning shift',
                'pm' => 'Checked out - work day completed'
            ]
        ];
        
        return $messages[$action][$shiftType] ?? 'Action completed';
    }

    private function getNextActionText($logs, $actionResult)
    {
        $logCount = $logs->count() + 1; // Including the action we just took
        
        switch ($logCount) {
            case 1: return 'Next: Start lunch break';
            case 2: return 'Next: End lunch break';
            case 3: return 'Next: Check out';
            case 4: return 'Work day completed';
            default: return 'No further actions';
        }
    }

    private function getButtonText($actionResult)
    {
        if ($actionResult['error']) {
            return 'Work Day Complete';
        }

        $buttonTexts = [
            'check_in' => 'Check In (Start Work)',
            'break_start' => 'Start Lunch Break',
            'break_end' => 'End Lunch Break',
            'check_out' => 'Check Out (End Work)'
        ];

        return $buttonTexts[$actionResult['action']] ?? 'Unknown Action';
    }

    private function getButtonColor($actionResult)
    {
        if ($actionResult['error']) {
            return 'gray'; // Disabled
        }

        $colors = [
            'check_in' => 'green',
            'break_start' => 'yellow',
            'break_end' => 'blue',
            'check_out' => 'red'
        ];

        return $colors[$actionResult['action']] ?? 'gray';
    }

    private function getStatusMessage($actionResult)
    {
        $statusMessages = [
            'check_in' => 'Ready to start your work day',
            'break_start' => 'Time for lunch break',
            'break_end' => 'Ready to resume afternoon shift',
            'check_out' => 'Ready to end your work day'
        ];

        return $statusMessages[$actionResult['action']] ?? 'Ready for action';
    }

    public function saveWorkplace(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|integer|min:10|max:1000'
        ]);

        $userId = $request->user_id;

        // First, create or update the workplace
        $workplace = Workplace::updateOrCreate(
            [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude
            ],
            [
                'name' => $request->name,
                'address' => $request->address,
                'radius' => $request->radius
            ]
        );

        // Then, create or update the user-workplace relationship
        DB::table('user_workplaces')->updateOrInsert(
            ['user_id' => $userId],
            [
                'workplace_id' => $workplace->id,
                'is_primary' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        return response()->json([
            'message' => 'Workplace saved successfully',
            'workplace' => [
                'id' => $workplace->id,
                'name' => $workplace->name,
                'address' => $workplace->address,
                'latitude' => (float)$workplace->latitude,
                'longitude' => (float)$workplace->longitude,
                'radius' => $workplace->radius
            ]
        ]);
    }

    public function performAction(Request $request)
    {
        // This will replace both checkIn and checkOut - it's the same logic
        return $this->checkIn($request);
    }

    public function getCurrentStatus($userId = 1)
    {
        $user = User::find($userId);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $today = now()->format('Y-m-d');
        
        // Get today's attendance logs
        $todaysLogs = AttendanceLog::forUser($userId)
            ->forDate($today)
            ->orderBy('timestamp')
            ->get();

        // Determine what action should be taken next
        $actionResult = $this->determineNextAction($todaysLogs);
        
        return response()->json([
            'current_logs_count' => $todaysLogs->count(),
            'logs' => $todaysLogs->map(function($log) {
                return [
                    'action' => $log->action,
                    'shift_type' => $log->shift_type,
                    'timestamp' => $log->timestamp->format('g:i A'),
                    'sequence' => $log->sequence
                ];
            }),
            'next_action' => $actionResult['error'] ? null : $actionResult['action'],
            'next_shift_type' => $actionResult['error'] ? null : $actionResult['shift_type'],
            'button_text' => $this->getButtonText($actionResult),
            'button_color' => $this->getButtonColor($actionResult),
            'can_perform_action' => !$actionResult['error'],
            'status_message' => $actionResult['error'] ?: $this->getStatusMessage($actionResult),
            'completed_today' => $todaysLogs->count() >= 4
        ]);
    }
}
