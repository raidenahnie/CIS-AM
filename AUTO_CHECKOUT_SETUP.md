# Auto Check-Out & Notification System Setup

## Overview
This system automatically checks out users at 6 PM and sends reminders at 4:30 PM. Notifications can be sent via Email, SMS, or both.

## What's Been Implemented

### 1. **Auto Check-Out (6:00 PM)**
- Automatically checks out all users still checked in at 6 PM
- Adds note: "Auto checked-out by system at 6 PM"
- Sends notification to affected users

### 2. **Reminders (4:30 PM)**
- Sends reminder to users still checked in
- Message: "Don't forget to check out before leaving work. Auto check-out will happen at 6 PM."

### 3. **Flexible Notifications**
Admin can choose notification method:
- **Email Only**
- **SMS Only**
- **Both Email & SMS**
- **Disabled** (no notifications)

## Setup Instructions for Hostinger

### Step 1: Upload Files
Make sure these files are on your server:
- `public/cron/daily-tasks.php` - The cron job file

### Step 2: Configure Environment
Your `.env` file should have these settings (already added):
```env
# SMS Configuration
SMS_ENABLED=true
SMS_API_URL=https://sms.cisdepedcavite.org/api/send
SMS_API_KEY=
```

### Step 3: Set Up Cron Job in Hostinger

1. **Login to Hostinger Control Panel**
2. **Go to Advanced → Cron Jobs**
3. **Click "Create Cron Job"**
4. **Fill in the details:**

   **Type:** PHP
   
   **Script Path:** 
   ```
   /home/your_username/public_html/public/cron/daily-tasks.php
   ```
   
   **Minute:** `*/15` (every 15 minutes)
   
   **Hour:** `*` (all hours)
   
   **Day:** `*` (every day)
   
   **Month:** `*` (every month)
   
   **Weekday:** `*` (every weekday)
   
   **OR use this simple cron expression:**
   ```
   */15 * * * *
   ```

5. **Save the cron job**

### Step 4: Configure Notifications in Admin Panel

1. Login as admin
2. Go to **Admin Dashboard → Settings**
3. Look for **"Auto Check-Out & Notifications"** section
4. Choose your notification method:
   - Email Only (default)
   - SMS Only
   - Both Email & SMS
   - Disabled
5. Set your SMS API URL (default: `https://sms.cisdepedcavite.org/api/send`)
6. Click **Save Settings**

## How It Works

1. **Cron job runs every 15 minutes** (set in Hostinger)
2. **At 4:30 PM** (16:30):
   - Script checks for users still checked in
   - Sends reminder notifications
3. **At 6:00 PM** (18:00):
   - Script finds all users still checked in
   - Auto checks them out at 6 PM
   - Sends completion notifications

## Testing

### Test the Cron Job Manually
SSH into your server or use Hostinger's file manager terminal:
```bash
php /home/your_username/public_html/public/cron/daily-tasks.php
```

You should see output like:
```
==========================================
CIS-AM Daily Tasks
Running at: 2025-11-23 16:30:00
==========================================

📢 TASK: Sending check-out reminders...
   Found 5 users still checked in
   ✓ Sent reminder to: John Doe (john@example.com)
   ...

==========================================
Tasks completed successfully!
==========================================
```

### Check Logs
Logs are stored in `storage/logs/laravel.log`

Look for entries like:
```
[2025-11-23 16:30:00] local.INFO: Sent check-out reminders to 5 users
[2025-11-23 18:00:00] local.INFO: Auto checked-out 3 users at 6 PM
```

## Troubleshooting

### Cron Job Not Running?
1. Check cron job is enabled in Hostinger
2. Verify the path is correct
3. Check file permissions (should be 755)

### Notifications Not Sending?
1. Check notification settings in admin panel
2. Verify email settings in `.env` file
3. For SMS: confirm SMS API URL is correct
4. Check `storage/logs/laravel.log` for errors

### Users Not Auto Checking Out?
1. Run manual test: `php public/cron/daily-tasks.php`
2. Check if current time is between 18:00-18:15
3. Verify database has attendance records without check_out_time

## Customization

### Change Auto Check-Out Time
Edit in database:
```sql
UPDATE system_settings SET value = '17:00' WHERE key = 'auto_checkout_time';
```

### Change Reminder Time
Edit in database:
```sql
UPDATE system_settings SET value = '16:00' WHERE key = 'reminder_time';
```

### Modify Messages
Edit messages in `public/cron/daily-tasks.php`:
- Line 72: Reminder message
- Line 113: Auto check-out message

## Files Modified/Created

1. ✅ `public/cron/daily-tasks.php` - Main cron job script
2. ✅ `database/migrations/2025_11_23_154156_create_system_settings_table.php` - Settings table
3. ✅ `.env` - Added SMS configuration
4. ✅ `config/services.php` - Added SMS service config
5. ✅ `app/Http/Controllers/AdminController.php` - Added notification settings methods
6. ✅ `routes/web.php` - Added notification settings routes

## Important Notes

- ⏰ **Time Zone**: System uses Asia/Manila timezone (configured in `.env`)
- 📧 **Email**: Uses existing email configuration in `.env`
- 📱 **SMS**: Uses your SMS Gate API at `https://sms.cisdepedcavite.org`
- 🔄 **Automatic**: Runs every 15 minutes, but only acts at 4:30 PM and 6:00 PM
- 💾 **Database**: All settings stored in `system_settings` table

## Support

If you encounter issues:
1. Check `storage/logs/laravel.log` for error messages
2. Verify cron job is running in Hostinger control panel
3. Test manually with: `php public/cron/daily-tasks.php`
4. Ensure database connection is working
5. Check email/SMS API credentials are correct

---

**Setup Complete!** 🎉

Users will now be automatically checked out at 6 PM with reminders at 4:30 PM.
