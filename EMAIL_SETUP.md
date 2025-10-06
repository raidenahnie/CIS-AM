# Email Configuration for Password Reset

## Current Setup
The application is configured to use the `log` mail driver for development purposes. This means that password reset emails will be logged to `storage/logs/laravel.log` instead of being sent.

## For Production/Testing with Real Emails

### Option 1: SMTP (Recommended)
Update your `.env` file with SMTP settings:

```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host.com
MAIL_PORT=587
MAIL_USERNAME=your-email@company.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@company.com
MAIL_FROM_NAME="CIS-AM System"
```

### Option 2: Gmail SMTP
For Gmail, you'll need to use an app password:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-gmail@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-gmail@gmail.com
MAIL_FROM_NAME="CIS-AM System"
```

### Option 3: Mailtrap (For Testing)
For testing emails without sending real ones:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=test@example.com
MAIL_FROM_NAME="CIS-AM System"
```

## Testing Email Configuration

Run this command to test if email is working:
```bash
php artisan tinker
```

Then in tinker:
```php
Mail::raw('Test email from CIS-AM', function ($message) {
    $message->to('test@example.com')->subject('Test Email');
});
```

## How Password Reset Works

1. Admin clicks "Reset Password" for a user in the admin dashboard
2. System generates a secure token and stores it in the `password_resets` table
3. Email with reset link is sent to the user
4. User clicks the link and is taken to a password reset form
5. User submits new password, system validates the token and updates the password
6. Token is deleted after successful reset

## Security Features

- Tokens expire after 24 hours
- Tokens are hashed in the database
- Only one active token per user (new requests invalidate old ones)
- Expired tokens are automatically cleaned up
- Reset form validates password strength and confirmation