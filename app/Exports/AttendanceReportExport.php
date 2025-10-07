<?php

namespace App\Exports;

// This class is for the old maatwebsite/excel v1.x (Laravel 4)
// which uses PHPExcel library

class AttendanceReportExport
{
    public $attendances;  // Changed to public
    public $reportType;
    public $startDate;
    public $endDate;

    public function __construct($attendances, $reportType, $startDate, $endDate)
    {
        $this->attendances = $attendances;
        $this->reportType = $reportType;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * Build the Excel file using the old Excel facade
     */
    public function build()
    {
        \Excel::create('Attendance_Report', function($excel) {
            $excel->sheet('Attendance', function($sheet) {
                // Prepare data array
                $data = [];
                
                // Add headers
                $data[] = [
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
                ];
                
                // Add data rows
                foreach ($this->attendances as $attendance) {
                    // Calculate hours worked from minutes
                    $hoursWorked = $attendance->total_hours ? round($attendance->total_hours / 60, 2) : 0;
                    
                    // Format check-in and check-out times
                    $checkInTime = $attendance->check_in_time ? date('h:i A', strtotime($attendance->check_in_time)) : 'N/A';
                    $checkOutTime = $attendance->check_out_time ? date('h:i A', strtotime($attendance->check_out_time)) : 'N/A';
                    
                    // Format date
                    $date = date('m/d/Y', strtotime($attendance->date));
                    
                    $data[] = [
                        $date,
                        $attendance->user->name ?? 'N/A',
                        $attendance->user->email ?? 'N/A',
                        $attendance->workplace->name ?? 'N/A',
                        $checkInTime,
                        $checkOutTime,
                        ucfirst($attendance->status),
                        $hoursWorked,
                        ($attendance->break_duration ?? 0),
                        $attendance->notes ?? ''
                    ];
                }
                
                // Set the data
                $sheet->fromArray($data, null, 'A1', false, false);
                
                // Get row count
                $rowCount = count($data);
                
                // Style header row (row 1)
                $sheet->row(1, function($row) {
                    $row->setFont([
                        'name' => 'Bookman Old Style',
                        'size' => 11,
                        'bold' => true
                    ]);
                    $row->setAlignment('center');
                    $row->setValignment('center');
                });
                
                // Set row heights
                $sheet->setHeight(1, 20);
                for ($i = 2; $i <= $rowCount; $i++) {
                    $sheet->setHeight($i, 18);
                }
                
                // Set column widths
                $sheet->setWidth('A', 15);  // Date
                $sheet->setWidth('B', 25);  // Employee Name
                $sheet->setWidth('C', 30);  // Email
                $sheet->setWidth('D', 30);  // Workplace
                $sheet->setWidth('E', 12);  // Time In
                $sheet->setWidth('F', 12);  // Time Out
                $sheet->setWidth('G', 12);  // Status
                $sheet->setWidth('H', 15);  // Hours Worked
                $sheet->setWidth('I', 18);  // Late Duration
                $sheet->setWidth('J', 30);  // Notes
                
                // Apply borders and font to all cells
                $sheet->cells("A1:J{$rowCount}", function($cells) {
                    $cells->setFont([
                        'name' => 'Bookman Old Style',
                        'size' => 11
                    ]);
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                    $cells->setValignment('center');
                });
                
                // Center align specific columns (data rows only)
                if ($rowCount > 1) {
                    $sheet->cells("E2:E{$rowCount}", function($cells) {
                        $cells->setAlignment('center');
                    });
                    $sheet->cells("F2:F{$rowCount}", function($cells) {
                        $cells->setAlignment('center');
                    });
                    $sheet->cells("G2:G{$rowCount}", function($cells) {
                        $cells->setAlignment('center');
                    });
                    $sheet->cells("H2:H{$rowCount}", function($cells) {
                        $cells->setAlignment('center');
                    });
                    $sheet->cells("I2:I{$rowCount}", function($cells) {
                        $cells->setAlignment('center');
                    });
                }
            });
        });
    }
}
