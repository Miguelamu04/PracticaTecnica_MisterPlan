<?php

namespace App\Services;

use App\Models\Reservation;
use DateTimeImmutable;
use InvalidArgumentException;

class ReservationService
{
    public function __construct(private Reservation $model)
    {
    }

    public function create(array $data): int
    {
        $requiredFields = [
            'guest_name',
            'guest_email',
            'accommodation',
            'check_in',
            'check_out',
            'amount'
        ];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || !is_scalar($data[$field]) ||
                trim((string) $data[$field]) === '') {
                throw new InvalidArgumentException("Required field: $field");
            }
        }

        
        $textFields = [
            'guest_name' => 150,
            'guest_email' => 254,
            'accommodation' => 200
        ];

        foreach ($textFields as $field => $maxLength) {
            if (!is_string($data[$field]) ||
                mb_strlen($data[$field], 'UTF-8') > $maxLength) {
                throw new InvalidArgumentException("Invalid field: $field");
            }
        }

        foreach (['check_in', 'check_out'] as $field) {
            if (!is_string($data[$field])) {
                throw new InvalidArgumentException("Invalid date: $field");
            }
        }

        if (isset($data['notes']) && !is_string($data['notes'])) {
            throw new InvalidArgumentException("Invalid notes");
        }


        if (!filter_var($data['guest_email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid guest email");
        }

        $checkIn = $this->parseDate($data['check_in']);
        $checkOut = $this->parseDate($data['check_out']);

        if (!$checkIn || !$checkOut || $checkOut <= $checkIn) {
            throw new InvalidArgumentException("Invalid reservation dates");
        }

        $amount = $data['amount'];

        if (!is_int($amount) && !is_float($amount) && !is_string($amount)) {
            throw new InvalidArgumentException("Invalid amount");
        }

        if (!preg_match('/^\d{1,8}(\.\d{1,2})?$/D', (string) $amount)) {
            throw new InvalidArgumentException("Invalid amount");
        }

        return $this->model->create($data);
    }
    
    public function findByFilters(array $filters): array
    {
        if (isset($filters['status']) && $filters['status'] !== '') {
            if (!is_string($filters['status']) ||
                !in_array($filters['status'], ['pendiente', 'confirmada', 'cancelada'], true)) {
                throw new InvalidArgumentException("Invalid status");
            }
        }

        foreach (['start_date', 'end_date'] as $field) {
            if (isset($filters[$field]) && $filters[$field] !== '') {
                if (!is_string($filters[$field]) ||
                    !$this->parseDate($filters[$field])) {
                    throw new InvalidArgumentException("Invalid date: $field");
                }
            }
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            if ($filters['end_date'] < $filters['start_date']) {
                throw new InvalidArgumentException("Invalid date range");
            }
        }

        if (isset($filters['guest']) && $filters['guest'] !== '') {
            if (!is_string($filters['guest']) ||
                mb_strlen($filters['guest'], 'UTF-8') > 150) {
                throw new InvalidArgumentException("Invalid guest");
            }
        }

        return $this->model->findByFilters($filters);
    }

    
    public function updateStatus(int $id, string $status): ?bool
    {
        if ($id <= 0) {
            throw new InvalidArgumentException("Invalid reservation ID");
        }

        if (!in_array($status, ['confirmada', 'cancelada'], true)) {
            throw new InvalidArgumentException("Invalid status");
        }

        return $this->model->updateStatus($id, $status);
    }

    
    public function findById(int $id): ?array
    {
        if ($id <= 0) {
            throw new InvalidArgumentException("Invalid reservation ID");
        }

        return $this->model->findById($id);
    }


    private function parseDate(string $value): ?DateTimeImmutable
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        if (!$date || $date->format('Y-m-d') !== $value) {
            return null;
        }

        return $date;
    }
}
