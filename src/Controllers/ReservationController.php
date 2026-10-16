<?php

namespace App\Controllers;

use App\Services\ReservationService;
use InvalidArgumentException;

class ReservationController
{
    public function __construct(private ReservationService $service)
    {
    }

    public function index(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $reservations = $this->service->findByFilters($_GET);

            http_response_code(200);

            echo json_encode([
                'data' => $reservations
            ], JSON_UNESCAPED_UNICODE);

        } catch (InvalidArgumentException $exception) {
            http_response_code(400);

            echo json_encode([
                'error' => $exception->getMessage()
            ]);
        }
    }

    
    public function show(string $id): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!ctype_digit($id) || $id === '0' || (string) (int) $id !== $id) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid reservation ID']);
            return;
        }

        $reservation = $this->service->findById((int) $id);

        if ($reservation === null) {
            http_response_code(404);
            echo json_encode(['error' => 'Reservation not found']);
            return;
        }

        http_response_code(200);

        echo json_encode([
            'data' => $reservation
        ], JSON_UNESCAPED_UNICODE);
    }

}
