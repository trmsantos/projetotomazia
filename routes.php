<?php
/**
 * Route Definitions
 * 
 * Define all application routes here.
 * Routes are registered on the $router instance passed from public/index.php
 * 
 * Supported patterns:
 * - Static routes: '/login', '/admin', '/cardapio'
 * - Dynamic routes: '/produto/{id}', '/user/{slug}'
 * 
 * The router automatically extracts parameters from the URL and passes them
 * to the handler function.
 */

// Home page route
$router->get('/', function($params) {
    require __DIR__ . '/index.php';
});

// Login page route
$router->get('/login', function($params) {
    require __DIR__ . '/login.php';
});

// Handle login form submission
$router->post('/login', function($params) {
    require __DIR__ . '/login.php';
});

// Admin panel route
$router->get('/admin', function($params) {
    require __DIR__ . '/admin.php';
});

// Handle admin form submissions
$router->post('/admin', function($params) {
    require __DIR__ . '/admin.php';
});

// Welcome page route
$router->get('/bemvindo', function($params) {
    require __DIR__ . '/bemvindo.php';
});

// Menu/cardapio page route
$router->get('/cardapio', function($params) {
    require __DIR__ . '/cardapio.php';
});

// Photos gallery route
$router->get('/fotos', function($params) {
    require __DIR__ . '/fotos.php';
});

// Handle photo upload/management
$router->post('/fotos', function($params) {
    require __DIR__ . '/fotos.php';
});

// Terms page route
$router->get('/termos', function($params) {
    require __DIR__ . '/termos.php';
});

// Registration form route
$router->get('/register', function($params) {
    require __DIR__ . '/register.php';
});

// Handle registration form submission
$router->post('/register', function($params) {
    require __DIR__ . '/register.php';
});

// Form page route
$router->get('/form', function($params) {
    require __DIR__ . '/form.php';
});

// Handle form submission
$router->post('/form', function($params) {
    require __DIR__ . '/form.php';
});

// Error page route
$router->get('/erro', function($params) {
    require __DIR__ . '/erro.php';
});

// Example of dynamic route with parameter extraction using a controller
// This demonstrates how to use controllers with the routing system
require_once __DIR__ . '/controllers/ProductController.php';
$productController = new ProductController();
$router->get('/produto/{id}', [$productController, 'show']);

// Example of admin dashboard with parameter
$router->get('/admin/dashboard', function($params) {
    // This shows how nested static routes work
    ?>
    <!DOCTYPE html>
    <html lang="pt">
    <head>
        <meta charset="UTF-8">
        <title>Admin Dashboard - Bar da Tomazia</title>
    </head>
    <body style="background-color: #5D1F3A; color: #f0f0f0; padding: 20px; font-family: Arial, sans-serif;">
        <h1 style="color: #D4AF37;">Admin Dashboard</h1>
        <p>Esta é uma rota estática aninhada de exemplo.</p>
        <p><a href="/admin" style="color: #D4AF37;">Ir para Admin</a></p>
    </body>
    </html>
    <?php
});

// Set custom 404 handler (optional)
$router->setNotFoundHandler(function() {
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="pt">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>404 - Página Não Encontrada</title>
        <link rel="stylesheet" href="/css/style.css">
        <style>
            body { background-color: #5D1F3A; color: #f0f0f0; font-family: Arial, sans-serif; text-align: center; padding: 50px; }
            h1 { font-size: 72px; margin: 0; color: #D4AF37; }
            p { font-size: 20px; }
            a { color: #D4AF37; text-decoration: none; }
            a:hover { text-decoration: underline; }
        </style>
    </head>
    <body>
        <h1>404</h1>
        <p>Desculpe, a página que procura não foi encontrada.</p>
        <a href="/">Voltar para a página inicial</a>
    </body>
    </html>
    <?php
});
