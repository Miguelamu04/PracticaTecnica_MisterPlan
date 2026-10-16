<?php

namespace App\Controllers;

use Twig\Environment;

class PageController
{
    public function __construct(private Environment $twig)
    {
    }

    public function reservations(): void
    {
        echo $this->twig->render('reservations/index.twig', [
            'title' => 'Reservations'
        ]);
    }
}
