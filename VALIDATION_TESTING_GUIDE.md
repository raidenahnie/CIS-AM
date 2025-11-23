# Frontend Validation Testing Guide

## Quick Test Checklist

### 1. Login Form Testing
**Location**: `/login`

| Test Case | Input | Expected Result |
|-----------|-------|-----------------|
| Empty email | (leave blank) | ❌ "Email is required" |
| Invalid email | `notanemail` | ❌ "Invalid email format" |
| SQL injection | `admin' OR '1'='1` | ❌ "Invalid characters detected" |
| XSS attempt | `<script>alert('xss')</script>` | ❌ Input sanitized automatically |
| Valid email | `user@domain.com` | ✅ Accepted |
| Short password | `1234567` | ❌ "Password must be at least 8 characters" |
| Rate limiting | Submit 4 times quickly | ⚠️ "Too many attempts. Please wait X seconds." (blocked on 4th) |

### 2. Password Reset Form Testing
**Location**: `/password/reset/{token}`

| Test Case | Input | Expected Result |
|-----------|-------|-----------------|
| Weak password | `password` | ⚠️ "Weak password" indicator |
| Good password | `Password123` | ⚠️ "Good password" indicator |
| Strong password | `P@ssw0rd123!` | ✅ "Strong password" indicator |
| Mismatched passwords | `Pass123` / `Pass456` | ❌ "Passwords do not match" |
| Too short | `Pass1` | ❌ "Password must be at least 8 characters" |
| No uppercase | `password123` | ❌ "Password must contain uppercase, lowercase, and numbers" |

### 3. Profile Update Testing
**Location**: Dashboard → Settings → Profile

| Test Case | Input | Expected Result |
|-----------|-------|-----------------|
| Empty name | (leave blank) | ❌ "Full Name is required" |
| Too short name | `A` | ❌ "Full Name must be at least 2 characters" |
| Name with SQL | `John'; DROP TABLE--` | ❌ "Invalid characters detected" |
| Invalid phone | `123` | ❌ "Phone number must be at least 10 digits" |
| Wrong phone format | `12345678901` | ❌ "Invalid phone format. Use +639XXXXXXXXX..." |
| Valid phone | `+639171234567` | ✅ Accepted |
| Valid phone alt | `09171234567` | ✅ Accepted |
| Rate limiting | Update 4 times quickly | ⚠️ Rate limit warning (blocked on 4th) |

### 4. Absence Request Testing
**Location**: Dashboard → Absence → Request Leave

| Test Case | Input | Expected Result |
|-----------|-------|-----------------|
| Past start date | Yesterday's date | ❌ "Start date cannot be in the past" |
| End before start | Start: Today, End: Yesterday | ❌ "End date must be after start date" |
| Too long duration | 31+ days | ❌ "Leave request cannot exceed 30 days..." |
| Short reason | `sick` (4 chars) | ❌ "Reason must be at least 10 characters" |
| Too long reason | 501+ characters | ❌ "Reason must not exceed 500 characters" |
| XSS in reason | `<script>...</script>` | ❌ Sanitized automatically |
| Valid request | Proper dates + 10+ char reason | ✅ Accepted |
| Rate limiting | Submit 4 requests quickly | ⚠️ Rate limit warning |

### 5. Admin - Workplace Management Testing
**Location**: Admin Dashboard → Workplaces → Add/Edit Workplace

| Test Case | Input | Expected Result |
|-----------|-------|-----------------|
| Empty name | (leave blank) | ❌ "Workplace name is required" |
| Too short address | `AB` | ❌ "Address must be at least 5 characters" |
| Invalid latitude | `100` | ❌ "Invalid latitude" |
| Invalid longitude | `200` | ❌ "Invalid longitude" |
| Zero radius | `0` | ❌ "Radius must be between 1 and 10000 meters" |
| Huge radius | `15000` | ❌ "Radius must be between 1 and 10000 meters" |
| Valid workplace | All fields correct | ✅ Accepted |

### 6. Admin - User Management Testing
**Location**: Admin Dashboard → Users → Add/Edit User

| Test Case | Input | Expected Result |
|-----------|-------|-----------------|
| Empty name | (leave blank) | ❌ "Name is required" |
| Invalid email | `notanemail` | ❌ "Invalid email format" |
| Email with XSS | `test@test.com<script>` | ❌ "Invalid characters detected" |
| Weak password | `pass123` | ❌ Password validation fails |
| Mismatched passwords | Different values | ❌ "Passwords do not match" |
| Valid user | All fields correct | ✅ Accepted |

### 7. Search Input Testing
**Location**: Any search field in the system

| Test Case | Input | Expected Result |
|-----------|-------|-----------------|
| SQL injection | `' OR 1=1--` | ⚠️ "Invalid characters detected and removed" |
| XSS attempt | `<script>alert(1)</script>` | ⚠️ Characters sanitized |
| Very long search | 101+ characters | ℹ️ Truncated to 100 characters |
| Normal search | `John Doe` | ✅ Works normally |

## Automated Testing with Browser Console

### Test All Validation Functions
Open browser console (F12) and run:

```javascript
// Test email validation
console.log('Email Tests:');
console.log(ValidationUtils.validateEmail('test@example.com')); // Should be valid
console.log(ValidationUtils.validateEmail('invalid-email')); // Should be invalid
console.log(ValidationUtils.validateEmail("admin' OR '1'='1")); // Should detect SQL

// Test phone validation
console.log('\nPhone Tests:');
console.log(ValidationUtils.validatePhone('+639171234567')); // Valid
console.log(ValidationUtils.validatePhone('09171234567')); // Valid
console.log(ValidationUtils.validatePhone('123')); // Invalid

// Test password validation
console.log('\nPassword Tests:');
console.log(ValidationUtils.validatePassword('Pass123')); // Missing special char
console.log(ValidationUtils.validatePassword('P@ssw0rd123')); // Strong

// Test sanitization
console.log('\nSanitization Tests:');
console.log(ValidationUtils.sanitize('<script>alert("xss")</script>')); // Should escape
console.log(ValidationUtils.hasSQLInjection("' OR '1'='1")); // Should detect

// Test rate limiting
console.log('\nRate Limit Test:');
for (let i = 0; i < 5; i++) {
    console.log(`Attempt ${i+1}:`, ValidationUtils.rateLimiter.canSubmit('test-form', 3, 60000));
}
```

## Visual Indicators to Check

### ✅ Success States
- Green border on input
- Green success message
- Success toast notification (green background)

### ❌ Error States
- Red border on input
- Red error message below field
- Error toast notification (red background)

### ⚠️ Warning States
- Yellow/orange toast for rate limits
- Warning messages in yellow

### ℹ️ Info States
- Blue toast for informational messages
- Character counters updating in real-time

## Performance Testing

### Check JavaScript Console
Should see:
```
🛡️ Search sanitization active on X inputs
```

### Network Tab
- Verify `validation-utils.js` loads (should be ~12KB)
- Verify `search-sanitizer.js` loads (should be ~2KB)
- Check no console errors

### Timing
- Validation should be instant (< 10ms)
- No lag when typing
- Smooth animations

## Browser Compatibility Testing

Test on:
- ✅ Chrome/Edge (latest)
- ✅ Firefox (latest)
- ✅ Safari (latest)
- ✅ Mobile Chrome
- ✅ Mobile Safari

## Security Testing

### XSS Payload Tests
Try these in various inputs:
```
<script>alert('XSS')</script>
<img src=x onerror=alert('XSS')>
javascript:alert('XSS')
<iframe src="javascript:alert('XSS')"></iframe>
```
**Expected**: All should be sanitized or rejected

### SQL Injection Tests
Try these in various inputs:
```
' OR '1'='1
'; DROP TABLE users--
' UNION SELECT * FROM users--
admin'--
```
**Expected**: All should be detected and rejected

## Common Issues & Solutions

### Issue: Validation not working
**Solution**: Check if `validation-utils.js` is loaded in browser console

### Issue: Toast not appearing
**Solution**: Check CSS animations are loaded, verify no console errors

### Issue: Rate limiting not working
**Solution**: Clear browser localStorage and cookies

### Issue: Search sanitization not active
**Solution**: Verify `search-sanitizer.js` is loaded after `validation-utils.js`

## Success Criteria

✅ All forms reject invalid input
✅ Clear error messages appear
✅ XSS attempts are blocked
✅ SQL patterns are detected
✅ Rate limiting works
✅ No console errors
✅ Good user experience (fast, clear feedback)
✅ Mobile-friendly validation

## Report Issues

If validation fails or can be bypassed:
1. Note the exact input used
2. Note which form/field
3. Check browser console for errors
4. Screenshot if possible
5. Report to development team

---
**Last Updated**: November 23, 2025
**Status**: Ready for Testing
