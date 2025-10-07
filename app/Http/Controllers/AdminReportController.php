<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\Workplace;
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

        $query = Attendance::with(['user', 'workplace'])
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

        // Calculate statistics
        $stats = $this->calculateAttendanceStats($attendances, $startDate, $endDate);

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
    private function calculateAttendanceStats($attendances, $startDate, $endDate)
    {
        $totalRecords = $attendances->count();
        $presentCount = $attendances->where('status', 'present')->count();
        $lateCount = $attendances->where('status', 'late')->count();
        $absentCount = $attendances->where('status', 'absent')->count();
        
        // Calculate total hours worked (total_hours is in minutes in DB)
        $totalMinutes = 0;
        $totalLateMinutes = 0;
        
        foreach ($attendances as $attendance) {
            if ($attendance->total_hours) {
                $totalMinutes += $attendance->total_hours; // Already in minutes
            }
            // Calculate late duration if check-in time is after expected time
            // For now, we'll assume late duration is calculated elsewhere or use break_duration
            if ($attendance->break_duration) {
                $totalLateMinutes += $attendance->break_duration;
            }
        }

        // Convert minutes to hours
        $totalHours = round($totalMinutes / 60, 2);
        
        // Calculate average hours per day
        $days = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
        $avgHoursPerDay = $totalRecords > 0 ? round($totalHours / $totalRecords, 2) : 0;

        return [
            'total_records' => $totalRecords,
            'present_count' => $presentCount,
            'late_count' => $lateCount,
            'absent_count' => $absentCount,
            'total_hours' => $totalHours,
            'total_late_minutes' => $totalLateMinutes,
            'avg_hours_per_day' => $avgHoursPerDay,
            'attendance_rate' => $totalRecords > 0 ? round(($presentCount / $totalRecords) * 100, 2) : 0,
            'date_range' => [
                'start' => $startDate,
                'end' => $endDate,
                'days' => $days
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

        $stats = $this->calculateAttendanceStats($attendances, $startDate, $endDate);

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
     * Export attendance report to CSV
     */
    public function exportReport(Request $request)
    {
        $reportType = $request->input('report_type', 'weekly');
        $userId = $request->input('user_id', null);
        $workplaceId = $request->input('workplace_id', null);
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $format = $request->input('format', 'csv'); // csv or excel

        // Get the data
        $query = Attendance::with(['user', 'workplace'])
            ->whereBetween('date', [$startDate, $endDate]);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($workplaceId) {
            $query->where('workplace_id', $workplaceId);
        }

        $attendances = $query->orderBy('date', 'desc')->get();

        // Generate filename
        $filename = 'attendance_report_' . $reportType . '_' . Carbon::now()->format('Y-m-d_His');

        if ($format === 'csv') {
            return $this->exportToCsv($attendances, $filename);
        } else {
            // Export as tab-delimited format that Excel can open with formatting
            return $this->exportToExcelCompatible($attendances, $filename);
        }
    }

    /**
     * Export to Excel-compatible format (tab-delimited with HTML table)
     */
    private function exportToExcelCompatible($attendances, $filename)
    {
        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        $html .= '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><style>';
        $html .= 'table { border-collapse: collapse; font-family: "Bookman Old Style", serif; font-size: 11pt; }';
        $html .= 'th { background-color: #f0f0f0; font-weight: bold; text-align: center; border: 1px solid black; padding: 8px; height: 20px; }';
        $html .= 'td { border: 1px solid black; padding: 6px; height: 18px; vertical-align: middle; }';
        $html .= '.center { text-align: center; }';
        $html .= '</style></head><body>';
        $html .= '<table>';
        
        // Header row
        $html .= '<tr>';
        $html .= '<th width="100">Date</th>';
        $html .= '<th width="150">Employee Name</th>';
        $html .= '<th width="180">Email</th>';
        $html .= '<th width="180">Workplace</th>';
        $html .= '<th width="80">Time In</th>';
        $html .= '<th width="80">Time Out</th>';
        $html .= '<th width="80">Status</th>';
        $html .= '<th width="100">Hours Worked</th>';
        $html .= '<th width="120">Late Duration (min)</th>';
        $html .= '<th width="180">Notes</th>';
        $html .= '</tr>';
        
        // Data rows
        foreach ($attendances as $attendance) {
            $hoursWorked = $attendance->total_hours ? round($attendance->total_hours / 60, 2) : 0;
            $checkInTime = $attendance->check_in_time ? date('h:i A', strtotime($attendance->check_in_time)) : 'N/A';
            $checkOutTime = $attendance->check_out_time ? date('h:i A', strtotime($attendance->check_out_time)) : 'N/A';
            $date = date('m/d/Y', strtotime($attendance->date));
            
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($date) . '</td>';
            $html .= '<td>' . htmlspecialchars($attendance->user->name ?? 'N/A') . '</td>';
            $html .= '<td>' . htmlspecialchars($attendance->user->email ?? 'N/A') . '</td>';
            $html .= '<td>' . htmlspecialchars($attendance->workplace->name ?? 'N/A') . '</td>';
            $html .= '<td class="center">' . htmlspecialchars($checkInTime) . '</td>';
            $html .= '<td class="center">' . htmlspecialchars($checkOutTime) . '</td>';
            $html .= '<td class="center">' . htmlspecialchars(ucfirst($attendance->status)) . '</td>';
            $html .= '<td class="center">' . $hoursWorked . '</td>';
            $html .= '<td class="center">' . ($attendance->break_duration ?? 0) . '</td>';
            $html .= '<td>' . htmlspecialchars($attendance->notes ?? '') . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</table></body></html>';
        
        // Set headers for Excel download
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
        header('Cache-Control: max-age=0');
        
        echo $html;
        exit;
    }

    /**
     * Old PHPExcel method - Not used due to PHP 8.4 incompatibility
     */
    private function exportToExcel_OLD($attendances, $filename)
    {
        // This method is disabled because PHPExcel is not compatible with PHP 8.4
        // Using exportToExcelCompatible() instead
        return $this->exportToExcelCompatible($attendances, $filename);
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
                'Time In',
                'Time Out',
                'Status',
                'Hours Worked',
                'Late Duration (min)',
                'Notes'
            ]);

            // Add data rows
            foreach ($attendances as $attendance) {
                // Calculate hours worked from minutes
                $hoursWorked = $attendance->total_hours ? round($attendance->total_hours / 60, 2) : 0;
                
                // Format check-in and check-out times
                $checkInTime = $attendance->check_in_time ? date('H:i', strtotime($attendance->check_in_time)) : 'N/A';
                $checkOutTime = $attendance->check_out_time ? date('H:i', strtotime($attendance->check_out_time)) : 'N/A';
                
                fputcsv($file, [
                    $attendance->date,
                    $attendance->user->name ?? 'N/A',
                    $attendance->user->email ?? 'N/A',
                    $attendance->workplace->name ?? 'N/A',
                    $checkInTime,
                    $checkOutTime,
                    ucfirst($attendance->status),
                    $hoursWorked,
                    $attendance->break_duration ?? '0',
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
        
        return response()->json([
            'success' => true,
            'stats' => $this->calculateAttendanceStats($attendances, $startDate->format('Y-m-d'), $endDate->format('Y-m-d'))
        ]);
    }
}
