# Authentication and Session Management - Strict Single Device Login

## Overview
This document describes the strict single device login system implemented in the CIS-AM application.

## Features Implemented

### 1. Redirect Authenticated Users from Login/Landing Pages
When a user is already logged in, they will be automatically redirected to the dashboard if they try to access:
- The landing page (`/`)
- The login page (`/login`)

**Implementation:**
- Created `RedirectIfAuthenticated` middleware
- Applied the `guest` middleware to landing and login routes
- Registered middleware alias in `bootstrap/app.php`

### 2. Strict Single Device Login (Login Blocking)
The system enforces **strict single device login** per account:
- **Only ONE active session per account is allowed at any time**
- If a user is already logged in on Device A, attempting to log in on Device B will be **BLOCKED**
- The login will fail with error: *"This account is already logged in on another device. Please log out from that device first."*
- To log in from a new device, the user **MUST explicitly log out** from the current device first

**This means:**
- ✅ User logs in on Laptop → Success
- ❌ User tries to log in on Phone → **BLOCKED** (login fails)
- ✅ User logs out on Laptop
- ✅ User logs in on Phone → Success

**Implementation:**
- Added `current_session_id` field to the `users` table to track active session
- Modified `AuthController::login()` to:
  - Check if user has an active session before allowing login
  - Verify the session still exists in the sessions table
  - Block login if an active session is found
  - Store the session ID only after successful login
- Modified `AuthController::logout()` to clear the session ID
- Created `ValidateSession` middleware to verify session validity
- Added error message display in the login view

## Database Changes

### Migration: `add_session_tracking_to_users_table`
```php
Schema::table('users', function (Blueprint $table) {
    $table->string('current_session_id')->nullable()->after('remember_token');
});
```

## Files Modified

1. **app/Http/Middleware/RedirectIfAuthenticated.php** (new)
   - Redirects authenticated users to dashboard

2. **app/Http/Middleware/ValidateSession.php** (new)
   - Validates that user's current session matches stored session ID
   - Handles edge cases where session was manually deleted

3. **app/Http/Controllers/AuthController.php**
   - Added session existence check before allowing login
   - Added login blocking logic with error message
   - Added session clearing on logout
   - Added logging for debugging
   - Added DB and Log facade imports

4. **app/Models/User.php**
   - Added `current_session_id` to fillable fields

5. **routes/web.php**
   - Added `guest` middleware to landing and login routes

6. **bootstrap/app.php**
   - Registered `guest` middleware alias
   - Added `ValidateSession` middleware to web middleware group

7. **resources/views/auth/login.blade.php**
   - Added error message display for blocked login attempts

8. **database/migrations/2025_10_07_152428_add_session_tracking_to_users_table.php** (new)
   - Migration to add session tracking field

## How It Works

### Login Flow
1. User submits login credentials
2. System validates credentials
3. **System checks if user already has an active session:**
   - Check if `current_session_id` exists
   - Query the sessions table for that session ID
   - **Check if session is still active** (last_activity within session lifetime)
   - If active session exists → **LOGIN BLOCKED** with error message
   - If session expired or doesn't exist → Clear the stored session ID and continue to step 4
4. Session is regenerated for security
5. New session ID is stored in the user record
6. User is redirected to dashboard

### Logout Flow
1. User clicks logout
2. System clears the `current_session_id` field (sets to null)
3. User session is invalidated
4. User is redirected to landing page
5. **User can now log in from any device**

### Session Validation Flow
1. On every request, `ValidateSession` middleware runs
2. If user is authenticated, check if their current session ID matches stored `current_session_id`
3. If not matching (edge case - shouldn't normally happen):
   - Clear the stored session ID
   - Force logout
   - Redirect to login with error message

## Testing

### Test Case 1: Basic Login Blocking
1. Log in on Device A (e.g., laptop)
2. **Expected:** Login successful, dashboard loads
3. Try to log in on Device B (e.g., phone) with same account
4. **Expected:** Login fails with error "This account is already logged in on another device. Please log out from that device first."

### Test Case 2: Login After Logout
1. Log in on Device A
2. **Expected:** Success
3. Log out on Device A
4. **Expected:** Redirected to landing page
5. Try to log in on Device B
6. **Expected:** Login successful

### Test Case 3: Incognito/Private Browsing
1. Log in on regular browser tab
2. **Expected:** Success
3. Open incognito/private tab
4. Try to log in with same account
5. **Expected:** Login blocked with error message
6. Log out from regular tab
7. Try to log in from incognito tab
8. **Expected:** Login successful

### Test Case 4: Multiple Devices Sequence
1. Login on Device A → **Success**
2. Try login on Device B → **BLOCKED**
3. Logout on Device A → **Success**
4. Login on Device B → **Success**
5. Try login on Device A → **BLOCKED**

## Security Considerations

- Each account can only have ONE active session at any time
- Sessions are tracked using unique session IDs stored in the database
- **Expired sessions are automatically detected and cleared** during login attempts
- Session expiration is based on the `last_activity` timestamp in the sessions table
- Default session lifetime: 2 hours (configurable in `config/session.php`)
- Users must explicitly log out to switch devices immediately
- After session expiration, users can log in from any device
- Session validation happens on every request for authenticated users
- Users receive clear feedback about login status

## Logging

All session-related activities are logged to `storage/logs/laravel.log`:

### Successful Login
```
[INFO] User user@example.com logged in with session abc123xyz
```

### Blocked Login Attempt
```
[WARNING] Login blocked for user@example.com - already logged in on another device
  existing_session_id: abc123xyz
```

### Expired Session Cleared
```
[INFO] Clearing expired/invalid session for user@example.com
  old_session_id: abc123xyz
```

### Session Validation Issue
```
[WARNING] Session mismatch for user user@example.com - forcing logout
  stored_session_id: abc123xyz
  current_session_id: def456uvw
```

## Common Questions

**Q: What happens if I close my browser without logging out?**
A: The session will remain in the database but will expire after the configured session lifetime (default: 2 hours). After that time, you can log in again. The system automatically detects expired sessions and clears them, allowing you to log in.

**Q: What happens if my session expires or is manually deleted from the database?**
A: The `ValidateSession` middleware will detect the mismatch, clear the stored session ID, and log you out. You can then log in again. During login, expired sessions are automatically detected and cleared.

**Q: Can I force logout someone else's session?**
A: As an admin, you could manually clear the `current_session_id` field in the database for a user, allowing them to log in from a new device.

**Q: What if my browser crashes or I close it without logging out?**
A: The session remains active in the database for the configured session lifetime (default: 2 hours). After this period expires, the system will automatically allow you to log in again. If you need immediate access, an admin can clear your session.

**Q: Why not just automatically invalidate the old session and allow the new login?**
A: This is a security feature to prevent unauthorized access. If someone tries to log in to your account from another device, it will be blocked, alerting you to potential unauthorized access. However, expired sessions are automatically cleaned up.

## Notes

- The IDE may show a false positive error for `$user->save()` in AuthController - this is normal
- Private browsing/incognito is treated as a separate device/session
- Each account can only have ONE active session across ALL devices
- Users must explicitly log out to switch devices
