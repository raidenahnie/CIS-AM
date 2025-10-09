# Fix for Late Time Decimal Display Issue

## Problem
The late time was showing as decimal values like "-130.616666 min" instead of proper hour/minute format like "2 hrs 10 min".

## Root Cause
The `diffInMinutes()` method in Laravel returns a **float value** by default, which includes fractional minutes. Additionally, the difference could be negative depending on the order of comparison.

## Solution
Applied two fixes to ensure proper integer values:

1. **Cast to integer**: `(int)` to remove decimal places
2. **Use absolute value**: `abs()` to ensure positive numbers

### Code Changes

#### Before:
```php
$lateMinutes = $checkIn->timestamp->diffInMinutes($lateTimeThreshold);
$breakMinutes = $breakStart->timestamp->diffInMinutes($breakEnd->timestamp);
```

#### After:
```php
$lateMinutes = abs((int) $checkIn->timestamp->diffInMinutes($lateTimeThreshold));
$breakMinutes = abs((int) $breakStart->timestamp->diffInMinutes($breakEnd->timestamp));
```

## Results

### Late Time Display:
- **Before**: "Late -130.616666 min"
- **After**: "Late 2 hrs 10 min"

### Break Duration Display:
- **Before**: "Duration: 0 min" (when decimals were lost)
- **After**: "Duration: 215 min" (properly calculated as integer)

## Examples of Correct Display:

| Minutes Late | Display |
|--------------|---------|
| 15 | Late 15 min |
| 45 | Late 45 min |
| 60 | Late 1 hr |
| 75 | Late 1 hr 15 min |
| 130 | Late 2 hrs 10 min |
| 146 | Late 2 hrs 26 min |
| 215 | Late 3 hrs 35 min |

## Files Modified
- `app/Http/Controllers/AdminController.php` - `getAttendanceStats()` method

## Testing
After refreshing the attendance page, all late times should now display in proper hour/minute format without decimals or negative signs.
