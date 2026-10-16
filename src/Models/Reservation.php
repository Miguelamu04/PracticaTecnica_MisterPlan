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
}
