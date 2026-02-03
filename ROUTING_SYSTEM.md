# Routing System Documentation

## Overview

This document describes the professional routing system implemented for the Bar da Tomazia PHP application. The system implements a Front Controller pattern with clean URLs and modern route handling.

## Architecture

### Components

1. **Router Class** (`Router.php`) - Core routing logic
2. **Front Controller** (`public/index.php`) - Entry point for all requests
3. **Route Definitions** (`routes.php`) - Application route mappings
4. **Apache Configuration** (`.htaccess`) - URL rewriting rules
5. **Controllers** (`controllers/`) - Optional controller classes

## Features

### ✅ Front Controller Pattern
All requests are routed through `public/index.php`, providing a centralized entry point.

### ✅ Clean URLs
URLs no longer require `.php` extensions:
- Old: `http://example.com/login.php`
- New: `http://example.com/login`

### ✅ Route Parameter Extraction
Dynamic parameters are automatically extracted from URLs:
- Route: `/produto/{id}`
- URL: `/produto/123`
- Result: `$params['id'] = '123'`

### ✅ HTTP Method Support
Routes can be registered for different HTTP methods:
- `$router->get('/path', $handler)` - GET requests
- `$router->post('/path', $handler)` - POST requests

### ✅ 404 Fallback Handler
Graceful handling of undefined routes with customizable 404 pages.

## Directory Structure

```
projetotomazia/
├── .htaccess                 # Root rewrite rules
├── Router.php                # Router class
├── routes.php                # Route definitions
├── config.php                # Application config
├── controllers/              # Controller classes
│   └── ProductController.php # Example controller
├── public/                   # Public web root
│   ├── .htaccess            # Public rewrite rules
│   ├── index.php            # Front controller
│   ├── css/                 # Stylesheets
│   ├── js/                  # JavaScript files
│   └── img/                 # Images
└── *.php                    # Legacy page files
```

## Usage Examples

### Defining Static Routes

```php
// In routes.php

// Simple GET route
$router->get('/login', function($params) {
    require __DIR__ . '/login.php';
});

// POST route for form submission
$router->post('/login', function($params) {
    require __DIR__ . '/login.php';
});
```

### Defining Dynamic Routes

```php
// Route with single parameter
$router->get('/produto/{id}', function($params) {
    $productId = $params['id'];
    // Use $productId to load product data
});

// Route with multiple parameters
$router->get('/user/{id}/post/{postId}', function($params) {
    $userId = $params['id'];
    $postId = $params['postId'];
    // Handle user post
});
```

### Using Controllers

```php
// In routes.php
require_once __DIR__ . '/controllers/ProductController.php';
$productController = new ProductController();

// Register controller method as route handler
$router->get('/produto/{id}', [$productController, 'show']);
```

```php
// In controllers/ProductController.php
class ProductController {
    public function show($params) {
        $productId = $params['id'];
        // Controller logic here
    }
}
```

### Custom 404 Handler

```php
// In routes.php
$router->setNotFoundHandler(function() {
    http_response_code(404);
    require __DIR__ . '/erro.php';
});
```

## Router API Reference

### Router::get($pattern, $handler)
Register a GET route.

**Parameters:**
- `$pattern` (string) - URL pattern (e.g., `/login` or `/produto/{id}`)
- `$handler` (callable) - Function or controller method to execute

**Example:**
```php
$router->get('/cardapio', function($params) {
    require __DIR__ . '/cardapio.php';
});
```

### Router::post($pattern, $handler)
Register a POST route.

**Parameters:**
- `$pattern` (string) - URL pattern
- `$handler` (callable) - Function or controller method to execute

**Example:**
```php
$router->post('/register', function($params) {
    require __DIR__ . '/register.php';
});
```

### Router::setNotFoundHandler($handler)
Set a custom 404 handler.

**Parameters:**
- `$handler` (callable) - Function to execute when no route matches

**Example:**
```php
$router->setNotFoundHandler(function() {
    http_response_code(404);
    echo 'Page not found';
});
```

### Router::dispatch()
Match the current request to a route and execute the handler.

**Example:**
```php
$router->dispatch();
```

## Route Pattern Syntax

### Static Routes
Routes with fixed paths:
```php
'/login'           // Matches: /login
'/admin/dashboard' // Matches: /admin/dashboard
```

### Dynamic Routes
Routes with parameters in `{paramName}` format:
```php
'/produto/{id}'              // Matches: /produto/123
'/user/{userId}/edit'        // Matches: /user/456/edit
'/blog/{year}/{month}/{day}' // Matches: /blog/2024/12/25
```

### Parameter Extraction
Parameters are automatically extracted and passed to handlers:
```php
$router->get('/produto/{id}', function($params) {
    // $params is an array: ['id' => '123']
    $id = $params['id'];
});
```

## Apache Configuration

### Root .htaccess
Redirects requests to the public directory:
```apache
RewriteEngine On
RewriteBase /

# Redirect to public directory
RewriteCond %{REQUEST_URI} !^/public/
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ public/$1 [L]

# Route through front controller
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ public/index.php [QSA,L]
```

### Public .htaccess
Routes requests to the front controller while preserving static files:
```apache
RewriteEngine On

# Allow direct access to static files
RewriteCond %{REQUEST_FILENAME} -f
RewriteRule ^ - [L]

# Allow access to directories
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]

# Route to front controller
RewriteRule ^(.*)$ index.php [QSA,L]
```

## Migration from Legacy URLs

### Backward Compatibility
The routing system maintains backward compatibility. Legacy `.php` files continue to work until routes are defined for them.

### Migration Steps

1. **Keep legacy files**: Don't delete existing `.php` files initially
2. **Add routes**: Define routes in `routes.php` for each page
3. **Test thoroughly**: Verify all functionality works through routes
4. **Update links**: Update internal links to use new clean URLs
5. **Remove legacy files**: (Optional) After full migration

### Example Migration

**Before:**
```html
<a href="login.php">Login</a>
```

**After:**
```html
<a href="/login">Login</a>
```

## Security Considerations

### Path Traversal Protection
The router validates paths and prevents directory traversal attacks.

### Input Validation
Always validate and sanitize route parameters:
```php
$router->get('/produto/{id}', function($params) {
    $id = intval($params['id']); // Convert to integer
    if ($id <= 0) {
        // Invalid ID - show error
        return;
    }
    // Safe to use $id
});
```

### CSRF Protection
Continue using CSRF tokens for POST requests:
```php
$router->post('/form', function($params) {
    if (!verifyCsrfToken($_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }
    // Process form
});
```

## Testing

### Testing Routes

Test your routes by accessing them in a browser or using curl:

```bash
# Test static route
curl http://localhost/login

# Test dynamic route
curl http://localhost/produto/123

# Test 404 handling
curl http://localhost/nonexistent
```

### Debugging

Enable PHP error reporting for debugging:
```php
// In public/index.php (for development only)
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## Performance Considerations

### Caching
- Static assets (CSS, JS, images) are served directly by Apache
- Route matching is optimized with regex compilation

### Best Practices
1. Define frequently accessed routes first in `routes.php`
2. Use controllers for complex logic to keep routes clean
3. Keep route handlers lightweight
4. Cache database queries where appropriate

## Troubleshooting

### Issue: 404 errors for all routes
**Solution:** Verify Apache mod_rewrite is enabled:
```bash
a2enmod rewrite
service apache2 restart
```

### Issue: Static files not loading
**Solution:** Check `.htaccess` rules allow direct file access.

### Issue: Parameters not extracted
**Solution:** Verify route pattern uses correct syntax: `{paramName}`

## Future Enhancements

Potential improvements for the routing system:

1. **Middleware support** - Add before/after hooks for routes
2. **Route groups** - Group routes with common prefixes
3. **HTTP method routing** - Add PUT, DELETE, PATCH support
4. **Route caching** - Cache compiled route patterns
5. **Named routes** - Reference routes by name instead of URL

## Conclusion

The routing system provides a modern, professional foundation for the application while maintaining backward compatibility with legacy code. It enables clean URLs, better code organization, and improved security through centralized request handling.

For questions or issues, please refer to the codebase or contact the development team.
