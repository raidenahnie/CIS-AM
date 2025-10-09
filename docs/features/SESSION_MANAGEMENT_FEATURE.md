# Authentication and Session Management Updates

## Overview
This document describes the authentication and session management improvements implemented in the CIS-AM system.

## Features Implemented

### 1. Redirect Authenticated Users from Login/Landing Pages
When a user is already logged in, they will be automatically redirected to the dashboard if they try to access:
- The landing page (`/`)
- The login page (`/login`)

**Implementation:**
- Created `RedirectIfAuthenticated` middleware
- Applied the `guest` middleware to landing and login routes
- Registered middleware alias in `bootstrap/app.php`

### 2. Single Device Login Restriction
The system now enforces single device login per account:
- When a user logs in from a new device/browser, their previous session is automatically invalidated
- The user on the old device will be logged out when they try to perform any action
- A clear error message is displayed explaining that the account is logged in on another device

**Implementation:**
- Added `current_session_id` field to the `users` table
- Modified `AuthController::login()` to:
  - Check if user has an active session on another device
  - Invalidate old sessions when logging in from a new device
  - Store the current session ID
- Modified `AuthController::logout()` to clear the session ID
- Created `ValidateSession` middleware to check session validity on each request
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
   - Logs out users whose session has been invalidated

3. **app/Http/Controllers/AuthController.php**
   - Added session invalidation logic on login
   - Added session clearing on logout
   - Added DB facade import

4. **app/Models/User.php**
   - Added `current_session_id` to fillable fields

5. **routes/web.php**
   - Added `guest` middleware to landing and login routes

6. **bootstrap/app.php**
   - Registered `guest` middleware alias
   - Added `ValidateSession` middleware to web middleware group

7. **resources/views/auth/login.blade.php**
   - Added error message display for session expiration

8. **database/migrations/2025_10_07_152428_add_session_tracking_to_users_table.php** (new)
   - Migration to add session tracking field

## How It Works

### Login Flow
1. User submits login credentials
2. System validates credentials
3. System checks if user has an active session (`current_session_id`)
4. If yes, the old session is deleted from the `sessions` table
5. Session is regenerated for security
6. New session ID is stored in the user record
7. User is redirected to dashboard

### Session Validation Flow
1. On every request, `ValidateSession` middleware runs
2. If user is authenticated, check if their stored `current_session_id` matches current session
3. If not matching:
   - User is logged out
   - Session is invalidated
   - User is redirected to login with error message

### Logout Flow
1. User clicks logout
2. System clears the `current_session_id` field
3. User is logged out and redirected to landing page

## Testing

### Test Case 1: Redirect from Login/Landing when Authenticated
1. Log in to the system
2. Try to access `/` or `/login`
3. **Expected:** Automatically redirected to `/dashboard`

### Test Case 2: Single Device Login
1. Log in on Device A (e.g., laptop)
2. Try to log in on Device B (e.g., phone) with the same account
3. **Expected:** Login successful on Device B
4. Try to perform any action on Device A
5. **Expected:** Logged out on Device A with message "Your account is logged in on another device"

### Test Case 3: Same Device, Different Browser
1. Log in on Chrome
2. Open incognito/private tab in same browser
3. Try to log in with same account
4. **Expected:** Login successful in incognito
5. Try to perform action in regular Chrome tab
6. **Expected:** Logged out in regular tab

## Security Considerations

- Session IDs are unique per browser/device
- Old sessions are completely removed from the database
- Session validation happens on every request for authenticated users
- Users receive clear feedback about session status

## Notes

- The IDE may show a false positive error for `$user->save()` in AuthController - this is normal and the code will work correctly
- Private browsing/incognito is treated as a separate device/session
- The same device can only have one active session per account

## Bug Fixes

### Fixed: "Session Expired" Message Persisting After Logout
**Issue:** After being logged out due to login on another device, the "Session Expired" error message would continue to show even when trying to log in again.

**Root Cause:** The session regeneration was happening AFTER saving the session ID, causing a mismatch between the stored session ID and the actual session ID. This also caused issues with flash message handling.

**Solution:** 
1. Moved `$request->session()->regenerate()` to execute BEFORE saving the new session ID
2. Updated `ValidateSession` middleware to properly handle session invalidation with `session()->regenerate()` instead of just `regenerateToken()`
3. Added early return in `ValidateSession` for non-authenticated requests to prevent unnecessary processing

**Files Modified:**
- `app/Http/Controllers/AuthController.php` - Fixed session ID saving order
- `app/Http/Middleware/ValidateSession.php` - Improved session handling
