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
}
