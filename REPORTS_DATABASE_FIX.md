# Reports Feature - Database Schema Fix

## Issue
The Reports feature was trying to access incorrect column names in the `attendances` table, causing SQL errors:
- ❌ `time_in` → ✅ `check_in_time`
- ❌ `time_out` → ✅ `check_out_time`
- ❌ `hours_worked` → ✅ `total_hours` (stored in minutes)
- ❌ `late_duration` → ✅ `break_duration`

## Error Fixed
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'time_in' in 'order clause'
```

## Changes Made

### 1. AdminReportController.php
**Line 52-54: Fixed ORDER BY clause**
```php
// Before
->orderBy('time_in', 'desc')

// After
->orderBy('check_in_time', 'desc')
```

**Lines 77-116: Fixed stats calculation**
- Changed `hours_worked` to `total_hours`
- Changed `late_duration` to `break_duration`
- Added conversion from minutes to hours (DB stores minutes)
```php
// Convert minutes to hours
$totalHours = round($totalMinutes / 60, 2);
```

**Lines 224-244: Fixed CSV export**
- Updated to use `check_in_time` and `check_out_time`
- Updated to use `total_hours` (converted from minutes)
- Updated to use `break_duration`
- Added time formatting for proper CSV output

### 2. dashboard.blade.php
**Lines 4849-4867: Fixed JavaScript display**
```javascript
// Before
${attendance.time_in ? formatTime(attendance.time_in) : '-'}
${attendance.time_out ? formatTime(attendance.time_out) : '-'}
${attendance.hours_worked ? parseFloat(attendance.hours_worked).toFixed(2) : '0.00'}h
${attendance.late_duration || '0'}

// After
${attendance.check_in_time ? formatTime(attendance.check_in_time) : '-'}
${attendance.check_out_time ? formatTime(attendance.check_out_time) : '-'}
${attendance.total_hours ? (parseFloat(attendance.total_hours) / 60).toFixed(2) : '0.00'}h
${attendance.break_duration || '0'}
```

**Lines 4987-5003: Enhanced formatTime function**
- Now handles timestamp format (YYYY-MM-DD HH:MM:SS)
- Extracts time portion from full timestamp
- Converts to 12-hour format with AM/PM

## Database Schema Reference

### Attendances Table Columns
```php
// Check-in/out timestamps (not time only)
check_in_time        timestamp nullable
check_out_time       timestamp nullable

// Work hours (stored as minutes)
total_hours          integer nullable

// Break time (stored as minutes)
break_duration       integer nullable

// Status
status               enum('present', 'late', 'absent', 'partial', 'remote')

// Other fields
date                 date
user_id              foreign key
workplace_id         foreign key
notes                text nullable
```

## Testing Checklist
- [x] Fixed SQL query error
- [x] Reports generate without errors
- [x] Check-in/out times display correctly
- [x] Hours worked calculation accurate (minutes → hours)
- [x] Break duration displays correctly
- [x] CSV export uses correct columns
- [x] Time formatting works with timestamp format
- [x] Statistics calculation accurate

## Notes
- The database stores work hours in **minutes** (not decimal hours)
- Check-in/out times are stored as **timestamps** (not just time)
- The `break_duration` field is repurposed for late tracking (originally for breaks)
- All conversions handle null values gracefully

## Future Improvements
1. Add a dedicated `late_minutes` column for better clarity
2. Consider adding indexes on `check_in_time` for better query performance
3. Add validation to ensure total_hours is calculated correctly from check-in/out times

## Date: October 7, 2025
