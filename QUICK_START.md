# 🚀 QUICK START - Auto Check-Out System

## For Hostinger Setup (5 Minutes)

### Step 1: Setup Cron Job
1. Login to **Hostinger Control Panel**
2. Go to **Advanced → Cron Jobs**
3. Click **"Create Cron Job"**
4. Fill in:
   - **Type:** `PHP`
   - **Script Path:** `/home/your_username/public_html/public/cron/daily-tasks.php`
   - **Minute:** `*/15`
   - **Hour:** `*`
   - **Day:** `*`
   - **Month:** `*`
   - **Weekday:** `*`
5. **Save**

### Step 2: Test It
SSH or Terminal:
```bash
php /home/your_username/public_html/public/cron/test-auto-checkout.php
```

### Step 3: Done! ✅
- Reminders send at **4:30 PM**
- Auto check-out at **6:00 PM**
- Check logs: `storage/logs/laravel.log`

---

## API Endpoints (For Admin UI)

### Get Settings
```javascript
GET /admin/notification-settings
```

Response:
```json
{
  "success": true,
  "settings": {
    "notification_type": "email",
    "sms_api_url": "https://sms.cisdepedcavite.org/api/send",
    "auto_checkout_time": "18:00",
    "reminder_time": "16:30"
  }
}
```

### Save Settings
```javascript
POST /admin/notification-settings
Content-Type: application/json

{
  "notification_type": "both",
  "sms_api_url": "https://sms.cisdepedcavite.org/api/send"
}
```

---

## Manual Testing Commands

### Test without making changes:
```bash
php public/cron/test-auto-checkout.php
```

### Run actual cron job:
```bash
php public/cron/daily-tasks.php
```

### Check logs:
```bash
tail -f storage/logs/laravel.log
```

---

## Database Quick Checks

### View current settings:
```sql
SELECT * FROM system_settings;
```

### Check users still checked in:
```sql
SELECT u.name, u.email, a.check_in_time 
FROM attendances a 
JOIN users u ON a.user_id = u.id 
WHERE a.check_out_time IS NULL 
AND DATE(a.check_in_time) = CURDATE();
```

### Update notification type:
```sql
UPDATE system_settings SET value = 'both' WHERE key = 'notification_type';
```

---

## Troubleshooting One-Liners

**Cron not running?**
```bash
# Check Hostinger cron panel → Verify path
```

**No notifications?**
```sql
SELECT * FROM system_settings WHERE key = 'notification_type';
```

**Wrong time?**
```sql
UPDATE system_settings SET value = '17:00' WHERE key = 'auto_checkout_time';
```

**Test SMS:**
```bash
curl -X POST https://sms.cisdepedcavite.org/api/send \
  -d "phone=+1234567890" \
  -d "message=Test"
```

---

## Files Reference

| File | Purpose |
|------|---------|
| `public/cron/daily-tasks.php` | Main cron job |
| `public/cron/test-auto-checkout.php` | Test script |
| `AUTO_CHECKOUT_SETUP.md` | Full setup guide |
| `IMPLEMENTATION_SUMMARY.md` | Complete overview |
| `storage/logs/laravel.log` | Log file |

---

## Support Checklist

Before asking for help:
- [ ] Ran `php public/cron/test-auto-checkout.php`
- [ ] Checked `storage/logs/laravel.log`
- [ ] Verified cron is enabled in Hostinger
- [ ] Confirmed time is correct (Asia/Manila)
- [ ] Tested email/SMS configuration

---

**That's it! Simple and effective.** 🎉
