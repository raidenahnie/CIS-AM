<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

// Bootstrap the framework
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Call the controller
$controller = new \App\Http\Controllers\Api\DashboardController();
$response = $controller->getAttendanceHistory(2);

if (method_exists($response, 'getData')) {
    $data = $response->getData(true);
    echo json_encode($data, JSON_PRETTY_PRINT);
} else {
    // If it's already an array or collection
    echo json_encode($response, JSON_PRETTY_PRINT);
}
