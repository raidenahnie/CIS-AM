<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

App\Models\SystemSetting::set('sms_api_url', 'https://sms.cisdepedcavite.org/send-sms.php');
echo "✓ SMS API URL updated to: https://sms.cisdepedcavite.org/send-sms.php\n";
