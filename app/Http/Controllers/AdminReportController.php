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
use Carbon\Carbon;

class AdminReportController extends Controller
{
    /**
     * Get attendance reports based on filters
     */
    public function getAttendanceReports(Request $request)
    {
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

        // Get unique user count for attendance rate calculation
        $uniqueUserCount = $userId ? 1 : $attendances->pluck('user_id')->unique()->count();

        // Calculate statistics
        $stats = $this->calculateAttendanceStats($attendances, $startDate, $endDate, $uniqueUserCount);

        return response()->json([
            'success' => true,
            'data' => $attendances,
            'stats' => $stats,
            'filters' => [
                'report_type' => $reportType,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'user_id' => $userId,
                'workplace_id' => $workplaceId,
            ]
        ]);
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
        
        // Late threshold: 9:00 AM
        $lateTimeThreshold = Carbon::today()->setTime(9, 0, 0);
        
        // Calculate total hours worked and late arrivals
        $totalMinutes = 0;
        $totalLateMinutes = 0;
        
        foreach ($attendances as $attendance) {
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
     * Get individual employee report
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

        return response()->json([
            'success' => true,
            'user' => $user,
            'attendances' => $attendances,
            'logs' => $logs,
            'stats' => $stats
        ]);
    }

    /**
     * Export attendance report to Excel or CSV
     */
    public function exportReport(Request $request)
    {
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
        $recordCount = $attendances->count();

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
            return $this->exportToCsv($attendances, $filename);
        } else {
            // Use modern PhpSpreadsheet for .xlsx export
            return $this->exportToExcel($attendances, $filename, $reportType, $startDate, $endDate);
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

            // Add data rows
            foreach ($attendances as $attendance) {
                // Get check-in and check-out from logs if available
                $checkInLog = null;
                $checkOutLog = null;
                
                if ($attendance->logs && $attendance->logs->count() > 0) {
                    $checkInLog = $attendance->logs->firstWhere('action', 'check_in');
                    $checkOutLog = $attendance->logs->firstWhere('action', 'check_out');
                }
                
                // Use log timestamps or fall back to attendance table fields
                $checkInTime = null;
                $checkOutTime = null;
                
                if ($checkInLog && $checkInLog->timestamp) {
                    $checkInTime = $checkInLog->timestamp;
                } elseif ($attendance->check_in_time) {
                    $checkInTime = $attendance->check_in_time;
                }
                
                if ($checkOutLog && $checkOutLog->timestamp) {
                    $checkOutTime = $checkOutLog->timestamp;
                } elseif ($attendance->check_out_time) {
                    $checkOutTime = $attendance->check_out_time;
                }
                
                // Calculate hours worked
                $hoursWorked = 0;
                $lateMinutes = 0;
                
                if ($checkInTime && $checkOutTime) {
                    $checkIn = new \DateTime($checkInTime);
                    $checkOut = new \DateTime($checkOutTime);
                    
                    $workMinutes = ($checkOut->getTimestamp() - $checkIn->getTimestamp()) / 60;
                    
                    if ($attendance->break_duration) {
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
                    ucfirst($attendance->status),
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
