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
            try {
                $reservations = $this->service->findByFilters($_GET);

                $this->jsonResponse(200, [
                    'data' => $reservations
                ]);
            } catch (InvalidArgumentException $exception) {
                $this->jsonResponse(400, [
                    'error' => $exception->getMessage()
                ]);
            }
        }

    
    public function show(string $id): void
    {
        $reservationId = $this->parseReservationId($id);

        if ($reservationId === null) {
            $this->jsonResponse(400, ['error' => 'Invalid reservation ID']);
            return;
        }

        $reservation = $this->service->findById($reservationId);

        if ($reservation === null) {
            $this->jsonResponse(404, ['error' => 'Reservation not found']);
            return;
        }

        $this->jsonResponse(200, [
            'data' => $reservation
        ]);
    }

    
    public function store(): void
    {
        $data = $this->readJson();

        if ($data === null) {
            $this->jsonResponse(400, ['error' => 'Invalid JSON']);
            return;
        }

        try {
            $id = $this->service->create($data);

            $this->jsonResponse(201, [
                'message' => 'Reservation created',
                'id' => $id
            ]);
        } catch (InvalidArgumentException $exception) {
            $this->jsonResponse(400, [
                'error' => $exception->getMessage()
            ]);
        }
    }

    
    public function updateStatus(string $id): void
    {
        $reservationId = $this->parseReservationId($id);

        if ($reservationId === null) {
            $this->jsonResponse(400, ['error' => 'Invalid reservation ID']);
            return;
        }

        $data = $this->readJson();

        if ($data === null) {
            $this->jsonResponse(400, ['error' => 'Invalid JSON']);
            return;
        }

        if (!isset($data['status']) || !is_string($data['status'])) {
            $this->jsonResponse(400, ['error' => 'Invalid status']);
            return;
        }

        try {
            $result = $this->service->updateStatus($reservationId, $data['status']);

            if ($result === null) {
                $this->jsonResponse(404, ['error' => 'Reservation not found']);
                return;
            }

            $this->jsonResponse(200, [
                'message' => $result
                    ? 'Reservation status updated'
                    : 'Reservation status unchanged'
            ]);
        } catch (InvalidArgumentException $exception) {
            $this->jsonResponse(400, [
                'error' => $exception->getMessage()
            ]);
        }
    }
    
    private function readJson(): ?array
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!is_array($data) || json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return $data;
    }

    private function parseReservationId(string $id): ?int
    {
        if (!ctype_digit($id) || $id === '0' || (string) (int) $id !== $id) {
            return null;
        }

        return (int) $id;
    }

    private function jsonResponse(int $statusCode, array $data): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
