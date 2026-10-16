<?php

namespace App;

class Router
{
    private array $routes = [];

    public function add(string $method, string $path, callable $handler): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }

    public function dispatch(string $method, string $path): void
    {
        $methodAllowed = false;

        foreach ($this->routes as $route) {
            $pattern = preg_replace('/\{id\}/', '([0-9]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if (!preg_match($pattern, $path, $matches)) {
                continue;
            }

            if ($route['method'] !== $method) {
                $methodAllowed = true;
                continue;
            }

            $route['handler'](...array_slice($matches, 1));
            return;
        }

        header('Content-Type: application/json; charset=utf-8');

        if ($methodAllowed) {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        http_response_code(404);
        echo json_encode(['error' => 'Route not found']);
    }
}
