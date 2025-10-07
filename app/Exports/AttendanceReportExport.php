<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Modern Excel export using PhpSpreadsheet
 * Generates .xlsx files compatible with Excel 2007+
 */
class AttendanceReportExport
{
    public $attendances;
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
     * Generate the Excel file and return the Spreadsheet object
     */
    public function generate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Attendance Report');

        // Set headers
        $headers = [
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
        
        $sheet->fromArray($headers, null, 'A1');

        // Add data rows
        $row = 2;
        foreach ($this->attendances as $attendance) {
            // Calculate hours worked properly
            $hoursWorked = 0;
            $lateMinutes = 0;
            $status = ucfirst($attendance->status ?? 'N/A');
            
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
            
            if ($checkInTime && $checkOutTime) {
                // Calculate work hours from check-in to check-out
                $checkIn = new \DateTime($checkInTime);
                $checkOut = new \DateTime($checkOutTime);
                
                $workMinutes = ($checkOut->getTimestamp() - $checkIn->getTimestamp()) / 60;
                
                // Subtract break duration if exists
                if ($attendance->break_duration) {
                    $workMinutes -= $attendance->break_duration;
                }
                
                $hoursWorked = round(max(0, $workMinutes) / 60, 2);
                
                // Check if late (after 9:00 AM)
                $checkInHour = (int)$checkIn->format('H');
                $checkInMinute = (int)$checkIn->format('i');
                $checkInTotalMinutes = ($checkInHour * 60) + $checkInMinute;
                $lateThreshold = (9 * 60); // 9:00 AM in minutes
                
                if ($checkInTotalMinutes > $lateThreshold) {
                    $lateMinutes = $checkInTotalMinutes - $lateThreshold;
                    if ($status !== 'Absent') {
                        $status = 'Late';
                    }
                } else {
                    if ($status !== 'Absent') {
                        $status = 'Present';
                    }
                }
            } elseif ($checkInTime && !$checkOutTime) {
                // Still working (partial day)
                $checkIn = new \DateTime($checkInTime);
                $now = new \DateTime();
                
                $workMinutes = ($now->getTimestamp() - $checkIn->getTimestamp()) / 60;
                
                // Subtract break duration if exists
                if ($attendance->break_duration) {
                    $workMinutes -= $attendance->break_duration;
                }
                
                $hoursWorked = round(max(0, $workMinutes) / 60, 2);
                
                // Check if late
                $checkInHour = (int)$checkIn->format('H');
                $checkInMinute = (int)$checkIn->format('i');
                $checkInTotalMinutes = ($checkInHour * 60) + $checkInMinute;
                $lateThreshold = (9 * 60);
                
                if ($checkInTotalMinutes > $lateThreshold) {
                    $lateMinutes = $checkInTotalMinutes - $lateThreshold;
                    $status = 'Late (Working)';
                } else {
                    $status = 'Present (Working)';
                }
            }
            
            // Format check-in and check-out times for display
            $checkInDisplay = $checkInTime ? date('h:i A', strtotime($checkInTime)) : 'N/A';
            $checkOutDisplay = $checkOutTime ? date('h:i A', strtotime($checkOutTime)) : 'N/A';
            
            // Format date
            $date = date('m/d/Y', strtotime($attendance->date));
            
            $rowData = [
                $date,
                $attendance->user->name ?? 'N/A',
                $attendance->user->email ?? 'N/A',
                $attendance->workplace->name ?? 'N/A',
                $checkInDisplay,
                $checkOutDisplay,
                $status,
                $hoursWorked,
                $lateMinutes, // Show late minutes instead of break duration
                $attendance->notes ?? ''
            ];
            
            $sheet->fromArray($rowData, null, 'A' . $row);
            $row++;
        }

        $lastRow = $row - 1;

        // Style header row
        $headerStyle = [
            'font' => [
                'name' => 'Bookman Old Style',
                'size' => 11,
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F0F0F0'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);

        // Set row heights
        $sheet->getRowDimension(1)->setRowHeight(20);
        for ($i = 2; $i <= $lastRow; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(18);
        }

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(15);  // Date
        $sheet->getColumnDimension('B')->setWidth(25);  // Employee Name
        $sheet->getColumnDimension('C')->setWidth(30);  // Email
        $sheet->getColumnDimension('D')->setWidth(30);  // Workplace
        $sheet->getColumnDimension('E')->setWidth(12);  // Time In
        $sheet->getColumnDimension('F')->setWidth(12);  // Time Out
        $sheet->getColumnDimension('G')->setWidth(12);  // Status
        $sheet->getColumnDimension('H')->setWidth(15);  // Hours Worked
        $sheet->getColumnDimension('I')->setWidth(18);  // Late Duration
        $sheet->getColumnDimension('J')->setWidth(30);  // Notes

        // Apply borders and font to all data cells
        $dataStyle = [
            'font' => [
                'name' => 'Bookman Old Style',
                'size' => 11,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];
        $sheet->getStyle("A2:J{$lastRow}")->applyFromArray($dataStyle);

        // Center align specific columns
        $centerColumns = ['E', 'F', 'G', 'H', 'I'];
        foreach ($centerColumns as $col) {
            $sheet->getStyle("{$col}2:{$col}{$lastRow}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        return $spreadsheet;
    }

    /**
     * Save the Excel file to a given path
     */
    public function save($filePath)
    {
        $spreadsheet = $this->generate();
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);
        return $filePath;
    }

    /**
     * Download the Excel file
     */
    public function download($filename)
    {
        $spreadsheet = $this->generate();
        
        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
