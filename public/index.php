<?php

use App\Controllers\ReservationController;
use App\Database\Connection;
use App\Models\Reservation;
use App\Router;
use App\Services\ReservationService;
use App\Controllers\PageController;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require dirname(__DIR__) . '/vendor/autoload.php';
ini_set('display_errors', '0');

try{
Dotenv\Dotenv::createImmutable(dirname(__DIR__))->load();

$pdo = Connection::create();

$model = new Reservation($pdo);
$service = new ReservationService($model);
$controller = new ReservationController($service);
$loader = new FilesystemLoader(dirname(__DIR__) . '/views');

$twig = new Environment($loader, [
    'autoescape' => 'html'
]);

$pageController = new PageController($twig, $service);

$router = new Router();

$router->add('GET', '/reservations', [$pageController, 'reservations']);
$router->add('GET', '/reservations/{id}', [$pageController, 'show']);
$router->add('GET', '/api/reservations', [$controller, 'index']);
$router->add('GET', '/api/reservations/{id}', [$controller, 'show']);
$router->add('POST', '/api/reservations', [$controller, 'store']);
$router->add(
    'PATCH',
    '/api/reservations/{id}/status',
    [$controller, 'updateStatus']
);

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$router->dispatch($method, $path);
} catch (Throwable $exception) {
    error_log($exception);

    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'error' => 'Internal server error'
    ]);
}
