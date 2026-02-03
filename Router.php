<?php

/**
 * Router Class
 * 
 * A simple and clean routing system that implements:
 * - Route registration with HTTP method support
 * - Pattern matching with parameter extraction
 * - 404 fallback handling
 * 
 * Usage example:
 *   $router = new Router();
 *   $router->get('/login', function() { ... });
 *   $router->get('/produto/{id}', function($params) { ... });
 *   $router->dispatch();
 */
class Router {
    
    /**
     * @var array Registered routes grouped by HTTP method
     */
    private $routes = [
        'GET' => [],
        'POST' => []
    ];
    
    /**
     * @var callable|null 404 not found handler
     */
    private $notFoundHandler = null;
    
    /**
     * Register a GET route
     * 
     * @param string $pattern URL pattern (e.g., '/login' or '/produto/{id}')
     * @param callable $handler Function to execute when route matches
     * @return void
     */
    public function get($pattern, $handler) {
        $this->addRoute('GET', $pattern, $handler);
    }
    
    /**
     * Register a POST route
     * 
     * @param string $pattern URL pattern
     * @param callable $handler Function to execute when route matches
     * @return void
     */
    public function post($pattern, $handler) {
        $this->addRoute('POST', $pattern, $handler);
    }
    
    /**
     * Add a route to the routing table
     * 
     * @param string $method HTTP method
     * @param string $pattern URL pattern
     * @param callable $handler Route handler
     * @return void
     */
    private function addRoute($method, $pattern, $handler) {
        $this->routes[$method][$pattern] = $handler;
    }
    
    /**
     * Set custom 404 not found handler
     * 
     * @param callable $handler Function to execute when no route matches
     * @return void
     */
    public function setNotFoundHandler($handler) {
        $this->notFoundHandler = $handler;
    }
    
    /**
     * Dispatch the request to the appropriate route handler
     * 
     * Matches the current request URI and method against registered routes.
     * Extracts parameters from dynamic route segments.
     * 
     * @return void
     */
    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $this->getCurrentUri();
        
        // Try to find a matching route
        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $pattern => $handler) {
                $params = $this->matchRoute($pattern, $uri);
                
                if ($params !== false) {
                    // Route matched - execute handler with parameters
                    call_user_func($handler, $params);
                    return;
                }
            }
        }
        
        // No route matched - execute 404 handler
        $this->executeNotFoundHandler();
    }
    
    /**
     * Get the current request URI without query string
     * 
     * @return string Clean URI path
     */
    private function getCurrentUri() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove trailing slash unless it's the root
        if ($uri !== '/' && substr($uri, -1) === '/') {
            $uri = rtrim($uri, '/');
        }
        
        return $uri;
    }
    
    /**
     * Match a route pattern against a URI
     * 
     * Supports dynamic parameters in the format {paramName}.
     * Example: '/produto/{id}' matches '/produto/123' with params['id'] = '123'
     * 
     * @param string $pattern Route pattern
     * @param string $uri Request URI
     * @return array|false Array of extracted parameters or false if no match
     */
    private function matchRoute($pattern, $uri) {
        // Handle exact match for static routes
        if ($pattern === $uri) {
            return [];
        }
        
        // Convert route pattern to regex
        // Replace {param} with named capture groups
        $regex = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $pattern);
        
        // Escape forward slashes for regex
        $regex = str_replace('/', '\/', $regex);
        
        // Add anchors
        $regex = '/^' . $regex . '$/';
        
        // Try to match the URI against the pattern
        if (preg_match($regex, $uri, $matches)) {
            // Extract only named parameters
            $params = [];
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $params[$key] = $value;
                }
            }
            return $params;
        }
        
        return false;
    }
    
    /**
     * Execute the 404 not found handler
     * 
     * @return void
     */
    private function executeNotFoundHandler() {
        if ($this->notFoundHandler !== null) {
            call_user_func($this->notFoundHandler);
        } else {
            // Default 404 handler
            http_response_code(404);
            echo '<!DOCTYPE html>';
            echo '<html lang="pt">';
            echo '<head>';
            echo '<meta charset="UTF-8">';
            echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
            echo '<title>404 - Página Não Encontrada</title>';
            echo '<style>';
            echo 'body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #5D1F3A; color: #fff; }';
            echo 'h1 { font-size: 72px; margin: 0; color: #D4AF37; }';
            echo 'p { font-size: 20px; }';
            echo 'a { color: #D4AF37; text-decoration: none; }';
            echo 'a:hover { text-decoration: underline; }';
            echo '</style>';
            echo '</head>';
            echo '<body>';
            echo '<h1>404</h1>';
            echo '<p>Página não encontrada</p>';
            echo '<a href="/">Voltar para a página inicial</a>';
            echo '</body>';
            echo '</html>';
        }
    }
}
