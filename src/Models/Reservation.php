<?php

namespace App\Models;

use PDO;

class Reservation
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM reserva WHERE id = :id";

        $statement = $this->pdo->prepare($sql);
        $statement->execute(['id' => $id]);

        $reservation = $statement->fetch();

        return $reservation ?: null;
    }

    
    public function findAll(): array
    {
        $sql = "SELECT * FROM reserva ORDER BY fecha_creacion DESC, id DESC";

        $statement = $this->pdo->query($sql);

        return $statement->fetchAll();
    }

    
    public function findByFilters(array $filters): array
    {
        $sql = "SELECT * FROM reserva WHERE 1 = 1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND estado = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['start_date'])) {
            $sql .= " AND fecha_entrada >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $sql .= " AND fecha_salida <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }

        if (!empty($filters['guest'])) {
            $sql .= " AND nombre_huesped LIKE :guest";
            $params['guest'] = '%' . $filters['guest'] . '%';
        }

        $sql .= " ORDER BY fecha_creacion DESC, id DESC";

        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }
    public function create(array $data): int
    {
        $sql = "INSERT INTO reserva (
                    nombre_huesped,
                    email_huesped,
                    nombre_alojamiento,
                    fecha_entrada,
                    fecha_salida,
                    importe,
                    notas
                ) VALUES (
                    :guest_name,
                    :guest_email,
                    :accommodation,
                    :check_in,
                    :check_out,
                    :amount,
                    :notes
                )";

        $this->pdo->beginTransaction();

        try {
            $statement = $this->pdo->prepare($sql);

            $statement->execute([
                'guest_name' => $data['guest_name'],
                'guest_email' => $data['guest_email'],
                'accommodation' => $data['accommodation'],
                'check_in' => $data['check_in'],
                'check_out' => $data['check_out'],
                'amount' => $data['amount'],
                'notes' => $data['notes'] ?? null
            ]);

            $reservationId = (int) $this->pdo->lastInsertId();

            $eventSql = "INSERT INTO reserva_evento (
                            reserva_id,
                            tipo,
                            descripcion
                        ) VALUES (
                            :reservation_id,
                            'creacion',
                            'Reserva creada en estado pendiente'
                        )";

            $eventStatement = $this->pdo->prepare($eventSql);
            $eventStatement->execute([
                'reservation_id' => $reservationId
            ]);

            $this->pdo->commit();

            return $reservationId;

        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }

    
    public function findEventsByReservationId(int $reservationId): array
    {
        $sql = "SELECT *
                FROM reserva_evento
                WHERE reserva_id = :reservation_id
                ORDER BY fecha_creacion ASC, id ASC";

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            'reservation_id' => $reservationId
        ]);

        return $statement->fetchAll();
    }

    
    public function updateStatus(int $id, string $status): ?bool
    {
        $this->pdo->beginTransaction();

        try {
            $statement = $this->pdo->prepare(
                "SELECT estado FROM reserva WHERE id = :id FOR UPDATE"
            );
            $statement->execute(['id' => $id]);

            $reservation = $statement->fetch();

            if (!$reservation) {
                $this->pdo->rollBack();
                return null;
            }

            if ($reservation['estado'] === $status) {
                $this->pdo->commit();
                return false;
            }

            $statement = $this->pdo->prepare(
                "UPDATE reserva SET estado = :status WHERE id = :id"
            );
            $statement->execute([
                'status' => $status,
                'id' => $id
            ]);

            $eventType = $status === 'confirmada'
                ? 'confirmacion'
                : 'cancelacion';

            $description = "Cambio de estado: "
                . $reservation['estado'] . " a " . $status;

            $statement = $this->pdo->prepare(
                "INSERT INTO reserva_evento (reserva_id, tipo, descripcion)
                VALUES (:id, :type, :description)"
            );

            $statement->execute([
                'id' => $id,
                'type' => $eventType,
                'description' => $description
            ]);

            $this->pdo->commit();

            return true;

        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }

}
