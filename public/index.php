<?php

/**
 * Front Controller - Entry point for all requests
 */

// Load composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Core\ExceptionHandler;
use App\Helpers\SecurityHelper;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Initialize secure session
SecurityHelper::initSecureSession();

// Set up exception handler
$debug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';
$exceptionHandler = new ExceptionHandler($debug);
$exceptionHandler->register();

// Create router
$router = new Router();

// Define routes
// Home route
$router->get('/', 'HomeController@index', 'home');

// Authentication routes
$router->get('/login', 'AuthController@showLogin', 'login');
$router->post('/login', 'AuthController@login');
$router->post('/logout', 'AuthController@logout', 'logout');

// Customer routes
$router->get('/bemvindo', 'CustomerController@welcome', 'welcome');
$router->get('/cardapio', 'CustomerController@menu', 'menu');
$router->get('/fotos', 'CustomerController@photos', 'photos');
$router->get('/termos', 'CustomerController@terms', 'terms');
$router->post('/register', 'CustomerController@register', 'register');

// Admin routes (protected)
$router->get('/admin', 'AdminController@dashboard', 'admin.dashboard');
$router->post('/admin/product', 'AdminController@saveProduct', 'admin.product.save');
$router->post('/admin/product/delete', 'AdminController@deleteProduct', 'admin.product.delete');
$router->post('/admin/event', 'AdminController@saveEvent', 'admin.event.save');
$router->post('/admin/event/delete', 'AdminController@deleteEvent', 'admin.event.delete');
$router->post('/admin/event/toggle', 'AdminController@toggleEvent', 'admin.event.toggle');
$router->post('/admin/sms', 'AdminController@sendSms', 'admin.sms');
$router->post('/admin/photo/moderate', 'AdminController@moderatePhoto', 'admin.photo.moderate');

// Set 404 handler
$router->setNotFoundHandler(function() {
    // Try to find legacy PHP file
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
    // Map old URLs to new routes (backward compatibility)
    $legacyMap = [
        '/index.php' => '/',
        '/login.php' => '/login',
        '/bemvindo.php' => '/bemvindo',
        '/cardapio.php' => '/cardapio',
        '/fotos.php' => '/fotos',
        '/termos.php' => '/termos',
        '/admin.php' => '/admin',
        '/form.php' => '/register'
    ];
    
    // Check if it's a legacy URL
    if (isset($legacyMap[$uri])) {
        header('Location: ' . $legacyMap[$uri], true, 301);
        exit;
    }
    
    // Check if legacy file exists
    $legacyFile = __DIR__ . '/..' . $uri;
    if (file_exists($legacyFile) && is_file($legacyFile) && pathinfo($legacyFile, PATHINFO_EXTENSION) === 'php') {
        require $legacyFile;
        exit;
    }
    
    http_response_code(404);
    require __DIR__ . '/../erro.php';
});

// Dispatch the request
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

$router->dispatch($method, $uri);
