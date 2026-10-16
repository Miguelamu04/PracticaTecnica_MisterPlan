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
}
