<?php

use App\Controllers\ReservationController;
use App\Database\Connection;
use App\Models\Reservation;
use App\Router;
use App\Services\ReservationService;

require dirname(__DIR__) . '/vendor/autoload.php';

Dotenv\Dotenv::createImmutable(dirname(__DIR__))->load();

$pdo = Connection::create();

$model = new Reservation($pdo);
$service = new ReservationService($model);
$controller = new ReservationController($service);

$router = new Router();

$router->add('GET', '/api/reservations', [$controller, 'index']);

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$router->dispatch($method, $path);
