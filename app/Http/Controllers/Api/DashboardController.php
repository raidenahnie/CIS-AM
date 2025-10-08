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
    public function getUserStats($userId = null)
    {
        if (!$userId) {
            return response()->json(['error' => 'User ID is required'], 400);
        }
        
        $user = User::find($userId);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $currentMonth = now()->startOfMonth();
        $today = now()->format('Y-m-d');

        // Calculate total work days in current month (Monday-Friday only)
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();
        $totalWorkDaysInMonth = 0;
        
        for ($date = $startOfMonth->copy(); $date <= $endOfMonth; $date->addDay()) {
            // Count Monday (1) to Friday (5)
            if ($date->dayOfWeek >= 1 && $date->dayOfWeek <= 5) {
                $totalWorkDaysInMonth++;
            }
        }

        // Get user's attendances for current month (work days only - Monday to Friday)
        $attendances = Attendance::where('user_id', $userId)
            ->where('date', '>=', $currentMonth)
            ->whereRaw('DAYOFWEEK(date) BETWEEN 2 AND 6') // Monday=2, Friday=6 in MySQL
            ->get();

        $presentDays = $attendances->where('status', '!=', 'absent')->count();
        $attendanceRate = $totalWorkDaysInMonth > 0 ? round(($presentDays / $totalWorkDaysInMonth) * 100, 1) : 0;

        // Calculate average check-in time
        $checkInTimes = $attendances->whereNotNull('check_in_time');
        $avgCheckInMinutes = $checkInTimes->avg(function ($attendance) {
            return $attendance->check_in_time ? $attendance->check_in_time->hour * 60 + $attendance->check_in_time->minute : null;
        });
        
        if ($avgCheckInMinutes) {
            $hours = floor($avgCheckInMinutes / 60);
            $minutes = $avgCheckInMinutes % 60;
            $ampm = $hours >= 12 ? 'PM' : 'AM';
            $displayHour = $hours > 12 ? $hours - 12 : ($hours == 0 ? 12 : $hours);
            $avgCheckIn = sprintf('%d:%02d %s', $displayHour, $minutes, $ampm);
        } else {
            $avgCheckIn = '8:00 AM';
        }

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
            'total_work_days_this_month' => $totalWorkDaysInMonth,
            'attendance_rate' => $attendanceRate,
            'average_checkin_time' => $avgCheckIn,
            'today_hours' => $todayHours,
            'current_status' => $currentStatus,
            'user' => $user->name
        ]);
    }

    public function getAttendanceHistory($userId = null)
    {
        if (!$userId) {
            return response()->json(['error' => 'User ID is required'], 400);
        }
        
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

    public function getAttendanceLogs($userId = null)
    {
        if (!$userId) {
            return response()->json(['error' => 'User ID is required'], 400);
        }
        
        try {
            $logs = AttendanceLog::where('user_id', $userId)
                ->orderBy('timestamp', 'desc')
                ->limit(100) // Limit for performance
                ->get()
                ->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'action' => $log->action,
                        'timestamp' => $log->timestamp->format('g:i A'),
                        'shift_type' => $log->shift_type ?? 'regular',
                        'location' => $log->address ?? 'Workplace',
                        'date' => $log->timestamp->format('M j, Y'),
                        'date_raw' => $log->timestamp->format('Y-m-d'),
                        'is_valid_location' => $log->is_valid_location ?? true
                    ];
                });

            return response()->json($logs);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch attendance logs'], 500);
        }
    }

    public function getUserWorkplace($userId = null)
    {
        if (!$userId) {
            return response()->json(['error' => 'User ID is required'], 400);
        }
        
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
            // Convert distance to km for better readability
            $distanceKm = round($distance / 1000, 1);
            return response()->json([
                'error' => "You are {$distanceKm}km away from your workplace. You must be within {$workplace->radius}m to check in/out.",
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
        $currentTime = now();
        
        // No logs yet - first check-in of the day
        if ($logCount === 0) {
            // Determine shift type based on check-in time
            // AM shift: before 12:00 PM
            // PM shift: 12:00 PM and after
            $shiftType = $currentTime->hour < 12 ? 'am' : 'pm';
            
            return [
                'action' => 'check_in',
                'shift_type' => $shiftType,
                'sequence' => 1,
                'error' => false
            ];
        }

        $lastLog = $todaysLogs->last();
        $firstLog = $todaysLogs->first();
        
        // Get the shift type from the first check-in
        $shiftType = $firstLog->shift_type;
        
        // For AM shift: check_in -> break_start -> break_end -> check_out (4 actions total)
        // For PM shift: check_in -> check_out (2 actions total, no lunch break)
        
        if ($shiftType === 'am') {
            // AM shift workflow with lunch break
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
                    
                case 4: // Already completed full AM shift cycle
                    return [
                        'error' => 'You have already completed your full work day (checked in, lunch break, and checked out).'
                    ];
            }
        } else {
            // PM shift workflow without lunch break
            switch ($logCount) {
                case 1: // After first check-in (PM shift)
                    if ($lastLog->action === 'check_in') {
                        return [
                            'action' => 'check_out',
                            'shift_type' => 'pm',
                            'sequence' => 2,
                            'error' => false
                        ];
                    }
                    break;
                    
                case 2: // Already completed PM shift cycle
                    return [
                        'error' => 'You have already completed your PM shift (checked in and checked out).'
                    ];
            }
        }
        
        return [
            'error' => 'Invalid attendance sequence detected. Please contact administrator.'
        ];
    }

    private function getActionMessage($action, $shiftType)
    {
        $messages = [
            'check_in' => [
                'am' => 'Checked in for morning shift (AM)',
                'pm' => 'Checked in for afternoon shift (PM)'
            ],
            'break_start' => [
                'am' => 'Started lunch break',
                'pm' => 'Started lunch break' // This shouldn't happen for PM shifts
            ],
            'break_end' => [
                'am' => 'Lunch break ended, resuming afternoon work',
                'pm' => 'Lunch break ended, resuming afternoon work'
            ],
            'check_out' => [
                'am' => 'Checked out from morning shift',
                'pm' => 'Checked out - PM shift completed'
            ]
        ];
        
        return $messages[$action][$shiftType] ?? 'Action completed';
    }

    private function getNextActionText($logs, $actionResult)
    {
        $logCount = $logs->count() + 1; // Including the action we just took
        
        // If there's an error, work is completed
        if ($actionResult['error']) {
            return 'Work day completed';
        }
        
        // Get shift type from action result or first log
        $shiftType = $actionResult['shift_type'] ?? ($logs->count() > 0 ? $logs->first()->shift_type : 'am');
        
        if ($shiftType === 'am') {
            // AM shift workflow
            switch ($logCount) {
                case 1: return 'Next: Start lunch break';
                case 2: return 'Next: End lunch break';
                case 3: return 'Next: Check out';
                case 4: return 'Work day completed';
                default: return 'No further actions';
            }
        } else {
            // PM shift workflow (no lunch break)
            switch ($logCount) {
                case 1: return 'Next: Check out';
                case 2: return 'PM shift completed';
                default: return 'No further actions';
            }
        }
    }

    private function getButtonText($actionResult)
    {
        if ($actionResult['error']) {
            return 'Work Day Complete';
        }

        $action = $actionResult['action'];
        $shiftType = $actionResult['shift_type'];

        // For check_in, specify the shift type
        if ($action === 'check_in') {
            return $shiftType === 'am' ? 'Check In (AM Shift)' : 'Check In (PM Shift)';
        }

        $buttonTexts = [
            'break_start' => 'Start Lunch Break',
            'break_end' => 'End Lunch Break',
            'check_out' => $shiftType === 'pm' ? 'Check Out (End PM Shift)' : 'Check Out (End Work)'
        ];

        return $buttonTexts[$action] ?? 'Unknown Action';
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
        $action = $actionResult['action'];
        $shiftType = $actionResult['shift_type'];

        if ($action === 'check_in') {
            return $shiftType === 'am' ? 'Ready to start your morning shift' : 'Ready to start your afternoon shift';
        }

        $statusMessages = [
            'break_start' => 'Time for lunch break',
            'break_end' => 'Ready to resume afternoon work',
            'check_out' => $shiftType === 'pm' ? 'Ready to end your PM shift' : 'Ready to end your work day'
        ];

        return $statusMessages[$action] ?? 'Ready for action';
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

    public function getCurrentStatus($userId = null)
    {
        if (!$userId) {
            return response()->json(['error' => 'User ID is required'], 400);
        }
        
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
        
        // Determine if work is completed based on shift type
        $isCompleted = false;
        if ($todaysLogs->count() > 0) {
            $firstLog = $todaysLogs->first();
            $shiftType = $firstLog->shift_type;
            
            // AM shift is complete after 4 actions, PM shift after 2 actions
            $requiredActions = ($shiftType === 'am') ? 4 : 2;
            $isCompleted = $todaysLogs->count() >= $requiredActions;
        }
        
        return response()->json([
            'current_logs_count' => $todaysLogs->count(),
            'shift_type' => $todaysLogs->count() > 0 ? $todaysLogs->first()->shift_type : null,
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
            'completed_today' => $isCompleted
        ]);
    }

    /**
     * Get all workplaces assigned to a user
     */
    public function getUserWorkplaces($userId = null)
    {
        if (!$userId) {
            return response()->json(['error' => 'User ID is required'], 400);
        }
        
        $user = User::with(['workplaces' => function($query) {
            $query->where('is_active', true);
        }])->find($userId);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $workplaces = $user->workplaces->map(function($workplace) {
            return [
                'id' => $workplace->id,
                'name' => $workplace->name,
                'address' => $workplace->address,
                'latitude' => (float)$workplace->latitude,
                'longitude' => (float)$workplace->longitude,
                'radius' => $workplace->radius,
                'is_primary' => (bool)$workplace->pivot->is_primary,
                'role' => $workplace->pivot->role ?? 'employee',
                'assigned_at' => $workplace->pivot->assigned_at ? 
                    \Carbon\Carbon::parse($workplace->pivot->assigned_at)->format('M j, Y') : null
            ];
        });

        return response()->json([
            'workplaces' => $workplaces,
            'count' => $workplaces->count(),
            'primary_workplace' => $workplaces->firstWhere('is_primary', true)
        ]);
    }

    /**
     * Set a workplace as primary for a user
     */
    public function setPrimaryWorkplace(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'workplace_id' => 'required|exists:workplaces,id'
        ]);

        $userId = $request->user_id;
        $workplaceId = $request->workplace_id;

        // Verify user is assigned to this workplace
        $assignment = DB::table('user_workplaces')
            ->where('user_id', $userId)
            ->where('workplace_id', $workplaceId)
            ->first();

        if (!$assignment) {
            return response()->json(['error' => 'User is not assigned to this workplace'], 400);
        }

        // Use transaction to safely update primary workplace
        // The unique constraint prevents duplicate (user_id, is_primary) combinations
        // So we must remove the old primary BEFORE setting the new one
        DB::transaction(function () use ($userId, $workplaceId) {
            // Step 1: Remove primary status from all OTHER workplaces for this user FIRST
            DB::table('user_workplaces')
                ->where('user_id', $userId)
                ->where('workplace_id', '!=', $workplaceId)
                ->where('is_primary', true)  // Only update those that are currently primary
                ->update(['is_primary' => false, 'updated_at' => now()]);

            // Step 2: Now safely set the selected workplace as primary
            DB::table('user_workplaces')
                ->where('user_id', $userId)
                ->where('workplace_id', $workplaceId)
                ->update(['is_primary' => true, 'updated_at' => now()]);
        });

        return response()->json([
            'message' => 'Primary workplace updated successfully',
            'workplace_id' => $workplaceId
        ]);
    }
}
