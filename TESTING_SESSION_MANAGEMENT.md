# Session Management Testing Guide

## How Single Device Login Works

### Expected Behavior
When you log in from a new device, the OLD device's session is invalidated in the database. The old device will CONTINUE to work UNTIL they try to do something (make a request). When they make any request to the server, the `ValidateSession` middleware will check if their session ID matches the stored one, and if not, they'll be logged out.

## Step-by-Step Test

### Test 1: Phone Regular Browser → Phone Incognito
1. **Login on Phone (Regular Browser)**
   - Go to login page
   - Enter credentials and login
   - ✅ Should see dashboard
   - Note: User's `current_session_id` = Phone Regular Session ID

2. **Login on Phone (Incognito/Private Tab)**
   - Open incognito/private tab
   - Go to login page
   - Enter credentials and login
   - ✅ Should see dashboard
   - Note: User's `current_session_id` = Phone Incognito Session ID
   - Note: Phone Regular Session deleted from database

3. **Go Back to Phone (Regular Browser)**
   - Switch back to regular browser tab
   - ❌ Don't just look at it - the page is still loaded
   - ✅ **Do something**: Click a link, refresh page, or do any action
   - ✅ Should be logged out with message "Your account is logged in on another device"

### Test 2: Phone → PC
1. **Login on Phone**
   - Login successfully
   - User's `current_session_id` = Phone Session ID

2. **Login on PC**
   - Login successfully
   - User's `current_session_id` = PC Session ID
   - Phone Session deleted from database

3. **Go Back to Phone**
   - The dashboard might still be visible (cached)
   - **Take an action**: Click something, refresh, navigate
   - ✅ Should be logged out with error message

### Test 3: Multiple Devices Sequence
1. Login on Device A → Success (A's session stored)
2. Login on Device B → Success (B's session stored, A's session deleted)
3. **Use Device A** → Should be logged out
4. Login on Device A again → Success (A's new session stored, B's session deleted)
5. **Use Device B** → Should be logged out

## Checking Logs

After testing, check the Laravel log file at `storage/logs/laravel.log`:

### Login Logs
```
User user@example.com logged in with session abc123xyz
```

### Session Invalidation Logs
```
Invalidating old session for user user@example.com
  old_session_id: abc123xyz
  new_session_id: def456uvw
```

### Middleware Logs (when old device is accessed)
```
Session mismatch for user user@example.com
  stored_session_id: def456uvw
  current_session_id: abc123xyz
```

## Common Misunderstandings

❌ **WRONG**: "When I login on Device B, Device A should immediately show logout"
- The old device page is already loaded in the browser
- The server can't "push" the logout to the browser
- Device A will only know when it makes a request

✅ **CORRECT**: "When I login on Device B, and then DO SOMETHING on Device A, it logs me out"
- This is how it works!
- The middleware checks on every request
- When Device A makes any request, it gets logged out

## Important Notes

1. **Just looking at the page doesn't trigger a check** - you must interact with it
2. **The dashboard might still be visible** - it's cached in your browser
3. **You must refresh or click something** - this sends a request to the server
4. **API calls also trigger the check** - if the dashboard loads data via API, that will trigger it
5. **Each browser/tab is treated as separate** - incognito = different device

## Debugging Steps

If it's not working:

1. Check the log file: `storage/logs/laravel.log`
2. Verify the user's `current_session_id` in the database after each login
3. Check the `sessions` table - old sessions should be deleted
4. Make sure you're actually making a request (refresh/click/navigate) on the old device
5. Clear browser cache if needed
