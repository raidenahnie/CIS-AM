<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Workplace;
use App\Models\UserWorkplace;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function getUserStats($userId = null)
    {
        if (!$userId) {
            return response()->json(['error' => 'User ID is required'], 400);
        }
        
        // ⚡ CACHE: Cache user stats for 5 minutes to reduce database load
        $cacheKey = "user_stats_{$userId}_" . now()->format('Y-m-d-H-i');
        
        return \Cache::remember($cacheKey, 300, function() use ($userId) {
            $user = User::find($userId);
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $currentMonth = now()->startOfMonth();
            $today = now()->format('Y-m-d');

            // Calculate total work days in current month (Monday-Friday only)
            $startOfMonth = now()->startOfMonth();
            $endOfMonth = now()->endOfMonth();
            $userCreatedDate = Carbon::parse($user->created_at)->startOfDay();
            
            // Adjust start date if user was created mid-month
            $countStartDate = $startOfMonth->lt($userCreatedDate) ? $userCreatedDate->copy() : $startOfMonth->copy();
            
            $totalWorkDaysInMonth = 0;
            
            for ($date = $countStartDate->copy(); $date <= $endOfMonth; $date->addDay()) {
                // Count Monday (1) to Friday (5), only count up to today
                if ($date->dayOfWeek >= 1 && $date->dayOfWeek <= 5 && $date->lte(now())) {
                    $totalWorkDaysInMonth++;
                }
            }

            // ⚡ OPTIMIZED: Get only necessary columns and use whereIn for status filtering
            $attendances = Attendance::where('user_id', $userId)
                ->where('date', '>=', $currentMonth)
                ->whereRaw('DAYOFWEEK(date) BETWEEN 2 AND 6') // Monday=2, Friday=6 in MySQL
                ->select('id', 'date', 'status', 'check_in_time', 'check_out_time', 'total_hours')
                ->get();

        // Count unique dates where user was present (status contains "present" or is "late" or "special")
        // Special check-ins with multiple pairs should only count as 1 day present
        // Only count: present, late, special (these indicate actual presence)
        // Do NOT count: absent, excused (excused = approved absence, not present)
        $presentDays = $attendances->filter(function($attendance) {
            $status = strtolower($attendance->status);
            return in_array($status, ['present', 'late', 'special']);
        })->pluck('date')->unique()->count();
        
        $attendanceRate = $totalWorkDaysInMonth > 0 ? round(($presentDays / $totalWorkDaysInMonth) * 100, 1) : 0;
        
        // Cap attendance rate at 100%
        $attendanceRate = min($attendanceRate, 100);

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
        }); // End Cache::remember
    }

    public function getAttendanceHistory($userId = null)
    {
        if (!$userId) {
            return response()->json(['error' => 'User ID is required'], 400);
        }
        
        // ⚡ OPTIMIZED: Eager load all relationships at once to prevent N+1 queries
        $attendances = Attendance::where('user_id', $userId)
            ->with([
                'workplace',
                'logs' => function($q) {
                    $q->with('workplace')  // Eager load workplace for special logs
                      ->orderBy('timestamp', 'asc');
                }
            ])
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get()
            ->flatMap(function ($attendance) {
                // If this is a special attendance, expand it into multiple rows
                // based on its special attendance logs (paired check_in/check_out).
                if ($attendance->status === 'special') {
                    $rows = [];

                    // ⚡ OPTIMIZED: Use already eager-loaded logs (no additional query)
                    $logs = $attendance->logs->filter(function($log) {
                        return $log->shift_type === 'special' || $log->type === 'special';
                    })->sortBy('timestamp');

                    // Track open check-ins per workplace to pair with check-outs
                    $open = [];

                    $pairIndex = 0;
                    foreach ($logs as $log) {
                        if ($log->action === 'check_in') {
                            $open[$log->workplace_id][] = $log;
                        } elseif ($log->action === 'check_out') {
                            if (!empty($open[$log->workplace_id])) {
                                $checkInLog = array_pop($open[$log->workplace_id]);
                                $minutes = $checkInLog->timestamp->diffInMinutes($log->timestamp);
                                $hoursText = $minutes > 0 ? round($minutes / 60, 1) . ' hrs' : '0 hrs';

                                $pairIndex++;

                                $rows[] = [
                                    // Unique row id helps frontend avoid using the parent attendance fields
                                    'row_id' => $attendance->id . '-' . $pairIndex,
                                    'attendance_id' => $attendance->id,
                                    'pair_index' => $pairIndex,
                                    'date' => $attendance->date->format('M j, Y'),
                                    'date_raw' => $attendance->date->format('Y-m-d'),
                                    'check_in' => $checkInLog->timestamp->format('g:i A'),
                                    'check_out' => $log->timestamp ? $log->timestamp->format('g:i A') : 'Still working',
                                    'total_hours' => $hoursText,
                                    'location' => $log->workplace ? $log->workplace->name : ($attendance->workplace ? $attendance->workplace->name : 'Unknown'),
                                    'status' => 'Special',
                                    'status_class' => $this->getStatusClass('special'),
                                    // Provide the overarching attendance-level check_in/out too (for compatibility)
                                    'attendance_check_in' => $attendance->check_in_time ? $attendance->check_in_time->format('g:i A') : null,
                                    'attendance_check_out' => $attendance->check_out_time ? $attendance->check_out_time->format('g:i A') : null,
                                ];
                            } else {
                                // Unpaired check_out, show it with unknown check-in
                                $pairIndex++;
                                $rows[] = [
                                    'row_id' => $attendance->id . '-' . $pairIndex,
                                    'attendance_id' => $attendance->id,
                                    'pair_index' => $pairIndex,
                                    'date' => $attendance->date->format('M j, Y'),
                                    'date_raw' => $attendance->date->format('Y-m-d'),
                                    'check_in' => '--',
                                    'check_out' => $log->timestamp ? $log->timestamp->format('g:i A') : 'Still working',
                                    'total_hours' => '0 hrs',
                                    'location' => $log->workplace ? $log->workplace->name : ($attendance->workplace ? $attendance->workplace->name : 'Unknown'),
                                    'status' => 'Special',
                                    'status_class' => $this->getStatusClass('special'),
                                    'attendance_check_in' => $attendance->check_in_time ? $attendance->check_in_time->format('g:i A') : null,
                                    'attendance_check_out' => $attendance->check_out_time ? $attendance->check_out_time->format('g:i A') : null,
                                ];
                            }
                        }
                    }

                    // Any remaining open check-ins (no check-out yet)
                    foreach ($open as $workplaceLogs) {
                        foreach ($workplaceLogs as $checkInLog) {
                            $pairIndex++;
                            $rows[] = [
                                'row_id' => $attendance->id . '-' . $pairIndex,
                                'attendance_id' => $attendance->id,
                                'pair_index' => $pairIndex,
                                'date' => $attendance->date->format('M j, Y'),
                                'date_raw' => $attendance->date->format('Y-m-d'),
                                'check_in' => $checkInLog->timestamp->format('g:i A'),
                                'check_out' => 'Still working',
                                'total_hours' => '0 hrs',
                                'location' => $checkInLog->workplace ? $checkInLog->workplace->name : ($attendance->workplace ? $attendance->workplace->name : 'Unknown'),
                                'status' => 'Special',
                                'status_class' => $this->getStatusClass('special'),
                                'attendance_check_in' => $attendance->check_in_time ? $attendance->check_in_time->format('g:i A') : null,
                                'attendance_check_out' => $attendance->check_out_time ? $attendance->check_out_time->format('g:i A') : null,
                            ];
                        }
                    }

                    return collect($rows);
                }

                // Regular attendance (non-special) - single row
                // If attendance-level check_in/check_out are missing, try to derive
                // them from the eager-loaded logs for that attendance.
                $derivedCheckIn = null;
                $derivedCheckOut = null;

                if ($attendance->logs && $attendance->logs->isNotEmpty()) {
                    $firstCheckInLog = $attendance->logs->firstWhere('action', 'check_in');
                    $lastCheckOutLog = $attendance->logs->where('action', 'check_out')->last();

                    if ($firstCheckInLog) {
                        $derivedCheckIn = $firstCheckInLog->timestamp->format('g:i A');
                    }
                    if ($lastCheckOutLog) {
                        $derivedCheckOut = $lastCheckOutLog->timestamp->format('g:i A');
                    }
                }

                // Handle excused absences (approved leave) - no check-in/out times
                if ($attendance->status === 'excused') {
                    return collect([
                        [
                            'row_id' => $attendance->id . '-0',
                            'attendance_id' => $attendance->id,
                            'pair_index' => 0,
                            'date' => $attendance->date->format('M j, Y'),
                            'date_raw' => $attendance->date->format('Y-m-d'),
                            'check_in' => '--',
                            'check_out' => '--',
                            'total_hours' => '0 hrs',
                            'location' => $attendance->workplace ? $attendance->workplace->name : 'Unknown',
                            'status' => 'Excused',
                            'status_class' => $this->getStatusClass('excused'),
                            'attendance_check_in' => null,
                            'attendance_check_out' => null,
                            'notes' => $attendance->notes ?? 'Approved absence'
                        ]
                    ]);
                }

                $checkInValue = $attendance->check_in_time ? $attendance->check_in_time->format('g:i A') : ($derivedCheckIn ?? '--');
                $checkOutValue = $attendance->check_out_time ? $attendance->check_out_time->format('g:i A') : ($derivedCheckOut ?? 'Still working');

                return collect([
                    [
                        'row_id' => $attendance->id . '-0',
                        'attendance_id' => $attendance->id,
                        'pair_index' => 0,
                        'date' => $attendance->date->format('M j, Y'),
                        'date_raw' => $attendance->date->format('Y-m-d'),
                        'check_in' => $checkInValue,
                        'check_out' => $checkOutValue,
                        'total_hours' => $attendance->total_hours ? round($attendance->total_hours / 60, 1) . ' hrs' : '0 hrs',
                        'location' => $attendance->workplace ? $attendance->workplace->name : 'Unknown',
                        'status' => ucfirst($attendance->status),
                        'status_class' => $this->getStatusClass($attendance->status),
                        'attendance_check_in' => $attendance->check_in_time ? $attendance->check_in_time->format('g:i A') : null,
                        'attendance_check_out' => $attendance->check_out_time ? $attendance->check_out_time->format('g:i A') : ($derivedCheckOut ?? null),
                    ]
                ]);
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

        // Check if user has already used special check-in today (mutual exclusivity)
        $hasSpecialCheckin = AttendanceLog::where('user_id', $userId)
            ->whereDate('timestamp', $today)
            ->where('shift_type', 'special')
            ->exists();
        
        if ($hasSpecialCheckin) {
            return response()->json([
                'error' => 'You have already used Special Check-In today. You cannot use both regular and special check-in on the same day.',
                'locked_type' => 'special'
            ], 400);
        }

        // Check if user already has attendance today (to lock workplace for regular check-in)
        $todaysAttendance = Attendance::where('user_id', $userId)
            ->where('date', $today)
            ->first();

        if ($todaysAttendance) {
            // User already checked in today - must use the SAME workplace for all actions
            $lockedWorkplace = Workplace::find($todaysAttendance->workplace_id);
            
            if (!$lockedWorkplace) {
                return response()->json([
                    'error' => 'Invalid workplace configuration. Please contact administrator.',
                ], 400);
            }

            // Validate they're at the same workplace
            $distance = $this->calculateDistance(
                $request->latitude,
                $request->longitude,
                $lockedWorkplace->latitude,
                $lockedWorkplace->longitude
            );

            if ($distance > $lockedWorkplace->radius) {
                $distanceKm = round($distance / 1000, 1);
                return response()->json([
                    'error' => "You must return to {$lockedWorkplace->name} to complete your workday. Currently {$distanceKm}km away.",
                    'distance' => round($distance),
                    'required_workplace' => $lockedWorkplace->name,
                    'required_radius' => $lockedWorkplace->radius
                ], 400);
            }

            $workplace = $lockedWorkplace;
            $isAssignedWorkplace = $todaysAttendance->is_assigned_workplace;
            $validatedDistance = $distance;

        } else {
            // First check-in of the day - can be at any workplace
            $locationValidation = $this->validateCheckInLocation($request->latitude, $request->longitude, $userId);
            
            if (!$locationValidation['valid']) {
                return response()->json([
                    'error' => $locationValidation['message'],
                    'distance' => $locationValidation['distance'],
                    'nearest_workplace' => $locationValidation['nearest_workplace'],
                    'required_radius' => $locationValidation['required_radius'] ?? null
                ], 400);
            }

            $workplace = $locationValidation['workplace'];
            $isAssignedWorkplace = $locationValidation['is_assigned'];
            $validatedDistance = $locationValidation['distance'];
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
                'status' => 'present',
                'is_assigned_workplace' => $isAssignedWorkplace
            ]
        );

        // Update workplace_id if changed
        if ($attendance->workplace_id != $workplace->id) {
            $attendance->workplace_id = $workplace->id;
            $attendance->is_assigned_workplace = $isAssignedWorkplace;
            $attendance->save();
        }

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
            'is_valid_location' => true,
            'distance_from_workplace' => round($validatedDistance),
            'method' => 'gps',
            'ip_address' => $request->ip()
        ]);

        // Update attendance record with first check-in time if this is the first action
        $shouldSendNotification = false;
        $notificationData = null;
        
        if ($actionResult['action'] === 'check_in' && !$attendance->check_in_time) {
            $attendance->update(['check_in_time' => $currentTime]);
            
            // Prepare notification data to send after response
            $shouldSendNotification = true;
            $notificationData = [
                'user_id' => $userId,
                'shift_label' => strtoupper($actionResult['shift_type']),
                'check_in_time' => $currentTime->format('g:i A'),
                'workplace_name' => $workplace->name
            ];
        }

        $responseData = [
            'message' => $this->getActionMessage($actionResult['action'], $actionResult['shift_type']),
            'action' => $actionResult['action'],
            'shift_type' => $actionResult['shift_type'],
            'sequence' => $actionResult['sequence'],
            'next_action' => $this->getNextActionText($todaysLogs, $actionResult),
            'attendance_log' => $log,
            'distance' => round($validatedDistance),
            'is_valid_location' => true,
            'workplace' => [
                'id' => $workplace->id,
                'name' => $workplace->name,
                'is_assigned' => $isAssignedWorkplace
            ]
        ];

        // Add warning if checking in at non-assigned workplace
        if (!$isAssignedWorkplace) {
            $responseData['warning'] = "You are checking in at {$workplace->name}, which is not in your assigned workplaces.";
            $responseData['info'] = 'This check-in has been logged for admin review.';
            
            // Attendance record already tracks this with is_assigned_workplace column
            // Admins can view in Check-In Logs modal (Attendance section)
            \Log::info("User {$userId} checked in at non-assigned workplace", [
                'workplace_id' => $workplace->id,
                'workplace_name' => $workplace->name,
                'attendance_id' => $attendance->id
            ]);
        }

        // Send notification asynchronously after response
        if ($shouldSendNotification && $notificationData) {
            $this->sendCheckinNotificationAsync($notificationData);
        }

        return response()->json($responseData);
    }

    /**
     * Validate check-in location and find appropriate workplace
     */
    private function validateCheckInLocation($latitude, $longitude, $userId)
    {
        Log::info("Validating check-in location for user {$userId} at lat: {$latitude}, lng: {$longitude}");
        
        // Step 1: Try primary workplace first
        $primaryWorkplace = DB::table('user_workplaces')
            ->join('workplaces', 'user_workplaces.workplace_id', '=', 'workplaces.id')
            ->where('user_workplaces.user_id', $userId)
            ->where('user_workplaces.is_primary', true)
            ->where('workplaces.is_active', true)
            ->select('workplaces.*')
            ->first();

        if ($primaryWorkplace) {
            $distance = $this->calculateDistance($latitude, $longitude, $primaryWorkplace->latitude, $primaryWorkplace->longitude);
            Log::info("Primary workplace distance: {$distance}m from {$primaryWorkplace->name}");
            
            if ($distance <= $primaryWorkplace->radius) {
                Log::info("Within primary workplace radius - Check-in allowed");
                return [
                    'valid' => true,
                    'workplace' => $primaryWorkplace,
                    'distance' => $distance,
                    'is_assigned' => true
                ];
            }
        }

        // Step 2: Check all assigned workplaces
        Log::info("Checking assigned workplaces for user {$userId}");
        $assignedWorkplaces = DB::table('user_workplaces')
            ->join('workplaces', 'user_workplaces.workplace_id', '=', 'workplaces.id')
            ->where('user_workplaces.user_id', $userId)
            ->where('workplaces.is_active', true)
            ->select('workplaces.*')
            ->get();

        Log::info("Found " . $assignedWorkplaces->count() . " assigned workplaces");

        foreach ($assignedWorkplaces as $workplace) {
            $distance = $this->calculateDistance($latitude, $longitude, $workplace->latitude, $workplace->longitude);
            Log::info("Distance to {$workplace->name}: {$distance}m (radius: {$workplace->radius}m)");
            
            if ($distance <= $workplace->radius) {
                Log::info("Within assigned workplace radius - Check-in allowed at {$workplace->name}");
                return [
                    'valid' => true,
                    'workplace' => $workplace,
                    'distance' => $distance,
                    'is_assigned' => true
                ];
            }
        }

        // Step 3: Check ALL system workplaces (non-assigned)
        Log::info("Not within any assigned workplace, checking ALL system workplaces");
        $allWorkplaces = DB::table('workplaces')
            ->where('is_active', true)
            ->get();

        Log::info("Found " . $allWorkplaces->count() . " total system workplaces");
        $assignedIds = $assignedWorkplaces->pluck('id')->toArray();

        foreach ($allWorkplaces as $workplace) {
            // Skip already checked assigned workplaces
            if (in_array($workplace->id, $assignedIds)) {
                continue;
            }

            $distance = $this->calculateDistance($latitude, $longitude, $workplace->latitude, $workplace->longitude);
            Log::info("Distance to NON-ASSIGNED {$workplace->name}: {$distance}m (radius: {$workplace->radius}m)");
            
            if ($distance <= $workplace->radius) {
                Log::info("Within NON-ASSIGNED workplace radius - Check-in allowed at {$workplace->name} with WARNING");
                return [
                    'valid' => true,
                    'workplace' => $workplace,
                    'distance' => $distance,
                    'is_assigned' => false // Flag as non-assigned workplace
                ];
            }
        }

        // Step 4: Find nearest workplace for error message
        $nearestWorkplace = null;
        $nearestDistance = PHP_FLOAT_MAX;

        foreach ($allWorkplaces as $workplace) {
            $distance = $this->calculateDistance($latitude, $longitude, $workplace->latitude, $workplace->longitude);
            
            if ($distance < $nearestDistance) {
                $nearestDistance = $distance;
                $nearestWorkplace = $workplace;
            }
        }

        $distanceKm = round($nearestDistance / 1000, 1);
        
        return [
            'valid' => false,
            'workplace' => null,
            'distance' => $nearestDistance,
            'nearest_workplace' => $nearestWorkplace ? $nearestWorkplace->name : null,
            'required_radius' => $nearestWorkplace ? $nearestWorkplace->radius : null,
            'message' => $nearestWorkplace 
                ? "You are {$distanceKm}km away from the nearest workplace ({$nearestWorkplace->name}). You must be within {$nearestWorkplace->radius}m to check in/out."
                : 'No workplace found nearby. Please contact your administrator.'
        ];
    }

    private function getStatusClass($status)
    {
        return match(strtolower($status)) {
            'present' => 'bg-green-100 text-green-800',
            'late' => 'bg-yellow-100 text-yellow-800', 
            'absent' => 'bg-red-100 text-red-800',
            'special' => 'bg-blue-100 text-blue-800',
            'excused' => 'bg-blue-100 text-blue-800',
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
        
        // Get today's attendance logs (REGULAR only, not special)
        $todaysLogs = AttendanceLog::forUser($userId)
            ->forDate($today)
            ->where(function($query) {
                $query->where('shift_type', '!=', 'special')
                      ->orWhereNull('shift_type');
            })
            ->where(function($query) {
                $query->where('type', '!=', 'special')
                      ->orWhereNull('type');
            })
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
     * Get workplaces for the current authenticated user (for manual location entry)
     */
    public function getCurrentUserWorkplaces(Request $request)
    {
        $user = $request->user();
        
        $workplaces = $user->workplaces()
            ->where('is_active', true)
            ->get()
            ->map(function($workplace) {
                return [
                    'id' => $workplace->id,
                    'name' => $workplace->name,
                    'address' => $workplace->address,
                    'latitude' => (float)$workplace->latitude,
                    'longitude' => (float)$workplace->longitude,
                    'radius' => $workplace->radius,
                    'is_primary' => (bool)$workplace->pivot->is_primary,
                ];
            });

        return response()->json([
            'workplaces' => $workplaces,
            'count' => $workplaces->count()
        ]);
    }

    /**
     * Get all active workplaces in the system
     */
    public function getAllWorkplaces(Request $request)
    {
        $workplaces = Workplace::where('is_active', true)
            ->get()
            ->map(function($workplace) {
                return [
                    'id' => $workplace->id,
                    'name' => $workplace->name,
                    'address' => $workplace->address,
                    'latitude' => (float)$workplace->latitude,
                    'longitude' => (float)$workplace->longitude,
                    'radius' => $workplace->radius,
                    'is_primary' => false, // Not assigned to user, so not primary
                    'role' => null,
                    'assigned_at' => null
                ];
            });

        return response()->json([
            'success' => true,
            'workplaces' => $workplaces,
            'count' => $workplaces->count()
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
        DB::transaction(function () use ($userId, $workplaceId) {
            // Step 1: Remove primary status from all OTHER workplaces for this user FIRST
            DB::table('user_workplaces')
                ->where('user_id', $userId)
                ->where('workplace_id', '!=', $workplaceId)
                ->where('is_primary', true)
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

    /**
     * Get today's special check-in/out logs for a user
     */
    public function getSpecialCheckinLogs($userId = null)
    {
        if (!$userId) {
            return response()->json(['error' => 'User ID is required'], 400);
        }
        $today = now()->format('Y-m-d');
        $logs = AttendanceLog::where('user_id', $userId)
            ->special()
            ->whereDate('timestamp', $today)
            ->with('workplace') // Load workplace relationship
            ->orderBy('timestamp')
            ->limit(8)
            ->get();
        
        $uniqueLocations = $logs->pluck('workplace_id')->unique()->count();
        
        return response()->json([
            'logs' => $logs->map(function($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'timestamp' => $log->timestamp->format('g:i A'),
                    'time' => $log->timestamp->format('g:i A'), // For sorting compatibility
                    'date' => $log->timestamp->format('Y-m-d'), // For filtering compatibility
                    'location' => $log->workplace ? $log->workplace->name : ($log->address ?? 'Location'),
                    'workplace_name' => $log->workplace ? $log->workplace->name : ($log->address ?? 'Location'),
                    'workplace_id' => $log->workplace_id,
                ];
            }),
            'count' => $logs->count(),
            'pairs_count' => floor($logs->count() / 2),
            'unique_locations' => $uniqueLocations
        ]);
    }

    /**
    * Perform a special check-in or check-out
    */
    public function specialCheckin(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'workplace_id' => 'required|exists:workplaces,id',
            'action' => 'required|in:check_in,check_out',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'address' => 'nullable|string',
        ]);
        
        $userId = $request->user_id;
        $workplaceId = $request->workplace_id;
        $action = $request->action;
        $today = now()->format('Y-m-d');
        $currentTime = now();
        
        // Check if user has already used regular check-in today (mutual exclusivity)
        $hasRegularCheckin = AttendanceLog::where('user_id', $userId)
            ->whereDate('timestamp', $today)
            ->where('shift_type', '!=', 'special')
            ->exists();
        
        if ($hasRegularCheckin) {
            return response()->json([
                'error' => 'You have already used Regular Check-In today. You cannot use both regular and special check-in on the same day.',
                'locked_type' => 'regular'
            ], 400);
        }
        
        $todayLogs = AttendanceLog::where('user_id', $userId)
            ->special()
            ->whereDate('timestamp', $today)
            ->with('workplace')
            ->orderBy('timestamp')
            ->get();
        
        $totalActions = $todayLogs->count();
        
        if ($totalActions >= 8) {
            return response()->json(['error' => 'Maximum of 4 check-in/out pairs (8 actions) reached for today'], 400);
        }
        
        // Workplace locking logic - validate user is at the same workplace for all actions
        $firstCheckInLog = $todayLogs->where('action', 'check_in')->first();
        
        if ($firstCheckInLog && $firstCheckInLog->workplace_id != $workplaceId) {
            // User is trying to use a different workplace - validate distance to the first workplace
            $lockedWorkplace = Workplace::find($firstCheckInLog->workplace_id);
            
            if ($lockedWorkplace) {
                $distance = $this->calculateDistance(
                    $request->latitude,
                    $request->longitude,
                    $lockedWorkplace->latitude,
                    $lockedWorkplace->longitude
                );
                
                if ($distance > $lockedWorkplace->radius) {
                    $distanceKm = round($distance / 1000, 1);
                    return response()->json([
                        'error' => "You must return to {$lockedWorkplace->name} to continue your special shift. Currently {$distanceKm}km away.",
                        'distance' => round($distance),
                        'required_workplace' => $lockedWorkplace->name,
                        'required_radius' => $lockedWorkplace->radius
                    ], 400);
                }
            }
        }
        
        // For first check-in of the day, validate location (can be any workplace in system)
        if (!$firstCheckInLog && $action === 'check_in') {
            $workplace = Workplace::find($workplaceId);
            $distance = $this->calculateDistance(
                $request->latitude,
                $request->longitude,
                $workplace->latitude,
                $workplace->longitude
            );
            
            if ($distance > $workplace->radius) {
                // Not in the selected workplace radius
                $distanceKm = round($distance / 1000, 1);
                return response()->json([
                    'error' => "You are not within the geofence of {$workplace->name}. You are {$distanceKm}km away.",
                    'distance' => round($distance),
                    'required_radius' => $workplace->radius
                ], 400);
            }
            
            // Check if this is an assigned workplace
            $isAssignedWorkplace = UserWorkplace::where('user_id', $userId)
                ->where('workplace_id', $workplaceId)
                ->exists();
        } else {
            // For subsequent actions, use the assignment status from attendance record
            $attendance = Attendance::where('user_id', $userId)
                ->where('date', $today)
                ->first();
            $isAssignedWorkplace = $attendance ? $attendance->is_assigned_workplace : true;
        }
        
        // Check for validation based on action type
        if ($action === 'check_in') {
            // Find any open check-ins (check-ins without a corresponding check-out)
            $openCheckIns = [];
            foreach ($todayLogs as $log) {
                if ($log->action === 'check_in') {
                    $openCheckIns[$log->workplace_id] = $log;
                } elseif ($log->action === 'check_out' && isset($openCheckIns[$log->workplace_id])) {
                    unset($openCheckIns[$log->workplace_id]);
                }
            }
            
            // If there are any open check-ins, don't allow a new check-in
            if (!empty($openCheckIns)) {
                $openWorkplace = array_values($openCheckIns)[0]->workplace;
                $workplaceName = $openWorkplace ? $openWorkplace->name : 'a location';
                return response()->json([
                    'error' => "You must check out from {$workplaceName} before checking in again"
                ], 400);
            }
        } else { // check_out
            // For check-out, ensure there's a corresponding check-in at THIS workplace
            $workplaceLogs = $todayLogs->where('workplace_id', $workplaceId);
            $lastWorkplaceAction = $workplaceLogs->last();
            
            if (!$lastWorkplaceAction || $lastWorkplaceAction->action !== 'check_in') {
                return response()->json(['error' => 'You must check in before checking out at this location'], 400);
            }
        }
        
        $attendance = Attendance::firstOrCreate(
            ['user_id' => $userId, 'date' => $today],
            [
                'workplace_id' => $workplaceId, 
                'status' => 'special',
                'is_assigned_workplace' => $isAssignedWorkplace
            ]
        );
        
        // Update is_assigned_workplace if this is the first action
        if (!$firstCheckInLog && $action === 'check_in') {
            $attendance->is_assigned_workplace = $isAssignedWorkplace;
            $attendance->save();
            
            // Log non-assigned workplace check-in for debugging
            if (!$isAssignedWorkplace) {
                $workplace = Workplace::find($workplaceId);
                
                // Attendance record already tracks this with is_assigned_workplace column
                // Admins can view in Check-In Logs modal (Attendance section)
                \Log::info("User {$userId} checked in at non-assigned workplace (SPECIAL)", [
                    'workplace_id' => $workplaceId,
                    'workplace_name' => $workplace->name,
                    'attendance_id' => $attendance->id
                ]);
            }
        }
        
        $log = AttendanceLog::create([
            'user_id' => $userId,
            'workplace_id' => $workplaceId,
            'attendance_id' => $attendance->id,
            'action' => $action,
            'shift_type' => 'special',
            'sequence' => $totalActions + 1,
            'timestamp' => $currentTime,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'address' => $request->address,
            'is_valid_location' => true,
            'method' => 'gps',
            'ip_address' => $request->ip(),
            'type' => 'special'
        ]);
        
        // Recalculate the summary after every action
        $this->updateAttendanceSummary($userId, $today);
        
        $responseData = [
            'message' => 'Special ' . ($action === 'check_in' ? 'check-in' : 'check-out') . ' recorded',
            'log' => $log->toArray(),
            'total_actions' => $totalActions + 1,
            'remaining_actions' => 8 - ($totalActions + 1)
        ];
        
        // Add warning if checking in at non-assigned workplace
        if (!$firstCheckInLog && $action === 'check_in' && !$isAssignedWorkplace) {
            $workplace = Workplace::find($workplaceId);
            $responseData['warning'] = "You are checking in at {$workplace->name}, which is not in your assigned workplaces.";
            $responseData['info'] = 'This check-in has been logged for admin review.';
        }
        
        // Send notification asynchronously after response for special check-ins
        if ($action === 'check_in') {
            $this->sendCheckinNotificationAsync([
                'user_id' => $userId,
                'shift_label' => 'SPECIAL',
                'check_in_time' => $currentTime->format('g:i A'),
                'workplace_id' => $workplaceId
            ]);
        }
        
        return response()->json($responseData);
    }

    /**
     * Recalculate and update the main attendance record from all logs of the day.
     * This ensures the summary (check_in, check_out, total_hours, status) is always accurate.
     */
    private function updateAttendanceSummary($userId, $date)
    {
        $attendance = Attendance::where('user_id', $userId)->where('date', $date)->first();
        if (!$attendance) {
            return;
        }

        $allLogs = AttendanceLog::where('user_id', $userId)
            ->whereDate('timestamp', $date)
            ->orderBy('timestamp', 'asc')
            ->get();

        if ($allLogs->isEmpty()) {
            // If all logs are deleted, reset the summary
            $attendance->update([
                'check_in_time' => null,
                'check_out_time' => null,
                'total_hours' => null,
                'status' => 'absent' 
            ]);
            return;
        }

        // 1. Update Status
        if ($allLogs->contains('shift_type', 'special')) {
            $attendance->status = 'special';
        } else {
            // Basic status for regular shifts
            $attendance->status = $attendance->isLate() ? 'late' : 'present';
        }

        // 2. Update first check-in and last check-out
        $firstCheckIn = $allLogs->where('action', 'check_in')->first();
        $lastCheckOut = $allLogs->where('action', 'check_out')->last();

        $attendance->check_in_time = $firstCheckIn ? $firstCheckIn->timestamp : null;
        $attendance->check_out_time = $lastCheckOut ? $lastCheckOut->timestamp : null;

        // 3. Recalculate total_hours from all pairs
        $totalMinutes = 0;
        $openCheckIns = [];

        foreach ($allLogs as $log) {
            if ($log->action === 'check_in') {
                // Push the check-in log onto a stack for its workplace
                $openCheckIns[$log->workplace_id][] = $log;
            } elseif ($log->action === 'check_out' && !empty($openCheckIns[$log->workplace_id])) {
                // Pop the last check-in for that workplace and calculate duration
                $checkInLog = array_pop($openCheckIns[$log->workplace_id]);
                $totalMinutes += $checkInLog->timestamp->diffInMinutes($log->timestamp);
            }
        }

        $attendance->total_hours = $totalMinutes > 0 ? $totalMinutes : null;

        $attendance->save();
    }

    /**
     * Get absence records for a user (calculated from attendance gaps)
     */
    public function getAbsenceRecords($userId = null, Request $request)
    {
        try {
            if (!$userId) {
                return response()->json(['error' => 'User ID is required'], 400);
            }
            
            $user = User::find($userId);
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            // Get date range from request or default to current month
            $startDate = Carbon::parse($request->input('start_date', Carbon::now('Asia/Manila')->startOfMonth()->format('Y-m-d')), 'Asia/Manila');
            $endDate = Carbon::parse($request->input('end_date', Carbon::now('Asia/Manila')->endOfMonth()->format('Y-m-d')), 'Asia/Manila');
            $today = Carbon::now('Asia/Manila');

            // Don't count future dates - limit end date to today
            if ($endDate->gt($today)) {
                $endDate = $today->copy();
            }

            // Don't count dates before user account was created
            $userCreatedDate = Carbon::parse($user->created_at, 'Asia/Manila')->startOfDay();
            if ($startDate->lt($userCreatedDate)) {
                $startDate = $userCreatedDate->copy();
            }

            // Get all attendance records in date range
            // Include records with check-ins OR excused status (approved absences)
            $attendances = Attendance::where('user_id', $userId)
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where(function($query) {
                    $query->whereNotNull('check_in_time')
                          ->orWhere('status', 'excused');
                })
                ->pluck('date')
                ->map(function($date) {
                    return Carbon::parse($date)->format('Y-m-d');
                })
                ->toArray();

            // Get approved absence requests in date range
            $approvedRequests = \App\Models\AbsenceRequest::where('user_id', $userId)
                ->where('status', 'approved')
                ->where(function($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                          ->orWhereBetween('end_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                          ->orWhere(function($q) use ($startDate, $endDate) {
                              $q->where('start_date', '<=', $startDate->format('Y-m-d'))
                                ->where('end_date', '>=', $endDate->format('Y-m-d'));
                          });
                })
                ->get();

            // Create a map of approved dates with reasons
            $approvedDates = [];
            foreach ($approvedRequests as $request) {
                $requestStart = Carbon::parse($request->start_date);
                $requestEnd = Carbon::parse($request->end_date);
                $current = $requestStart->copy();
                
                while ($current->lte($requestEnd)) {
                    if ($current->isWeekday()) {
                        $approvedDates[$current->format('Y-m-d')] = [
                            'reason' => $request->reason,
                            'admin_comment' => $request->admin_comment,
                            'approved_at' => $request->reviewed_at
                        ];
                    }
                    $current->addDay();
                }
            }

            // Calculate absences (workdays without attendance)
            $absences = [];
            $currentDate = $startDate->copy();
            
            while ($currentDate->lte($endDate)) {
                // Only check workdays (Monday to Friday) and only past/today dates
                if ($currentDate->isWeekday() && $currentDate->lte($today)) {
                    $dateStr = $currentDate->format('Y-m-d');
                    
                    // If no attendance record for this workday, it's an unexcused absence
                    // (Excused absences have attendance records with status='excused')
                    if (!in_array($dateStr, $attendances)) {
                        $absences[] = [
                            'date' => $dateStr,
                            'formatted_date' => $currentDate->format('M j, Y'),
                            'day_of_week' => $currentDate->format('l'),
                            'status' => 'unexcused',
                            'status_label' => 'Unexcused',
                            'status_class' => 'bg-red-100 text-red-800',
                            'reason' => 'No check-in recorded',
                            'admin_comment' => null,
                            'is_recent' => $currentDate->gte(Carbon::now('Asia/Manila')->subDays(7))
                        ];
                    }
                }
            $currentDate->addDay();
        }
        
        // Count excused absences directly from approved requests (not from absences array)
        $excusedCount = 0;
        foreach ($approvedDates as $dateStr => $data) {
            $approvedDate = Carbon::parse($dateStr);
            // Only count if it's a workday within the date range and not in the future
            if ($approvedDate->isWeekday() && 
                $approvedDate->gte($startDate) && 
                $approvedDate->lte($endDate) && 
                $approvedDate->lte($today)) {
                $excusedCount++;
            }
        }

        // Sort by date descending
        usort($absences, function($a, $b) {
            return strcmp($b['date'], $a['date']);
        });

        return response()->json([
            'success' => true,
            'absences' => $absences,
            'stats' => [
                'total' => count($absences) + $excusedCount, // Total includes both unexcused and excused
                'unexcused' => count($absences),
                'excused' => $excusedCount,
                'date_range' => [
                    'start' => $startDate->format('Y-m-d'),
                    'end' => $endDate->format('Y-m-d')
                ]
            ]
        ]);
        } catch (\Exception $e) {
            Log::error('Error getting absence records: ' . $e->getMessage(), [
                'userId' => $userId,
                'exception' => $e,
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while retrieving absence records: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get weekly absence summary
     */
    public function getWeeklyAbsenceSummary($userId = null)
    {
        if (!$userId) {
            return response()->json(['error' => 'User ID is required'], 400);
        }

        $user = User::find($userId);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Get current week in PH timezone (Monday to Friday)
        $now = Carbon::now('Asia/Manila');
        $monday = $now->copy()->startOfWeek();
        $friday = $monday->copy()->addDays(4);
        
        // Don't count dates before user account was created
        $userCreatedDate = Carbon::parse($user->created_at, 'Asia/Manila')->startOfDay();
        if ($monday->lt($userCreatedDate)) {
            $monday = $userCreatedDate->copy();
        }

        // Get attendance records for this week (including excused absences)
        $attendances = Attendance::where('user_id', $userId)
            ->whereBetween('date', [$monday->format('Y-m-d'), $friday->format('Y-m-d')])
            ->where(function($query) {
                $query->whereNotNull('check_in_time')
                      ->orWhere('status', 'excused');
            })
            ->pluck('date')
            ->map(function($date) {
                return Carbon::parse($date)->format('Y-m-d');
            })
            ->toArray();

        // Get approved absence requests for this week
        $approvedRequests = \App\Models\AbsenceRequest::where('user_id', $userId)
            ->where('status', 'approved')
            ->where(function($query) use ($monday, $friday) {
                $query->whereBetween('start_date', [$monday->format('Y-m-d'), $friday->format('Y-m-d')])
                      ->orWhereBetween('end_date', [$monday->format('Y-m-d'), $friday->format('Y-m-d')])
                      ->orWhere(function($q) use ($monday, $friday) {
                          $q->where('start_date', '<=', $monday->format('Y-m-d'))
                            ->where('end_date', '>=', $friday->format('Y-m-d'));
                      });
            })
            ->get();

        // Create a map of approved dates
        $approvedDates = [];
        foreach ($approvedRequests as $request) {
            $requestStart = Carbon::parse($request->start_date);
            $requestEnd = Carbon::parse($request->end_date);
            $current = $requestStart->copy();
            
            while ($current->lte($requestEnd)) {
                if ($current->isWeekday()) {
                    $approvedDates[$current->format('Y-m-d')] = $request->reason;
                }
                $current->addDay();
            }
        }

        // Calculate absences (only unexcused ones appear in list)
        $absences = [];
        $currentDate = $monday->copy();
        
        while ($currentDate->lte($friday)) {
            if ($currentDate->lte($now)) { // Only count past/today
                $dateStr = $currentDate->format('Y-m-d');
                
                // If no attendance record for this workday, it's an unexcused absence
                if (!in_array($dateStr, $attendances)) {
                    $absences[] = [
                        'date' => $currentDate->format('M j, Y'),
                        'day' => $currentDate->format('l'),
                        'status' => 'unexcused',
                        'reason' => 'No check-in recorded'
                    ];
                }
            }
            $currentDate->addDay();
        }
        
        // Count excused absences directly from approved requests
        $excusedCount = 0;
        foreach ($approvedDates as $dateStr => $reason) {
            $approvedDate = Carbon::parse($dateStr);
            // Only count if it's within the week range and not in the future
            if ($approvedDate->gte($monday) && 
                $approvedDate->lte($friday) && 
                $approvedDate->lte($now)) {
                $excusedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'week_start' => $monday->format('M j, Y'),
            'week_end' => $friday->format('M j, Y'),
            'total_absences' => count($absences) + $excusedCount,
            'unexcused_absences' => count($absences),
            'excused_absences' => $excusedCount,
            'absences' => $absences
        ]);
    }

    /**
     * Get monthly absence summary
     */
    public function getMonthlyAbsenceSummary($userId = null, Request $request)
    {
        if (!$userId) {
            return response()->json(['error' => 'User ID is required'], 400);
        }

        $user = User::find($userId);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Get month/year from request or default to current
        $month = $request->input('month', Carbon::now('Asia/Manila')->month);
        $year = $request->input('year', Carbon::now('Asia/Manila')->year);

        $startOfMonth = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Manila');
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $today = Carbon::now('Asia/Manila');

        // Don't count future dates
        if ($endOfMonth->gt($today)) {
            $endOfMonth = $today;
        }

        // Calculate total workdays in the month (up to today)
        $totalWorkdays = 0;
        $currentDate = $startOfMonth->copy();
        while ($currentDate->lte($endOfMonth)) {
            if ($currentDate->isWeekday() && !$currentDate->isWeekend()) {
                $totalWorkdays++;
            }
            $currentDate->addDay();
        }

        // Get attendance records (including excused absences)
        $attendances = Attendance::where('user_id', $userId)
            ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->where(function($query) {
                $query->whereNotNull('check_in_time')
                      ->orWhere('status', 'excused');
            })
            ->whereRaw('DAYOFWEEK(date) BETWEEN 2 AND 6')
            ->pluck('date')
            ->map(function($date) {
                return Carbon::parse($date)->format('Y-m-d');
            })
            ->toArray();

        // Get approved absence requests for this month
        $approvedRequests = \App\Models\AbsenceRequest::where('user_id', $userId)
            ->where('status', 'approved')
            ->where(function($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('start_date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
                      ->orWhereBetween('end_date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
                      ->orWhere(function($q) use ($startOfMonth, $endOfMonth) {
                          $q->where('start_date', '<=', $startOfMonth->format('Y-m-d'))
                            ->where('end_date', '>=', $endOfMonth->format('Y-m-d'));
                      });
            })
            ->get();

        // Create a map of approved dates
        $approvedDates = [];
        foreach ($approvedRequests as $request) {
            $requestStart = Carbon::parse($request->start_date);
            $requestEnd = Carbon::parse($request->end_date);
            $current = $requestStart->copy();
            
            while ($current->lte($requestEnd)) {
                if ($current->isWeekday()) {
                    $approvedDates[] = $current->format('Y-m-d');
                }
                $current->addDay();
            }
        }

        $presentDays = count($attendances);
        $totalAbsences = $totalWorkdays - $presentDays;
        
        // Count excused absences (absences with approved requests)
        $excusedAbsences = 0;
        $currentDate = $startOfMonth->copy();
        while ($currentDate->lte($endOfMonth)) {
            if ($currentDate->isWeekday() && !$currentDate->isWeekend()) {
                $dateStr = $currentDate->format('Y-m-d');
                // If absent and has approved request
                if (!in_array($dateStr, $attendances) && in_array($dateStr, $approvedDates)) {
                    $excusedAbsences++;
                }
            }
            $currentDate->addDay();
        }
        
        $attendanceRate = $totalWorkdays > 0 ? round(($presentDays / $totalWorkdays) * 100, 1) : 0;

        // Calculate absences grouped by week
        $absencesByWeek = $this->calculateAbsencesByWeek($userId, $startOfMonth, $endOfMonth, $attendances);

        return response()->json([
            'success' => true,
            'month' => $startOfMonth->format('F Y'),
            'total_workdays' => $totalWorkdays,
            'present_days' => $presentDays,
            'total_absences' => $totalAbsences,
            'unexcused_absences' => $totalAbsences - $excusedAbsences,
            'excused_absences' => $excusedAbsences,
            'attendance_rate' => $attendanceRate,
            'absences_by_week' => $absencesByWeek
        ]);
    }

    /**
     * Helper to calculate absences by week
     */
    private function calculateAbsencesByWeek($userId, $startOfMonth, $endOfMonth, $attendedDates)
    {
        $weeks = [];
        $currentWeekStart = $startOfMonth->copy()->startOfWeek();
        $today = Carbon::now('Asia/Manila');
        
        while ($currentWeekStart->lte($endOfMonth)) {
            $weekEnd = $currentWeekStart->copy()->endOfWeek();
            
            // Only include days within the month and up to today
            $weekStartDisplay = $currentWeekStart->gte($startOfMonth) ? $currentWeekStart : $startOfMonth;
            $weekEndDisplay = $weekEnd->lte($endOfMonth) ? $weekEnd : $endOfMonth;
            if ($weekEndDisplay->gt($today)) {
                $weekEndDisplay = $today;
            }
            
            // Count absences for this week
            $absenceCount = 0;
            $currentDate = $weekStartDisplay->copy();
            
            while ($currentDate->lte($weekEndDisplay)) {
                if ($currentDate->isWeekday() && !$currentDate->isWeekend()) {
                    if (!in_array($currentDate->format('Y-m-d'), $attendedDates)) {
                        $absenceCount++;
                    }
                }
                $currentDate->addDay();
            }
            
            $weeks[] = [
                'week_start' => $weekStartDisplay->format('M j'),
                'week_end' => $weekEndDisplay->format('M j'),
                'absence_count' => $absenceCount,
                'unexcused_count' => $absenceCount
            ];
            
            $currentWeekStart->addWeek();
        }
        
        return $weeks;
    }

    /**
     * Get today's check-in type status (regular or special)
     * Used to enforce mutual exclusivity
     */
    public function getTodayCheckinType($userId)
    {
        $today = now()->format('Y-m-d');
        
        // Check for special check-in
        $hasSpecialCheckin = AttendanceLog::where('user_id', $userId)
            ->whereDate('timestamp', $today)
            ->where('shift_type', 'special')
            ->exists();
        
        if ($hasSpecialCheckin) {
            return response()->json([
                'type' => 'special',
                'message' => 'You have used Special Check-In today',
                'can_use_regular' => false,
                'can_use_special' => true
            ]);
        }
        
        // Check for regular check-in
        $hasRegularCheckin = AttendanceLog::where('user_id', $userId)
            ->whereDate('timestamp', $today)
            ->where('shift_type', '!=', 'special')
            ->exists();
        
        if ($hasRegularCheckin) {
            return response()->json([
                'type' => 'regular',
                'message' => 'You have used Regular Check-In today',
                'can_use_regular' => true,
                'can_use_special' => false
            ]);
        }
        
        // No check-in yet today
        return response()->json([
            'type' => null,
            'message' => 'No check-in recorded yet today',
            'can_use_regular' => true,
            'can_use_special' => true
        ]);
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = Auth::user();
            
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'phone_number' => 'nullable|regex:/^\+?[0-9]{10,15}$/',
                'password' => 'nullable|string|min:6'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }
            
            $user->name = $request->name;
            
            if ($request->filled('phone_number')) {
                $user->phone_number = $request->phone_number;
            }
            
            if ($request->filled('password')) {
                $user->password = bcrypt($request->password);
            }
            
            $user->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Send check-in notification asynchronously (after response is sent)
     */
    private function sendCheckinNotificationAsync($data)
    {
        // Use fastcgi_finish_request if available (PHP-FPM)
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
        
        // Now send notification in background
        $user = User::find($data['user_id']);
        if (!$user) return;
        
        $workplace = isset($data['workplace_id']) 
            ? Workplace::find($data['workplace_id']) 
            : (object)['name' => $data['workplace_name'] ?? 'your workplace'];
        
        $workplaceName = is_object($workplace) ? $workplace->name : $data['workplace_name'];
        $shiftLabel = $data['shift_label'];
        $checkInTime = $data['check_in_time'];
        
        $message = "You have successfully checked in at {$checkInTime} ({$shiftLabel} shift) at {$workplaceName}. " . 
                   ($shiftLabel === 'SPECIAL' ? 'Stay safe!' : 'Have a productive day!');
        
        $this->sendCheckinNotification($user, $message);
    }
    
    /**
     * Send check-in notification based on admin settings
     */
    private function sendCheckinNotification($user, $message)
    {
        try {
            Log::info("CHECKIN NOTIFICATION: Starting for user {$user->email}");
            
            $notificationType = DB::table('system_settings')
                ->where('key', 'notification_type')
                ->value('value') ?? 'email';
            
            Log::info("CHECKIN NOTIFICATION: Type = {$notificationType}");
            
            if ($notificationType === 'email' || $notificationType === 'both') {
                Log::info("CHECKIN NOTIFICATION: Sending email to {$user->email}");
                $this->sendCheckinEmail($user->email, $user->name, $message);
                Log::info("CHECKIN NOTIFICATION: Email sent successfully");
            }
            
            if (($notificationType === 'sms' || $notificationType === 'both') && !empty($user->phone_number)) {
                Log::info("CHECKIN NOTIFICATION: Sending SMS to {$user->phone_number}");
                $this->sendCheckinSMS($user->phone_number, $message);
                Log::info("CHECKIN NOTIFICATION: SMS sent successfully");
            }
            
        } catch (\Exception $e) {
            Log::error("CHECKIN NOTIFICATION FAILED for {$user->email}: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
        }
    }
    
    /**
     * Send check-in email notification
     */
    private function sendCheckinEmail($email, $name, $message)
    {
        try {
            $html = "
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #10B981; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                    .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px; }
                    .button { display: inline-block; background: #4F46E5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin-top: 20px; }
                    .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
                    .success-icon { font-size: 48px; text-align: center; margin-bottom: 20px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>✓ Check-In Successful</h2>
                    </div>
                    <div class='content'>
                        <div class='success-icon'>✓</div>
                        <p>Hello <strong>{$name}</strong>,</p>
                        <p>{$message}</p>
                        <p style='margin-top: 20px;'>
                            <a href='" . url('/dashboard') . "' class='button'>View Dashboard</a>
                        </p>
                    </div>
                    <div class='footer'>
                        <p>This is an automated message from CIS-AM Attendance Monitoring System</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            Mail::html($html, function ($mail) use ($email) {
                $mail->to($email)->subject('Check-In Successful');
            });
            
        } catch (\Exception $e) {
            throw new \Exception("Email send failed: " . $e->getMessage());
        }
    }
    
    /**
     * Send check-in SMS notification
     */
    private function sendCheckinSMS($phone, $message)
    {
        try {
            $smsApiUrl = DB::table('system_settings')
                ->where('key', 'sms_api_url')
                ->value('value');
            
            if (!$smsApiUrl) {
                throw new \Exception('SMS API URL not configured');
            }
            
            $smsApiKey = getenv('SMS_API_KEY');
            if (!$smsApiKey) {
                throw new \Exception('SMS_API_KEY not set in environment');
            }
            
            $payload = json_encode([
                'gatewayUrl' => 'api.sms-gate.app',
                'phone' => $phone,
                'message' => $message,
                'senderName' => 'CIS-AM'
            ]);
            
            $ch = curl_init($smsApiUrl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $smsApiKey
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode < 200 || $httpCode >= 300) {
                throw new \Exception("SMS API returned HTTP {$httpCode}: {$response}");
            }
            
        } catch (\Exception $e) {
            throw new \Exception("SMS send failed: " . $e->getMessage());
        }
    }
}
