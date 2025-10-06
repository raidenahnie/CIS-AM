# Attendance Monitoring Feature

## Overview
Added comprehensive attendance monitoring functionality to the admin dashboard that tracks check-in, check-out, and break times for all employees with late arrival detection.

## Features Implemented

### 1. Real-Time Statistics Dashboard
- **Today's Check-ins**: Shows total number of employees who checked in today
- **Average Hours**: Calculates average work hours per employee for the day
- **Late Arrivals**: Tracks employees who checked in after 9:00 AM threshold
- **On Break**: Shows count of employees currently on break

### 2. Detailed Attendance Table
Displays comprehensive attendance information for all employees:
- Employee name and email
- Check-in time (with late badge if after 9:00 AM)
- Check-out time
- Break time (start, end, and duration)
- Total work hours calculated
- Current status (Working, On Break, Completed, No activity)
- Workplace location

### 3. Late Arrival Tracking
- **Threshold**: 9:00 AM
- Late employees are highlighted with a red badge showing "Late {X} min"
- Late count is prominently displayed in the statistics cards
- Check-in times for late employees are shown in red

### 4. Break Time Monitoring
- Tracks break start and end times
- Calculates total break duration
- Shows current break status ("On break") for employees currently taking breaks
- Break time is automatically subtracted from total work hours

### 5. Work Hours Calculation
Accurately calculates work hours by:
- Taking the difference between check-in and check-out times
- Subtracting break duration
- Showing real-time hours for employees still working
- Handling edge cases (on break, no check-out yet)

## Technical Implementation

### Backend (AdminController.php)
Added `getAttendanceStats()` method that:
- Fetches all attendance logs for today
- Groups logs by user
- Identifies check-in, check-out, break start, and break end events
- Calculates work hours and break duration
- Determines if employee is late (after 9:00 AM)
- Returns statistics and detailed attendance data

### Frontend (admin/dashboard.blade.php)

#### View Changes:
- Added 4 statistic cards (check-ins, avg hours, late arrivals, on break)
- Created detailed attendance table with 7 columns
- Added search functionality
- Added refresh button with loading state
- Implemented responsive design

#### JavaScript Functions:
- `loadAttendanceData()`: Fetches data from API and updates UI
- `populateAttendanceTable()`: Renders attendance data in table
- `setupAttendanceSearch()`: Enables real-time search filtering
- `refreshAttendanceData()`: Manually refreshes attendance data
- Auto-loads data when switching to attendance section

### Routes (web.php)
Added new route:
```php
Route::get('/attendance-stats', [AdminController::class, 'getAttendanceStats']);
```

## Usage

### For Administrators:
1. Navigate to Admin Dashboard
2. Click on "Attendance" in the sidebar
3. View real-time attendance statistics and details
4. Use search bar to filter employees
5. Click "Refresh" button to update data

### Data Display:
- **Green badge**: Employees working normally
- **Yellow badge**: Employees on break
- **Blue badge**: Employees who completed their shift
- **Red badge**: Late arrivals (after 9:00 AM)
- **Gray**: No activity for the day

## Key Benefits
1. **Real-time monitoring**: Instant view of who's working, on break, or late
2. **Accountability**: Late arrivals are clearly tracked and highlighted
3. **Break management**: Full visibility into break times and duration
4. **Work hours tracking**: Accurate calculation including break deductions
5. **Easy search**: Quick filtering to find specific employees
6. **Responsive design**: Works on all devices

## API Response Format
```json
{
  "success": true,
  "stats": {
    "total_checkins": 15,
    "late_arrivals": 3,
    "average_hours": 6.5
  },
  "attendance": [
    {
      "user_id": 1,
      "user_name": "John Doe",
      "user_email": "john@example.com",
      "check_in": "8:45 AM",
      "check_out": "5:30 PM",
      "break_start": "12:00 PM",
      "break_end": "1:00 PM",
      "break_duration": "60 min",
      "work_hours": "7.75 hrs",
      "is_late": false,
      "status": "Completed",
      "workplace": "Main Office",
      "location": "123 Main St"
    }
  ]
}
```

## Future Enhancements (Potential)
- Export attendance data to CSV/PDF
- Historical attendance reports
- Weekly/Monthly attendance summaries
- Configurable late time threshold
- Email alerts for late arrivals
- Attendance trend analytics
- Department-wise filtering
- Custom date range selection

## Notes
- Late time threshold is currently hardcoded to 9:00 AM
- Work hours calculation excludes break time
- Times are displayed in 12-hour format (AM/PM)
- Auto-refreshes when switching to attendance section
- Manual refresh available via button
