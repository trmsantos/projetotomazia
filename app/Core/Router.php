<?php

namespace App\Core;

/**
 * Router - Handles URL routing with support for dynamic parameters
 */
class Router
{
    private array $routes = [];
    private array $namedRoutes = [];
    private $notFoundHandler;

    /**
     * Add a GET route
     */
    public function get(string $path, $handler, ?string $name = null): void
    {
        $this->addRoute('GET', $path, $handler, $name);
    }

    /**
     * Add a POST route
     */
    public function post(string $path, $handler, ?string $name = null): void
    {
        $this->addRoute('POST', $path, $handler, $name);
    }

    /**
     * Add a route that handles multiple methods
     */
    public function match(array $methods, string $path, $handler, ?string $name = null): void
    {
        foreach ($methods as $method) {
            $this->addRoute($method, $path, $handler, $name);
        }
    }

    /**
     * Add a route to the router
     */
    private function addRoute(string $method, string $path, $handler, ?string $name = null): void
    {
        $pattern = $this->convertPathToRegex($path);
        $this->routes[$method][] = [
            'path' => $path,
            'pattern' => $pattern,
            'handler' => $handler,
            'name' => $name
        ];
        
        if ($name) {
            $this->namedRoutes[$name] = $path;
        }
    }

    /**
     * Convert path with parameters to regex pattern
     */
    private function convertPathToRegex(string $path): string
    {
        // Replace {param} with regex capture group
        $pattern = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    /**
     * Set 404 not found handler
     */
    public function setNotFoundHandler($handler): void
    {
        $this->notFoundHandler = $handler;
    }

    /**
     * Dispatch the request
     */
    public function dispatch(string $method, string $uri): void
    {
        // Remove query string from URI
        $uri = parse_url($uri, PHP_URL_PATH);
        
        // Remove trailing slash except for root
        if ($uri !== '/' && substr($uri, -1) === '/') {
            $uri = rtrim($uri, '/');
        }

        // Check if we have routes for this method
        if (!isset($this->routes[$method])) {
            $this->handleNotFound();
            return;
        }

        // Try to match route
        foreach ($this->routes[$method] as $route) {
            if (preg_match($route['pattern'], $uri, $matches)) {
                // Extract named parameters
                $params = array_filter($matches, function($key) {
                    return !is_numeric($key);
                }, ARRAY_FILTER_USE_KEY);

                $this->callHandler($route['handler'], $params);
                return;
            }
        }

        // No route matched
        $this->handleNotFound();
    }

    /**
     * Call the route handler
     */
    private function callHandler($handler, array $params = []): void
    {
        if (is_callable($handler)) {
            call_user_func_array($handler, [$params]);
        } elseif (is_string($handler)) {
            // Handler is "ControllerName@method"
            $parts = explode('@', $handler);
            if (count($parts) === 2) {
                $controllerClass = "App\\Controllers\\" . $parts[0];
                $method = $parts[1];
                
                if (class_exists($controllerClass)) {
                    $controller = new $controllerClass();
                    if (method_exists($controller, $method)) {
                        call_user_func_array([$controller, $method], [$params]);
                        return;
                    }
                }
            }
        }
        
        // If we get here, handler is invalid
        throw new \Exception("Invalid route handler");
    }

    /**
     * Handle 404 not found
     */
    private function handleNotFound(): void
    {
        if ($this->notFoundHandler) {
            call_user_func($this->notFoundHandler);
        } else {
            http_response_code(404);
            echo "404 - Page Not Found";
        }
    }

    /**
     * Generate URL for named route
     */
    public function url(string $name, array $params = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new \Exception("Route '$name' not found");
        }

        $path = $this->namedRoutes[$name];
        
        // Replace parameters in path
        foreach ($params as $key => $value) {
            $path = str_replace('{' . $key . '}', $value, $path);
        }

        return $path;
    }
}
