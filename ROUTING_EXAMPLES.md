# Routing System Examples

This document provides practical examples of using the routing system in the Bar da Tomazia application.

## Basic Examples

### Example 1: Simple Static Route

```php
// In routes.php
$router->get('/about', function($params) {
    echo '<h1>About Us</h1>';
    echo '<p>Welcome to Bar da Tomazia!</p>';
});
```

**Access:** `http://yourdomain.com/about`

### Example 2: Route with Existing Page

```php
// In routes.php
$router->get('/login', function($params) {
    require __DIR__ . '/login.php';
});
```

**Access:** `http://yourdomain.com/login`

### Example 3: POST Route for Form Handling

```php
// In routes.php
$router->post('/contact', function($params) {
    require __DIR__ . '/contact.php';
});
```

**Usage:** Form submissions to `/contact` will be handled

## Dynamic Route Examples

### Example 4: Single Parameter Route

```php
// In routes.php
$router->get('/produto/{id}', function($params) {
    $productId = intval($params['id']);
    
    // Load product from database
    $db = getDbConnection();
    $stmt = $db->prepare('SELECT * FROM produtos WHERE id_produto = :id');
    $stmt->bindValue(':id', $productId, SQLITE3_INTEGER);
    $product = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    
    if ($product) {
        echo '<h1>' . htmlspecialchars($product['nome_prod']) . '</h1>';
        echo '<p>Preço: €' . number_format($product['preco'], 2) . '</p>';
    } else {
        echo '<h1>Produto não encontrado</h1>';
    }
});
```

**Access:** 
- `http://yourdomain.com/produto/1` → Shows product with ID 1
- `http://yourdomain.com/produto/123` → Shows product with ID 123

### Example 5: Multiple Parameters Route

```php
// In routes.php
$router->get('/category/{category}/produto/{id}', function($params) {
    $category = htmlspecialchars($params['category']);
    $productId = intval($params['id']);
    
    echo '<h1>Category: ' . $category . '</h1>';
    echo '<p>Product ID: ' . $productId . '</p>';
});
```

**Access:** 
- `http://yourdomain.com/category/bebidas/produto/5`
- Results in: `$params['category'] = 'bebidas'`, `$params['id'] = '5'`

## Controller Examples

### Example 6: Using a Controller Class

**Step 1:** Create the controller (`controllers/EventController.php`):

```php
<?php
class EventController {
    private $db;
    
    public function __construct() {
        $this->db = getDbConnection();
    }
    
    public function show($params) {
        $eventId = intval($params['id']);
        
        $stmt = $this->db->prepare('SELECT * FROM eventos WHERE id_evento = :id');
        $stmt->bindValue(':id', $eventId, SQLITE3_INTEGER);
        $event = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        
        if ($event) {
            $this->renderEvent($event);
        } else {
            $this->render404();
        }
    }
    
    private function renderEvent($event) {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title><?php echo htmlspecialchars($event['nome']); ?></title>
        </head>
        <body>
            <h1><?php echo htmlspecialchars($event['nome']); ?></h1>
            <p><?php echo htmlspecialchars($event['descricao']); ?></p>
            <p>Data: <?php echo htmlspecialchars($event['data']); ?></p>
        </body>
        </html>
        <?php
    }
    
    private function render404() {
        http_response_code(404);
        echo '<h1>Evento não encontrado</h1>';
    }
}
```

**Step 2:** Register the route in `routes.php`:

```php
require_once __DIR__ . '/controllers/EventController.php';
$eventController = new EventController();
$router->get('/evento/{id}', [$eventController, 'show']);
```

**Access:** `http://yourdomain.com/evento/1`

### Example 7: RESTful Controller Pattern

```php
// In controllers/ApiController.php
class ApiController {
    public function listProducts($params) {
        header('Content-Type: application/json');
        $db = getDbConnection();
        $products = [];
        
        $result = $db->query('SELECT * FROM produtos');
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $products[] = $row;
        }
        
        echo json_encode($products);
    }
    
    public function getProduct($params) {
        header('Content-Type: application/json');
        $productId = intval($params['id']);
        
        $db = getDbConnection();
        $stmt = $db->prepare('SELECT * FROM produtos WHERE id_produto = :id');
        $stmt->bindValue(':id', $productId, SQLITE3_INTEGER);
        $product = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        
        if ($product) {
            echo json_encode($product);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Product not found']);
        }
    }
}

// In routes.php
require_once __DIR__ . '/controllers/ApiController.php';
$apiController = new ApiController();
$router->get('/api/produtos', [$apiController, 'listProducts']);
$router->get('/api/produtos/{id}', [$apiController, 'getProduct']);
```

**Access:**
- `http://yourdomain.com/api/produtos` → Returns all products as JSON
- `http://yourdomain.com/api/produtos/1` → Returns specific product as JSON

## Custom 404 Handler Examples

### Example 8: Custom 404 Page

```php
// In routes.php
$router->setNotFoundHandler(function() {
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="pt">
    <head>
        <meta charset="UTF-8">
        <title>Página Não Encontrada - Bar da Tomazia</title>
        <link rel="stylesheet" href="/css/style.css">
    </head>
    <body>
        <div class="container">
            <h1>404 - Página Não Encontrada</h1>
            <p>Desculpe, a página que procura não existe.</p>
            <p>Que tal explorar nosso <a href="/cardapio">cardápio</a>?</p>
            <p>Ou <a href="/">voltar para o início</a>?</p>
        </div>
    </body>
    </html>
    <?php
});
```

### Example 9: 404 with Logging

```php
// In routes.php
$router->setNotFoundHandler(function() {
    // Log the 404 error
    error_log("404 Not Found: " . $_SERVER['REQUEST_URI'] . " from " . $_SERVER['REMOTE_ADDR']);
    
    http_response_code(404);
    require __DIR__ . '/erro.php';
});
```

## Advanced Examples

### Example 10: Route with Authentication Check

```php
// In routes.php
$router->get('/admin/users', function($params) {
    // Check if user is logged in
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        header('Location: /login');
        exit;
    }
    
    // User is authenticated, show admin page
    require __DIR__ . '/admin_users.php';
});
```

### Example 11: Route with CSRF Protection

```php
// In routes.php
$router->post('/delete-product', function($params) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        http_response_code(403);
        echo 'Invalid CSRF token';
        exit;
    }
    
    // Process deletion
    require __DIR__ . '/delete_product.php';
});
```

### Example 12: Route with Content Negotiation

```php
// In routes.php
$router->get('/product/{id}', function($params) {
    $productId = intval($params['id']);
    
    // Load product data
    $db = getDbConnection();
    $stmt = $db->prepare('SELECT * FROM produtos WHERE id_produto = :id');
    $stmt->bindValue(':id', $productId, SQLITE3_INTEGER);
    $product = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    
    if (!$product) {
        http_response_code(404);
        echo 'Product not found';
        return;
    }
    
    // Check Accept header for content type
    $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
    
    if (strpos($acceptHeader, 'application/json') !== false) {
        // Return JSON
        header('Content-Type: application/json');
        echo json_encode($product);
    } else {
        // Return HTML
        header('Content-Type: text/html');
        echo '<h1>' . htmlspecialchars($product['nome_prod']) . '</h1>';
        echo '<p>Price: €' . number_format($product['preco'], 2) . '</p>';
    }
});
```

**Access:**
- `curl http://yourdomain.com/product/1` → Returns HTML
- `curl -H "Accept: application/json" http://yourdomain.com/product/1` → Returns JSON

## Testing Routes

### Example 13: Testing Routes with cURL

```bash
# Test GET route
curl http://localhost/login

# Test POST route
curl -X POST http://localhost/login \
  -d "username=admin&password=secret"

# Test dynamic route
curl http://localhost/produto/123

# Test 404 handling
curl http://localhost/nonexistent
```

### Example 14: Testing with PHP

```php
// test_routes.php
<?php
// Simulate GET request
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/produto/123';

require 'public/index.php';
```

## Route Organization Tips

### Example 15: Organizing Routes by Feature

```php
// In routes.php

// ============================================
// PUBLIC ROUTES
// ============================================

// Home
$router->get('/', function($params) {
    require __DIR__ . '/index.php';
});

// Authentication
$router->get('/login', function($params) {
    require __DIR__ . '/login.php';
});
$router->post('/login', function($params) {
    require __DIR__ . '/login.php';
});

// ============================================
// PRODUCT ROUTES
// ============================================

require_once __DIR__ . '/controllers/ProductController.php';
$productController = new ProductController();
$router->get('/produtos', [$productController, 'list']);
$router->get('/produto/{id}', [$productController, 'show']);

// ============================================
// ADMIN ROUTES
// ============================================

$router->get('/admin', function($params) {
    require __DIR__ . '/admin.php';
});
$router->post('/admin', function($params) {
    require __DIR__ . '/admin.php';
});

// ============================================
// API ROUTES
// ============================================

require_once __DIR__ . '/controllers/ApiController.php';
$apiController = new ApiController();
$router->get('/api/produtos', [$apiController, 'listProducts']);
$router->get('/api/produtos/{id}', [$apiController, 'getProduct']);
```

## Migration from Legacy URLs

### Example 16: Redirecting Old URLs

```php
// In routes.php - Add these at the top for backward compatibility

// Redirect old .php URLs to new clean URLs
$router->get('/login.php', function($params) {
    header('Location: /login', true, 301);
    exit;
});

$router->get('/admin.php', function($params) {
    header('Location: /admin', true, 301);
    exit;
});

$router->get('/cardapio.php', function($params) {
    header('Location: /cardapio', true, 301);
    exit;
});
```

## Conclusion

These examples demonstrate the flexibility and power of the routing system. You can:
- Define simple static routes
- Extract parameters from URLs
- Use controllers for better code organization
- Implement custom 404 handlers
- Add authentication and CSRF protection
- Support multiple content types
- Organize routes by feature

For more details, see the [ROUTING_SYSTEM.md](ROUTING_SYSTEM.md) documentation.
