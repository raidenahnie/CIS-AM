<?php
/**
 * Test if the notification endpoint is accessible
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');

echo "=== Testing Notification Endpoint ===\n\n";

// Simulate a POST request to the test notification endpoint
$request = \Illuminate\Http\Request::create(
    '/admin/test-notification',
    'POST',
    ['notification_type' => 'email']
);

// Add authentication - get admin user
$admin = \App\Models\User::where('role', 'admin')->first();
if ($admin) {
    $request->setUserResolver(function () use ($admin) {
        return $admin;
    });
    echo "Authenticated as: {$admin->name} ({$admin->email})\n\n";
} else {
    echo "ERROR: No admin user found\n";
    exit(1);
}

try {
    echo "Sending request to: POST /admin/test-notification\n";
    $response = $kernel->handle($request);
    
    echo "Status Code: " . $response->getStatusCode() . "\n";
    echo "Response:\n";
    echo $response->getContent() . "\n\n";
    
    if ($response->getStatusCode() === 200) {
        echo "✓ Endpoint is working!\n";
        $data = json_decode($response->getContent(), true);
        if (isset($data['success']) && $data['success']) {
            echo "✓ Test notification sent successfully!\n";
            if (isset($data['details'])) {
                echo "Details:\n";
                foreach ($data['details'] as $detail) {
                    echo "  - $detail\n";
                }
            }
        } else {
            echo "✗ Test notification failed\n";
            if (isset($data['message'])) {
                echo "Message: " . $data['message'] . "\n";
            }
        }
    } else {
        echo "✗ Endpoint returned error code\n";
    }
    
} catch (\Exception $e) {
    echo "✗ Exception occurred!\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}
