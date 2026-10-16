<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Reservation;
use App\Services\ReservationService;

function validReservationData(): array
{
    return [
        'guest_name' => 'Test Guest',
        'guest_email' => 'test@example.com',
        'accommodation' => 'Test Apartment',
        'check_in' => '2026-12-20',
        'check_out' => '2026-12-23',
        'amount' => '150.00'
    ];
}

function expectValidationError(
    ReservationService $service,
    array $data,
    string $expectedMessage
): bool {
    try {
        $service->create($data);
        return false;
    } catch (InvalidArgumentException $exception) {
        return $exception->getMessage() === $expectedMessage;
    }
}

$reflection = new ReflectionClass(Reservation::class);
$model = $reflection->newInstanceWithoutConstructor();
$service = new ReservationService($model);

$invalidDates = validReservationData();
$invalidDates['check_in'] = '2026-12-23';
$invalidDates['check_out'] = '2026-12-20';

$invalidAmount = validReservationData();
$invalidAmount['amount'] = '-100.00';

$notesTooLong = validReservationData();
$notesTooLong['notes'] = str_repeat('A', 65536);

$tests = [
    'Required fields validation' => [
        [],
        'Required field: guest_name'
    ],
    'Invalid reservation dates' => [
        $invalidDates,
        'Invalid reservation dates'
    ],
    'Invalid amount' => [
        $invalidAmount,
        'Invalid amount'
    ],
    'Notes length validation' => [
        $notesTooLong,
        'Invalid notes'
    ]
];

$passed = 0;
$failed = 0;

foreach ($tests as $name => [$data, $expectedMessage]) {
    if (expectValidationError($service, $data, $expectedMessage)) {
        echo "PASS: $name\n";
        $passed++;
    } else {
        echo "FAIL: $name\n";
        $failed++;
    }
}

echo "\nResults: $passed passed, $failed failed\n";

exit($failed > 0 ? 1 : 0);
