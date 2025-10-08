# Manual Entry Access Code Feature

## Overview
This feature adds security to the manual GPS location entry functionality by requiring an admin-configured access code. Only users who know the code can access manual location entry.

## Features Implemented

### 1. **User-Side Security (dashboard.blade.php)**
- **Verification Modal**: When users click "Manual Entry", they must first enter the access code
- **Dynamic Code Fetching**: The code is fetched from the database via API, not hardcoded
- **Error Handling**: Shows error message with shake animation for incorrect codes
- **Fallback**: If API fails, uses default code "DEPED2025"

### 2. **Admin Dashboard Management**
- **System Settings Card**: New card in System Settings section for managing the access code
- **Current Code Display**: Shows the current access code (visible to admins)
- **Update Modal**: Professional modal with security features similar to admin account settings

### 3. **Security Features**
- ✅ Admin password verification required to update the code
- ✅ Code confirmation (must enter twice)
- ✅ Activity logging for all code changes
- ✅ 4-20 character validation
- ✅ Database storage in `system_settings` table

### 4. **Backend Implementation**
- **New Controller Method**: `updateManualEntryCode()` in AdminController
- **New Route**: `/admin/update-manual-entry-code` (POST)
- **Public API**: `/api/manual-entry-code` (GET) for user dashboard
- **Database Seeder**: Sets default code to "DEPED2025"

## How It Works

### For Users:
1. When location access fails, user can click "Manual Entry"
2. A verification modal appears requesting the access code
3. User enters the code provided by their administrator
4. If correct → Location entry form appears
5. If incorrect → Error message with shake animation

### For Administrators:
1. Navigate to Admin Dashboard → System Settings
2. Find "Manual Entry Access Code" card
3. Click "Update Access Code"
4. Enter your admin password (for verification)
5. Enter new code (4-20 characters)
6. Confirm the code
7. Submit → Code is updated and logged

## Default Configuration

**Default Access Code**: `DEPED2025`

This code is set automatically via the database seeder when the system is first initialized.

## API Endpoints

### Get Current Manual Entry Code (Public)
```
GET /api/manual-entry-code
```

**Response:**
```json
{
    "code": "DEPED2025"
}
```

### Update Manual Entry Code (Admin Only)
```
POST /admin/update-manual-entry-code
```

**Request Body:**
```json
{
    "admin_password": "your_admin_password",
    "key": "manual_entry_code",
    "value": "NEW_CODE_HERE"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Manual entry code updated successfully"
}
```

## Files Modified

1. **resources/views/dashboard.blade.php**
   - Added verification modal
   - Updated `verifyAdminCode()` function to fetch code from API
   - Added shake animation CSS

2. **resources/views/admin/dashboard.blade.php**
   - Added Manual Entry Access Code card in System Settings
   - Added update modal
   - Added JavaScript functions for modal management

3. **app/Http/Controllers/AdminController.php**
   - Added `updateManualEntryCode()` method

4. **routes/web.php**
   - Added `/admin/update-manual-entry-code` route
   - Added `/api/manual-entry-code` route

5. **database/seeders/ManualEntryCodeSeeder.php**
   - Created seeder for default code

## Security Considerations

✅ **Admin Password Required**: Only admins with valid password can update the code  
✅ **Activity Logging**: All code changes are logged in admin_activity_logs  
✅ **Code Validation**: Enforces 4-20 character length  
✅ **Confirmation Required**: Must enter code twice to prevent typos  
✅ **Database Storage**: Code stored in system_settings table, not hardcoded  

## Best Practices for Administrators

1. **Choose a Memorable Code**: Use something easy to share verbally but hard to guess
2. **Avoid Common Words**: Don't use "PASSWORD", "12345", etc.
3. **Share Securely**: Only share the code with authorized personnel
4. **Update Regularly**: Consider changing the code periodically for security
5. **Document Changes**: Keep track of when and why you change the code

## Usage Example

### Scenario: New Administrator Wants to Set a Custom Code

1. Admin logs into the system
2. Goes to Admin Dashboard → System Settings
3. Clicks "Update Access Code" on the Manual Entry Access Code card
4. Sees current code: `DEPED2025`
5. Enters admin password: `********`
6. Enters new code: `CAVITE2025`
7. Confirms new code: `CAVITE2025`
8. Clicks "Update Access Code"
9. Success! New code is now `CAVITE2025`
10. Admin shares this code with authorized staff

## Troubleshooting

### Issue: "Invalid admin password"
**Solution**: Make sure you're entering your current admin account password correctly.

### Issue: "Access codes do not match"
**Solution**: Ensure both code fields have identical values. Check for extra spaces.

### Issue: Code not working for users
**Solution**: 
1. Verify the code in Admin Dashboard System Settings
2. Check if user is entering the code correctly (case-sensitive)
3. Try refreshing the user's page

### Issue: Cannot see current code
**Solution**: 
1. Refresh the admin dashboard page
2. Check browser console for API errors
3. Verify database has the setting: `SELECT * FROM system_settings WHERE key='manual_entry_code'`

## Future Enhancements (Optional)

- [ ] Code expiration dates
- [ ] Multiple codes for different user groups
- [ ] SMS/Email notification when code is changed
- [ ] Two-factor authentication for code updates
- [ ] Code usage statistics and auditing

---

**Last Updated**: October 8, 2025  
**Feature Version**: 1.0  
**Status**: ✅ Production Ready
