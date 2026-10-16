<?php

namespace App\Controllers;

use Twig\Environment;
use App\Services\ReservationService;

class PageController
{
    public function __construct(
    private Environment $twig,
    private ReservationService $service
    ) {
    }

    public function reservations(): void
    {
        $reservations = $this->service->findByFilters([]);

        echo $this->twig->render('reservations/index.twig', [
            'reservations' => $reservations
        ]);
    }

    
    public function show(string $id): void
    {
        if (!ctype_digit($id) || $id === '0' || (string) (int) $id !== $id) {
            http_response_code(400);
            echo 'Invalid reservation ID';
            return;
        }

        $reservation = $this->service->findById((int) $id);

        if ($reservation === null) {
            http_response_code(404);
            echo 'Reservation not found';
            return;
        }

        $events = $this->service->findEventsByReservationId((int) $id);

        echo $this->twig->render('reservations/show.twig', [
            'reservation' => $reservation,
            'events' => $events
        ]);
    }

}
