<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\Workplace;
use App\Models\AdminActivityLog;
use App\Exports\AttendanceReportExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdminReportController extends Controller
{
    /**
     * Get attendance reports based on filters
     */
    public function getAttendanceReports(Request $request)
    {
        try {
            $reportType = $request->input('report_type', 'weekly'); // weekly, monthly, individual
            $userId = $request->input('user_id', null);
            $workplaceId = $request->input('workplace_id', null);
            $startDate = $request->input('start_date', null);
            $endDate = $request->input('end_date', null);

            // Set default date ranges based on report type
            if (!$startDate || !$endDate) {
                if ($reportType === 'weekly') {
                    $startDate = Carbon::now()->startOfWeek()->format('Y-m-d');
                    $endDate = Carbon::now()->endOfWeek()->format('Y-m-d');
                } elseif ($reportType === 'monthly') {
                    $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
                    $endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
                } else {
                    $startDate = Carbon::now()->subDays(30)->format('Y-m-d');
                    $endDate = Carbon::now()->format('Y-m-d');
                }
            }

            $query = Attendance::with(['user', 'workplace', 'logs'])
                ->whereBetween('date', [$startDate, $endDate]);

            // Filter by user if individual report
            if ($userId) {
                $query->where('user_id', $userId);
            }

            // Filter by workplace
            if ($workplaceId) {
                $query->where('workplace_id', $workplaceId);
            }

            $attendances = $query->orderBy('date', 'desc')
                ->orderBy('check_in_time', 'desc')
                ->get();

            // Expand attendances into per-pair rows for special days and derive missing times from logs
            $expandedRows = $this->expandAttendancesToRows($attendances);

            // Get unique user count for attendance rate calculation
            $uniqueUserCount = $userId ? 1 : $attendances->pluck('user_id')->unique()->count();

            // Calculate statistics
            $stats = $this->calculateAttendanceStats($attendances, $startDate, $endDate, $uniqueUserCount);

            // Add absences if this is an individual employee report
            $response = [
                'success' => true,
                'data' => $attendances,
                // flattened rows for frontend display / exports where callers prefer pair-level rows
                'rows' => $expandedRows,
                'stats' => $stats,
                'filters' => [
                    'report_type' => $reportType,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'user_id' => $userId,
                    'workplace_id' => $workplaceId,
                ]
            ];

            // Include absences only for individual employee reports
            if ($userId) {
                $response['absences'] = $this->calculateIndividualAbsences($userId, $startDate, $endDate);
            }

            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Error generating attendance report: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while generating the report: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate attendance statistics
     */
    private function calculateAttendanceStats($attendances, $startDate, $endDate, $userCount = 1)
    {
        $totalRecords = $attendances->count();
        $presentCount = 0;
        $lateCount = 0;
        $absentCount = 0;
        $nonAssignedCount = 0;
        
        // Late threshold: 9:00 AM
        $lateTimeThreshold = Carbon::today()->setTime(9, 0, 0);
        
        // Calculate total hours worked and late arrivals
        $totalMinutes = 0;
        $totalLateMinutes = 0;
        
        foreach ($attendances as $attendance) {
            // Count non-assigned workplace check-ins
            if (isset($attendance->is_assigned_workplace) && !$attendance->is_assigned_workplace) {
                $nonAssignedCount++;
            }
            
            // Determine if present (has check-in) or absent
            $hasCheckedIn = $attendance->check_in_time !== null;
            
            if ($hasCheckedIn) {
                // Count as present (includes both on-time and late)
                $presentCount++;
                
                $checkIn = Carbon::parse($attendance->check_in_time);
                
                // Check if late (checked in after 9:00 AM)
                $dateThreshold = Carbon::parse($attendance->date)->setTime(9, 0, 0);
                
                if ($checkIn->gt($dateThreshold)) {
                    $lateCount++;
                    // Calculate how many minutes late
                    $lateMinutes = $checkIn->diffInMinutes($dateThreshold);
                    $totalLateMinutes += $lateMinutes;
                }
                
                // Calculate work hours
                if ($attendance->check_out_time) {
                    // Full day worked
                    $checkOut = Carbon::parse($attendance->check_out_time);
                    $workMinutes = $checkIn->diffInMinutes($checkOut);
                    
                    // Subtract break duration if exists
                    if ($attendance->break_duration) {
                        $workMinutes -= $attendance->break_duration;
                    }
                    
                    $totalMinutes += max(0, $workMinutes);
                } else {
                    // Still working (partial day)
                    $now = Carbon::now();
                    $workMinutes = $checkIn->diffInMinutes($now);
                    
                    // Subtract break duration if exists
                    if ($attendance->break_duration) {
                        $workMinutes -= $attendance->break_duration;
                    }
                    
                    $totalMinutes += max(0, $workMinutes);
                }
            } elseif ($attendance->status === 'absent') {
                // Explicitly marked as absent
                $absentCount++;
            }
        }

        // Convert minutes to hours
        $totalHours = round($totalMinutes / 60, 2);
        
        // Calculate total days in range
        $totalDays = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
        
        // Calculate working days (Monday to Friday only, excluding weekends)
        $workingDays = 0;
        $currentDate = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        while ($currentDate->lte($end)) {
            // 1 = Monday, 5 = Friday (Carbon's dayOfWeek)
            if ($currentDate->dayOfWeek >= Carbon::MONDAY && $currentDate->dayOfWeek <= Carbon::FRIDAY) {
                $workingDays++;
            }
            $currentDate->addDay();
        }
        
        // Calculate average hours per day (based on actual records)
        $avgHoursPerDay = $totalRecords > 0 ? round($totalHours / $totalRecords, 2) : 0;

        // Calculate attendance rates:
        // 1. Present Rate: Of the records that exist, how many were present?
        //    This shows quality of existing attendance records
        $presentRate = $totalRecords > 0 ? round(($presentCount / $totalRecords) * 100, 2) : 0;
        
        // 2. Attendance Rate: Depends on whether it's individual or all employees
        //    - Individual (userCount = 1): Records / Working Days
        //    - All Employees (userCount > 1): Records / (Working Days × User Count)
        $expectedRecords = $workingDays * $userCount; // Total expected attendance records
        $attendanceRate = $expectedRecords > 0 ? round(($totalRecords / $expectedRecords) * 100, 2) : 0;
        
        // Cap attendance rate at 100% (in case there are multiple records per day)
        $attendanceRate = min($attendanceRate, 100);

        return [
            'total_records' => $totalRecords,
            'present_count' => $presentCount,
            'late_count' => $lateCount,
            'non_assigned_count' => $nonAssignedCount,
            'absent_count' => $absentCount,
            'total_hours' => $totalHours,
            'total_late_minutes' => $totalLateMinutes,
            'avg_hours_per_day' => $avgHoursPerDay,
            'attendance_rate' => $attendanceRate, // Based on working days × user count
            'present_rate' => $presentRate, // Based on existing records
            'date_range' => [
                'start' => $startDate,
                'end' => $endDate,
                'total_days' => $totalDays,
                'working_days' => $workingDays,
                'user_count' => $userCount,
                'expected_records' => $expectedRecords
            ]
        ];
    }

    /**
     * Get individual employee report with attendance and absence data
     */
    public function getIndividualReport($userId, Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $user = User::with('workplaces')->findOrFail($userId);

        $attendances = Attendance::with('workplace')
            ->where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->get();

        $stats = $this->calculateAttendanceStats($attendances, $startDate, $endDate, 1); // Individual user = 1

        // Get attendance logs for detailed timeline
        $logs = AttendanceLog::where('user_id', $userId)
            ->whereBetween('action_time', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('action_time', 'desc')
            ->limit(50)
            ->get();

        // Also provide expanded rows for per-pair representation
        $expandedRows = $this->expandAttendancesToRows($attendances);

        // Calculate absence days for individual employee
        $absences = $this->calculateIndividualAbsences($userId, $startDate, $endDate);

        return response()->json([
            'success' => true,
            'user' => $user,
            'attendances' => $attendances,
            'rows' => $expandedRows,
            'logs' => $logs,
            'stats' => $stats,
            'absences' => $absences // Include absence days
        ]);
    }

    /**
     * Calculate absence days for an individual employee (workdays without attendance)
     */
    private function calculateIndividualAbsences($userId, $startDate, $endDate)
    {
        try {
            $start = Carbon::parse($startDate, 'Asia/Manila');
            $end = Carbon::parse($endDate, 'Asia/Manila');
            $today = Carbon::now('Asia/Manila');

            // Don't count future dates
            if ($end->gt($today)) {
                $end = $today;
            }

            // Get ALL dates where user has ANY attendance record (including excused absences)
            // This prevents duplicates - if there's a record in DB, don't calculate it as absence
            $attendedDates = Attendance::where('user_id', $userId)
                ->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
                ->pluck('date')
                ->map(function($date) {
                    return Carbon::parse($date)->format('Y-m-d');
                })
                ->toArray();

            // Find absent days (workdays without ANY attendance record)
            $absences = [];
            $currentDate = $start->copy();
            
            while ($currentDate->lte($end)) {
                // Only count weekdays (Monday-Friday)
                if ($currentDate->isWeekday()) {
                    $dateStr = $currentDate->format('Y-m-d');
                    
                    // Only add as absence if there's NO attendance record at all for this date
                    if (!in_array($dateStr, $attendedDates)) {
                        $absences[] = [
                            'date' => $dateStr,
                            'formatted_date' => $currentDate->format('M j, Y'),
                            'day_of_week' => $currentDate->format('l'),
                            'status' => 'absent',
                        ];
                    }
                }
                $currentDate->addDay();
            }

            return $absences;
        } catch (\Exception $e) {
            Log::error('Error calculating individual absences: ' . $e->getMessage(), [
                'userId' => $userId,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'exception' => $e
            ]);
            return [];
        }
    }

    /**
     * Export attendance report to Excel or CSV
     */
    public function exportReport(Request $request)
    {
        try {
            $reportType = $request->input('report_type', 'weekly');
            $userId = $request->input('user_id', null);
            $workplaceId = $request->input('workplace_id', null);
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $format = $request->input('format', 'excel'); // excel or csv

            // Get the data with logs
            $query = Attendance::with(['user', 'workplace', 'logs'])
                ->whereBetween('date', [$startDate, $endDate]);

            if ($userId) {
                $query->where('user_id', $userId);
            }

            if ($workplaceId) {
                $query->where('workplace_id', $workplaceId);
            }

            $attendances = $query->orderBy('date', 'desc')->get();
            
            // Get absences if this is an individual employee report
            $absences = [];
            if ($userId) {
                $absences = $this->calculateIndividualAbsences($userId, $startDate, $endDate);
            }
            
            // Expand to per-pair rows for exporting
            $rows = $this->expandAttendancesToRows($attendances);
            
            // Merge absences into rows for export
            if (!empty($absences)) {
                $user = User::find($userId);
                foreach ($absences as $absence) {
                    $rows->push((object)[
                        'date' => $absence['date'],
                        'user' => $user,
                        'workplace' => null,
                        'check_in_time' => null,
                        'check_out_time' => null,
                        'status' => 'absent',
                        'logs' => collect([]),
                        'is_absence' => true,
                        'day_of_week' => $absence['day_of_week']
                    ]);
                }
                
                // Sort by date descending
                $rows = $rows->sortByDesc('date')->values();
            }
            
            $recordCount = $rows->count();

            // Build log description
            $filters = [];
            if ($userId) {
                $user = User::find($userId);
                $filters[] = "User: {$user->name}";
            }
            if ($workplaceId) {
                $workplace = Workplace::find($workplaceId);
                $filters[] = "Workplace: {$workplace->name}";
            }
            $filters[] = "Date Range: {$startDate} to {$endDate}";
            $filters[] = "Report Type: {$reportType}";
            $filterString = implode(', ', $filters);

            // Log the export action
            $action = $format === 'csv' ? 'export_attendance_report_csv' : 'export_attendance_report_excel';
            AdminActivityLog::log(
                $action,
                "Exported {$recordCount} attendance records as " . strtoupper($format) . " ({$filterString})",
                'attendance_report',
                null,
                ['format' => $format, 'report_type' => $reportType, 'filters' => $filters, 'record_count' => $recordCount]
            );

            // Generate filename
            $filename = 'attendance_report_' . $reportType . '_' . Carbon::now()->format('Y-m-d_His');

            if ($format === 'csv') {
                return $this->exportToCsv($rows, $filename);
            } else {
                // Use modern PhpSpreadsheet for .xlsx export
                return $this->exportToExcel($rows, $filename, $reportType, $startDate, $endDate);
            }
        } catch (\Exception $e) {
            Log::error('Error exporting attendance report: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while exporting the report: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format minutes to hours and minutes display
     */
    private function formatHoursMinutes($totalMinutes)
    {
        if (!$totalMinutes || $totalMinutes <= 0) {
            return '0mins';
        }
        
        $hours = floor($totalMinutes / 60);
        $minutes = round($totalMinutes % 60);
        
        if ($hours > 0 && $minutes > 0) {
            return sprintf('%dhr%s %dmin%s', $hours, $hours > 1 ? 's' : '', $minutes, $minutes > 1 ? 's' : '');
        } elseif ($hours > 0) {
            return sprintf('%dhr%s', $hours, $hours > 1 ? 's' : '');
        } else {
            return sprintf('%dmin%s', $minutes, $minutes > 1 ? 's' : '');
        }
    }

    /**
     * Export to modern Excel format (.xlsx) using PhpSpreadsheet
     */
    private function exportToExcel($attendances, $filename, $reportType, $startDate, $endDate)
    {
        $export = new AttendanceReportExport($attendances, $reportType, $startDate, $endDate);
        $export->download($filename);
    }

    /**
     * Export to CSV format
     */
    private function exportToCsv($attendances, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ];

        $callback = function() use ($attendances) {
            $file = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($file, [
                'Date',
                'Employee Name',
                'Email',
                'Workplace',
                'Check In',
                'Check Out',
                'Status',
                'Hours Worked',
                'Late',
                'Notes'
            ]);

            // Add data rows (attendances here may already be expanded per-pair)
            foreach ($attendances as $attendance) {
                // Use provided check_in_time / check_out_time if present
                $checkInTime = $attendance->check_in_time ?? ($attendance->check_in ?? null);
                $checkOutTime = $attendance->check_out_time ?? ($attendance->check_out ?? null);

                // If still null, try to derive from logs if present
                if (!$checkInTime && isset($attendance->logs) && $attendance->logs && $attendance->logs->count() > 0) {
                    $checkInLog = $attendance->logs->firstWhere('action', 'check_in');
                    $checkInTime = $checkInLog->timestamp ?? null;
                }
                if (!$checkOutTime && isset($attendance->logs) && $attendance->logs && $attendance->logs->count() > 0) {
                    // Use last check_out if exists
                    $checkOutLog = $attendance->logs->where('action', 'check_out')->last();
                    if ($checkOutLog) $checkOutTime = $checkOutLog->timestamp ?? null;
                }

                // Calculate hours worked
                $hoursWorked = 0;
                $lateMinutes = 0;

                if ($checkInTime && $checkOutTime) {
                    $checkIn = new \DateTime($checkInTime);
                    $checkOut = new \DateTime($checkOutTime);

                    $workMinutes = ($checkOut->getTimestamp() - $checkIn->getTimestamp()) / 60;

                    if (!empty($attendance->break_duration)) {
                        $workMinutes -= $attendance->break_duration;
                    }

                    $hoursWorked = round(max(0, $workMinutes) / 60, 2);

                    // Check if late
                    $checkInHour = (int)$checkIn->format('H');
                    $checkInMinute = (int)$checkIn->format('i');
                    $checkInTotalMinutes = ($checkInHour * 60) + $checkInMinute;

                    if ($checkInTotalMinutes > 540) { // 9:00 AM
                        $lateMinutes = $checkInTotalMinutes - 540;
                    }
                }

                // Format check-in and check-out times
                $checkInDisplay = $checkInTime ? date('H:i', strtotime($checkInTime)) : 'N/A';
                $checkOutDisplay = $checkOutTime ? date('H:i', strtotime($checkOutTime)) : 'N/A';

                fputcsv($file, [
                    $attendance->date,
                    $attendance->user->name ?? 'N/A',
                    $attendance->user->email ?? 'N/A',
                    $attendance->workplace->name ?? 'N/A',
                    $checkInDisplay,
                    $checkOutDisplay,
                    ucfirst($attendance->status ?? 'N/A'),
                    $this->formatHoursMinutes($hoursWorked * 60), // Convert hours to minutes
                    $this->formatHoursMinutes($lateMinutes),
                    $attendance->notes ?? ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get summary statistics for dashboard
     */
    /**
     * Expand attendances into per-pair rows and derive missing check in/out from logs
     * Returns a collection of row-like objects with properties used by exports and frontend
     */
    public function expandAttendancesToRows($attendances)
    {
        $rows = [];

        foreach ($attendances as $attendance) {
            $logs = $attendance->logs ?? collect();

            // Normalize and sort logs by timestamp / action_time
            $sorted = $logs->sortBy(function ($l) {
                return $l->timestamp ?? ($l->action_time ?? null);
            })->values();

            $isSpecial = ($attendance->status === 'special');
            if (!$isSpecial) {
                // Also consider legacy logs flagged by shift_type/type
                foreach ($sorted as $l) {
                    if (isset($l->type) && $l->type === 'special') { $isSpecial = true; break; }
                    if (isset($l->shift_type) && $l->shift_type === 'special') { $isSpecial = true; break; }
                }
            }

            if ($isSpecial) {
                // Pair check_in / check_out per workplace using a stack
                $open = [];
                foreach ($sorted as $log) {
                    $action = $log->action ?? null;
                    $wpId = $log->workplace_id ?? ($attendance->workplace_id ?? null);
                    $time = $log->timestamp ?? ($log->action_time ?? null);

                    if ($action === 'check_in') {
                        if (!isset($open[$wpId])) $open[$wpId] = [];
                        $open[$wpId][] = $log;
                    } elseif ($action === 'check_out') {
                        $in = null;
                        if (isset($open[$wpId]) && count($open[$wpId]) > 0) {
                            $in = array_pop($open[$wpId]);
                        }

                        $checkInTime = $in ? ($in->timestamp ?? ($in->action_time ?? null)) : null;
                        $checkOutTime = $time;

                        // prefer workplace from check-in log, then check-out log, then attendance
                        $wp = null;
                        if ($in && isset($in->workplace) && $in->workplace) {
                            $wp = $in->workplace;
                        } elseif (isset($log->workplace) && $log->workplace) {
                            $wp = $log->workplace;
                        } else {
                            $wp = $attendance->workplace ?? null;
                        }

                        $rows[] = (object) [
                            'attendance_id' => $attendance->id,
                            'date' => $attendance->date,
                            'user' => $attendance->user ?? null,
                            'workplace' => $wp,
                            'status' => $attendance->status,
                            'check_in_time' => $checkInTime,
                            'check_out_time' => $checkOutTime,
                            'break_duration' => $attendance->break_duration ?? null,
                            'notes' => $attendance->notes ?? null,
                            'logs' => null,
                        ];
                    }
                }

                // Close any open check-ins (still working)
                foreach ($open as $wpId => $stack) {
                    while (count($stack) > 0) {
                        $in = array_pop($stack);
                        $checkInTime = $in ? ($in->timestamp ?? ($in->action_time ?? null)) : null;
                        $wp = $in && isset($in->workplace) ? $in->workplace : ($attendance->workplace ?? null);

                        $rows[] = (object) [
                            'attendance_id' => $attendance->id,
                            'date' => $attendance->date,
                            'user' => $attendance->user ?? null,
                            'workplace' => $wp,
                            'status' => $attendance->status,
                            'check_in_time' => $checkInTime,
                            'check_out_time' => null,
                            'break_duration' => $attendance->break_duration ?? null,
                            'notes' => $attendance->notes ?? null,
                            'logs' => null,
                        ];
                    }
                }
            } else {
                // Non-special: single row, derive missing times from logs
                $checkIn = $attendance->check_in_time ?? null;
                $checkOut = $attendance->check_out_time ?? null;

                if (!$checkIn) {
                    $c = $sorted->firstWhere('action', 'check_in');
                    $checkIn = $c ? ($c->timestamp ?? ($c->action_time ?? null)) : null;
                }
                if (!$checkOut) {
                    // last check_out in logs
                    $co = null;
                    $filtered = $sorted->where('action', 'check_out');
                    if ($filtered->count() > 0) {
                        $co = $filtered->last();
                    }
                    $checkOut = $co ? ($co->timestamp ?? ($co->action_time ?? null)) : null;
                }

                // derive workplace from logs if available
                $wp = null;
                $firstLog = $sorted->first();
                if ($firstLog && isset($firstLog->workplace) && $firstLog->workplace) {
                    $wp = $firstLog->workplace;
                } else {
                    $wp = $attendance->workplace ?? null;
                }

                $rows[] = (object) [
                    'attendance_id' => $attendance->id,
                    'date' => $attendance->date,
                    'user' => $attendance->user ?? null,
                    'workplace' => $wp,
                    'status' => $attendance->status,
                    'check_in_time' => $checkIn,
                    'check_out_time' => $checkOut,
                    'break_duration' => $attendance->break_duration ?? null,
                    'notes' => $attendance->notes ?? null,
                    'logs' => $attendance->logs ?? null,
                ];
            }
        }

        return collect($rows);
    }

    public function getSummaryStats(Request $request)
    {
        $period = $request->input('period', 'today'); // today, week, month

        $startDate = match($period) {
            'today' => Carbon::today(),
            'week' => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
            default => Carbon::today()
        };

        $endDate = Carbon::now();

        $attendances = Attendance::whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])->get();
        
        // Get unique user count for attendance rate calculation
        $uniqueUserCount = $attendances->pluck('user_id')->unique()->count();
        if ($uniqueUserCount === 0) $uniqueUserCount = 1; // Avoid division by zero
        
        return response()->json([
            'success' => true,
            'stats' => $this->calculateAttendanceStats($attendances, $startDate->format('Y-m-d'), $endDate->format('Y-m-d'), $uniqueUserCount)
        ]);
    }

}
