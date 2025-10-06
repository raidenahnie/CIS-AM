# System Settings - Testing Guide

## Quick Visual Test Checklist

### 1. Access System Settings
- [ ] Log in as admin
- [ ] Navigate to "Settings" section in admin dashboard
- [ ] Verify the settings page loads correctly

### 2. Visual Verification

#### Security Settings Card
- [ ] Card displays with shield icon (indigo color)
- [ ] Has 3 toggles: Password expiry, 2FA, Session timeout
- [ ] All toggles are enabled (not disabled)
- [ ] Toggling switches shows notifications

#### Location Settings Card
- [ ] Card displays with map marker icon (green color)
- [ ] Has 2 toggles and 1 number input
- [ ] Default radius input accepts values
- [ ] Changing radius shows notification after 1 second

#### System Account Settings Card (DANGER ZONE)
- [ ] Card displays with user-shield icon (RED color)
- [ ] Has "DANGER ZONE" badge in red
- [ ] Shows warning message about security requirements
- [ ] "Modify Admin Account" button is red and prominent

#### Activity Logs Card
- [ ] Card displays with history icon (purple color)
- [ ] Describes activity tracking functionality
- [ ] "View Activity Logs" button is purple

#### Data Management Card
- [ ] Card displays with database icon (blue color)
- [ ] Has "Create Backup" and "Export Data" buttons
- [ ] Shows last backup information

### 3. Admin Account Modification Test

#### Opening the Modal
- [ ] Click "Modify Admin Account" button
- [ ] Modal opens with glassmorphism effect
- [ ] Modal has red-themed danger zone header
- [ ] Security requirements are clearly displayed

#### Security Testing
- [ ] Enter incorrect password → Should fail with error message
- [ ] Enter incorrect security phrase → Should fail with specific error
- [ ] Leave required fields empty → Should show validation errors

#### Successful Update Test
1. [ ] Enter current admin password
2. [ ] Type exactly: `CONFIRM UPDATE ADMIN`
3. [ ] Change name to "Test Admin"
4. [ ] Click "Update Admin Account"
5. [ ] Success notification appears
6. [ ] Page reloads with new name

#### Password Change Test
1. [ ] Open modal again
2. [ ] Enter current password
3. [ ] Type security phrase: `CONFIRM UPDATE ADMIN`
4. [ ] Enter new password and confirmation
5. [ ] Submit form
6. [ ] Should redirect to login page
7. [ ] Login with new password

### 4. Activity Logs Test

#### Opening Activity Logs
- [ ] Click "View Activity Logs" button
- [ ] Large modal opens (full width)
- [ ] Shows table with headers: Time, Admin, Action, Description, IP Address
- [ ] Loading indicator appears briefly

#### Log Entries Verification
- [ ] Previous admin actions appear in the table
- [ ] Each action has color-coded badge
- [ ] Timestamps are formatted correctly
- [ ] IP addresses are displayed
- [ ] Admin names are shown correctly

#### Pagination Test (if 50+ logs exist)
- [ ] Pagination controls appear at bottom
- [ ] Page numbers are clickable
- [ ] "Previous" and "Next" buttons work
- [ ] Current page is highlighted

#### Creating New Logs
1. [ ] Close activity logs modal
2. [ ] Create a new user
3. [ ] Open activity logs again
4. [ ] "Create User" action should appear at top
5. [ ] Verify details are correct

### 5. Settings Functionality Test

#### Toggle Switches
- [ ] Click "Require password change every 90 days" toggle
- [ ] Notification appears: "Setting enabled/disabled"
- [ ] Repeat for other toggles
- [ ] All toggles respond immediately

#### Default Radius Input
- [ ] Change value from 100 to 200
- [ ] Wait 1 second
- [ ] Notification appears: "Default radius updated to 200 meters"

### 6. Security Verification

#### Logged Actions Check
After each action, verify it appears in Activity Logs:
- [ ] User creation → "create_user"
- [ ] User update → "update_user"
- [ ] User deletion → "delete_user"
- [ ] Admin account update → "update_admin_account"
- [ ] Failed admin update → "failed_admin_update"

#### Double Security Test
1. [ ] Try to update admin account
2. [ ] Enter wrong password
3. [ ] Check activity logs
4. [ ] Failed attempt should be logged with reason

### 7. Responsive Design Test
- [ ] Test on desktop (1920x1080)
- [ ] Test on tablet (768px width)
- [ ] Test on mobile (375px width)
- [ ] All modals should be responsive
- [ ] Tables should scroll horizontally if needed

### 8. Edge Cases

#### Admin Account Modal
- [ ] Try submitting with empty security phrase
- [ ] Try submitting with partial phrase
- [ ] Try submitting with lowercase phrase
- [ ] Try changing email to existing user email
- [ ] Try very short password

#### Activity Logs
- [ ] Test with no logs in database
- [ ] Test with exactly 50 logs
- [ ] Test with 100+ logs
- [ ] Close modal and reopen (should refresh)

## Expected Behavior Summary

### Admin Account Modification
✅ **Security Phrase**: Must be exactly "CONFIRM UPDATE ADMIN" (case-sensitive)
✅ **Password Required**: Current password must be verified
✅ **Changes Logged**: All attempts (success/fail) are recorded
✅ **Auto Logout**: Email/password changes trigger logout

### Activity Logs
✅ **Real-time**: Shows all admin actions immediately
✅ **Detailed**: Includes who, what, when, where information
✅ **Paginated**: 50 entries per page
✅ **Color-coded**: Different colors for different action types

### Settings
✅ **Interactive**: All toggles and inputs are functional
✅ **Notifications**: Visual feedback for all changes
✅ **Debounced**: Input changes wait 1 second before notifying

## Common Issues & Solutions

### Issue: Modal doesn't open
**Solution**: Check browser console for JavaScript errors

### Issue: Security phrase not working
**Solution**: Verify exact phrase: `CONFIRM UPDATE ADMIN` (all caps, with spaces)

### Issue: Activity logs empty
**Solution**: Perform an admin action first (create/update/delete user)

### Issue: Settings don't persist
**Solution**: Backend implementation needed (currently demonstration only)

### Issue: Pagination not appearing
**Solution**: Need at least 51 log entries for pagination to show

## Screenshots to Take

1. System Settings overview with all cards
2. Admin Account Modal (empty state)
3. Admin Account Modal (with security warning)
4. Activity Logs Modal (populated)
5. Activity Logs Modal (pagination)
6. Notification examples
7. Mobile responsive view

## Performance Expectations

- Modal open time: < 100ms
- Activity logs load time: < 500ms
- Settings toggle response: Immediate
- Page reload after admin update: < 2s

## Browser Compatibility

Test in:
- [ ] Chrome/Edge (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Mobile browsers (iOS Safari, Chrome)

---

## Quick Test Script

```javascript
// Run in browser console to verify functions exist
console.log(typeof openAdminAccountModal); // should be "function"
console.log(typeof closeAdminAccountModal); // should be "function"
console.log(typeof openActivityLogsModal); // should be "function"
console.log(typeof loadActivityLogs); // should be "function"
```

## API Test Using cURL

### Get Activity Logs
```bash
curl -X GET "http://localhost/admin/activity-logs" \
  -H "Cookie: your-session-cookie" \
  -H "Accept: application/json"
```

### Update Admin Account (should fail without proper auth)
```bash
curl -X POST "http://localhost/admin/update-admin-account" \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: your-csrf-token" \
  -d '{
    "current_password": "password",
    "security_phrase": "CONFIRM UPDATE ADMIN",
    "name": "New Admin Name"
  }'
```

---

## Sign-off Checklist

Before marking as complete:
- [ ] All visual tests pass
- [ ] Security tests pass
- [ ] Activity logs are recording properly
- [ ] No console errors
- [ ] Mobile responsive works
- [ ] Documentation is complete
- [ ] Screenshots taken
- [ ] Code reviewed

**Tested by**: _______________
**Date**: _______________
**Status**: _______________
