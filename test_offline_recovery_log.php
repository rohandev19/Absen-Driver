<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing OfflineRecoveryLog Model\n";
echo "=================================\n\n";

// Check if model class exists
echo "1. Model Class: ";
if (class_exists('App\Models\OfflineRecoveryLog')) {
    echo "✓ OfflineRecoveryLog class exists\n";
} else {
    echo "✗ OfflineRecoveryLog class not found\n";
    exit(1);
}

// Create model instance
$model = new \App\Models\OfflineRecoveryLog();

// Check fillable fields
echo "\n2. Fillable Fields:\n";
$fillable = $model->getFillable();
$expectedFillable = [
    'driver_id',
    'attendance_id',
    'offline_entry_id',
    'device_timestamp',
    'recovery_timestamp',
    'delay_minutes',
    'result',
    'error_code',
    'error_message',
    'retry_count',
    'photo_size_kb',
];

foreach ($expectedFillable as $field) {
    $status = in_array($field, $fillable) ? '✓' : '✗';
    echo "   {$status} {$field}\n";
}

// Check casts
echo "\n3. Type Casts:\n";
$casts = $model->getCasts();
$expectedCasts = [
    'device_timestamp' => 'datetime',
    'recovery_timestamp' => 'datetime',
    'result' => 'string',
];

foreach ($expectedCasts as $field => $expectedType) {
    $actualType = $casts[$field] ?? 'not set';
    $status = ($actualType === $expectedType) ? '✓' : '✗';
    echo "   {$status} {$field}: {$actualType} (expected: {$expectedType})\n";
}

// Check relationships
echo "\n4. Relationships:\n";
try {
    $driver = $model->driver();
    echo "   ✓ driver() relationship exists (belongsTo Driver)\n";
} catch (Exception $e) {
    echo "   ✗ driver() relationship error: " . $e->getMessage() . "\n";
}

try {
    $attendance = $model->attendance();
    echo "   ✓ attendance() relationship exists (belongsTo Attendance)\n";
} catch (Exception $e) {
    echo "   ✗ attendance() relationship error: " . $e->getMessage() . "\n";
}

echo "\n5. Table Name:\n";
echo "   ✓ Table: {$model->getTable()}\n";

echo "\nAll checks completed!\n";
