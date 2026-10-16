<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Reservation;
use App\Services\ReservationService;

$model = new ReflectionClass(Reservation::class);
$reservation = $model->newInstanceWithoutConstructor();

$service = new ReservationService($reservation);


function testInvalidDates(ReservationService $service): bool
{
    $data = [
        'guest_name' => 'Test Guest',
        'guest_email' => 'test@example.com',
        'accommodation' => 'Test Apartment',
        'check_in' => '2026-12-23',
        'check_out' => '2026-12-20',
        'amount' => '150.00'
    ];

    try {
        $service->create($data);
        return false;
    } catch (InvalidArgumentException $exception) {
        return $exception->getMessage() === 'Invalid reservation dates';
    }
}


function testInvalidAmount(ReservationService $service): bool
{
    $data = [
        'guest_name' => 'Test Guest',
        'guest_email' => 'test@example.com',
        'accommodation' => 'Test Apartment',
        'check_in' => '2026-12-20',
        'check_out' => '2026-12-23',
        'amount' => '-100.00'
    ];

    try {
        $service->create($data);
        return false;
    } catch (InvalidArgumentException $exception) {
        return $exception->getMessage() === 'Invalid amount';
    }
}

function testNotesTooLong(ReservationService $service): bool
{
    $data = [
        'guest_name' => 'Test Guest',
        'guest_email' => 'test@example.com',
        'accommodation' => 'Test Apartment',
        'check_in' => '2026-12-20',
        'check_out' => '2026-12-23',
        'amount' => '150.00',
        'notes' => str_repeat('A', 65536)
    ];

    try {
        $service->create($data);
        return false;
    } catch (InvalidArgumentException $exception) {
        return $exception->getMessage() === 'Invalid notes';
    }
}

$passed = 0;
$failed = 0;

try {
    $service->create([]);
    echo "FAIL: Required fields validation\n";
    $failed++;
} catch (InvalidArgumentException $exception) {
    if ($exception->getMessage() === 'Required field: guest_name') {
        echo "PASS: Required fields validation\n";
        $passed++;
    } else {
        echo "FAIL: Required fields validation\n";
        $failed++;
    }
}

if (testInvalidDates($service)) {
    echo "PASS: Invalid reservation dates\n";
    $passed++;
} else {
    echo "FAIL: Invalid reservation dates\n";
    $failed++;
}


if (testInvalidAmount($service)) {
    echo "PASS: Invalid amount\n";
    $passed++;
} else {
    echo "FAIL: Invalid amount\n";
    $failed++;
}

if (testNotesTooLong($service)) {
    echo "PASS: Notes length validation\n";
    $passed++;
} else {
    echo "FAIL: Notes length validation\n";
    $failed++;
}

echo "\nResults: $passed passed, $failed failed\n";

exit($failed > 0 ? 1 : 0);
