# Excused Absence Display Fix

## Issue Description
When an absence request was approved for a date (e.g., 11/11/2025), the attendance history was showing "Still working" instead of properly displaying it as an "Excused" absence.

### Root Cause
1. When an absence request is approved, the system creates an attendance record with:
   - `status = 'excused'`
   - `check_in_time = null`
   - `check_out_time = null`
   - `notes` containing the reason and admin comments

2. The `getAttendanceHistory()` method in `DashboardController.php` was only handling these cases:
   - Special check-ins (with multiple check-in/out pairs)
   - Regular attendance (with check-in times)
   
3. For any attendance without `check_in_time`, it defaulted to showing "Still working" as the check-out value, which was incorrect for excused absences.

## Solution

### Changes Made

#### 1. Updated `getAttendanceHistory()` Method
**File:** `app/Http/Controllers/Api/DashboardController.php`

Added a new condition to handle excused absences before processing regular attendance:

```php
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
```

**Key Points:**
- Check-in and check-out are displayed as `--` instead of "Still working"
- Status is properly set to `Excused`
- The notes from the approved absence request are included
- Blue badge styling is applied (same as special check-ins)

#### 2. Updated `getStatusClass()` Method
Added handling for the 'excused' status:

```php
private function getStatusClass($status)
{
    return match(strtolower($status)) {
        'present' => 'bg-green-100 text-green-800',
        'late' => 'bg-yellow-100 text-yellow-800', 
        'absent' => 'bg-red-100 text-red-800',
        'special' => 'bg-blue-100 text-blue-800',
        'excused' => 'bg-blue-100 text-blue-800',  // NEW
        default => 'bg-gray-100 text-gray-800'
    };
}
```

## How Excused Absences Work

### Flow:
1. **User submits absence request** via the "Absence Records" section
2. **Admin reviews and approves** the request
3. **System creates attendance records** for each workday in the absence period:
   - Status: `excused`
   - Check-in/out times: `null`
   - Notes: Includes reason and admin comments
   - Approved flags set

4. **Display in attendance history:**
   - Shows `--` for check-in and check-out times
   - Displays "Excused" status with blue badge
   - Shows 0 hrs for total hours
   - Includes the absence reason in notes

### API Response Example

**Before Fix:**
```json
{
    "date": "Nov 11, 2025",
    "check_in": "--",
    "check_out": "Still working",  // WRONG!
    "status": "Excused",
    "total_hours": "0 hrs"
}
```

**After Fix:**
```json
{
    "date": "Nov 11, 2025",
    "check_in": "--",
    "check_out": "--",  // CORRECT!
    "status": "Excused",
    "status_class": "bg-blue-100 text-blue-800",
    "total_hours": "0 hrs",
    "notes": "Approved Leave: Wellness Break | Admin: Approved for wellness day"
}
```

## Testing

### Test Case 1: Approved Absence in History
1. Submit an absence request for a specific date
2. Admin approves the request
3. Check the attendance history for that date
4. **Expected:** Shows `--` for both check-in and check-out, with "Excused" status

### Test Case 2: Current Day Excused
1. Approve an absence request for today's date
2. View "My Attendance History" 
3. **Expected:** Today's entry shows as "Excused" with `--` for times

### Test Case 3: Multiple Excused Days
1. Approve an absence request spanning multiple days
2. View attendance history
3. **Expected:** Each workday shows as separate "Excused" entry

## Files Modified
- `app/Http/Controllers/Api/DashboardController.php`
  - `getAttendanceHistory()` method - Added excused absence handling
  - `getStatusClass()` method - Added 'excused' status styling

## Related Features
- Absence Request Management (`app/Http/Controllers/Api/AbsenceRequestController.php`)
- Attendance Records (`app/Models/Attendance.php`)
- Frontend Display (`resources/views/dashboard.blade.php`)

## Date Fixed
November 12, 2025

## Issue Reporter
User reported: "I excused this 11/11/2025 and yet it shows 'Still Working' lmao. I also found out that it didn't logged on the attendance history the excused"
