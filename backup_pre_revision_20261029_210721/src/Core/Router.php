<?php
// src/Core/Router.php

namespace App\Core;

class Router {
    private array $routes = [];

    public function get(string $path, $handler): void {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, $handler): void {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(): void {
        $method = Request::method();
        $route = Request::get('route', 'dashboard');

        // Check if route exists for current method
        if (isset($this->routes[$method][$route])) {
            $handler = $this->routes[$method][$route];
            $this->invokeHandler($handler);
            return;
        }

        // If POST route was requested but only GET exists or vice-versa
        if (isset($this->routes['GET'][$route]) && $method !== 'GET') {
            $handler = $this->routes['GET'][$route];
            $this->invokeHandler($handler);
            return;
        }

        // 404 Not Found
        http_response_code(404);
        View::render('errors.404', ['route' => $route]);
    }

    private function invokeHandler($handler): void {
        if (is_callable($handler)) {
            call_user_func($handler);
        } elseif (is_array($handler) && count($handler) === 2) {
            [$class, $action] = $handler;
            $controller = new $class();
            $controller->$action();
        } elseif (is_string($handler) && strpos($handler, '@') !== false) {
            [$class, $action] = explode('@', $handler);
            $fullClass = "\\App\\Controllers\\" . $class;
            $controller = new $fullClass();
            $controller->$action();
        }
    }
}
