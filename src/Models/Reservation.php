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

}
