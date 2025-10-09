# Late Time Format Update

## Change Summary
Updated the late time display to show hours when the delay reaches 60 minutes or more, otherwise show minutes.

## Changes Made

### Backend (AdminController.php)
Modified the `getAttendanceStats()` method to format the `late_by` field intelligently:

**Logic:**
- If late by **less than 60 minutes**: Display as `X min` (e.g., "45 min")
- If late by **60 minutes or more**: Display as `X hr` or `X hrs` (e.g., "1 hr", "2 hrs")
- If late by **hours + minutes**: Display as `X hr Y min` (e.g., "1 hr 15 min", "2 hrs 30 min")

**Examples:**
- 15 minutes late → `15 min`
- 45 minutes late → `45 min`
- 60 minutes late → `1 hr`
- 75 minutes late → `1 hr 15 min`
- 90 minutes late → `1 hr 30 min`
- 120 minutes late → `2 hrs`
- 130 minutes late → `2 hrs 10 min`
- 146 minutes late → `2 hrs 26 min`

### Implementation Code
```php
// Format late_by text
$lateByText = null;
if ($isLate && $checkIn) {
    $lateMinutes = $checkIn->timestamp->diffInMinutes($lateTimeThreshold);
    if ($lateMinutes >= 60) {
        $lateHours = floor($lateMinutes / 60);
        $remainingMinutes = $lateMinutes % 60;
        if ($remainingMinutes > 0) {
            $lateByText = $lateHours . ' hr ' . $remainingMinutes . ' min';
        } else {
            $lateByText = $lateHours . ($lateHours == 1 ? ' hr' : ' hrs');
        }
    } else {
        $lateByText = $lateMinutes . ' min';
    }
}
```

### Frontend (admin/dashboard.blade.php)
No changes needed - the frontend already displays the formatted `late_by` value from the backend:
```javascript
Late ${emp.late_by}
```

## Display Examples

### Badge Display (in table):
- **15 min late**: Red badge shows "Late 15 min"
- **1 hour late**: Red badge shows "Late 1 hr"
- **1 hour 15 min late**: Red badge shows "Late 1 hr 15 min"
- **2 hours late**: Red badge shows "Late 2 hrs"
- **2 hours 26 min late**: Red badge shows "Late 2 hrs 26 min"

## Benefits
1. **More readable**: Shows time in appropriate units
2. **Professional**: Uses proper time formatting
3. **Scalable**: Handles any late duration from minutes to multiple hours
4. **Grammatically correct**: Uses singular "hr" vs plural "hrs" appropriately
5. **Consistent**: Matches standard time display conventions

## Testing
To test the different formats, you can simulate check-ins at different times:
- Check in at 9:15 AM → "Late 15 min"
- Check in at 9:45 AM → "Late 45 min"
- Check in at 10:00 AM → "Late 1 hr"
- Check in at 10:30 AM → "Late 1 hr 30 min"
- Check in at 11:00 AM → "Late 2 hrs"
- Check in at 11:26 AM → "Late 2 hrs 26 min"
