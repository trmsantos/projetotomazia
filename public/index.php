<?php
/**
 * Front Controller
 * 
 * This is the entry point for all requests to the application.
 * All URLs are routed through this file via .htaccess rewrite rules.
 * 
 * The router handles:
 * - Static routes: /login, /admin, /cardapio
 * - Dynamic routes: /produto/{id}
 * - Fallback to 404 for undefined routes
 */

// Start session for all requests
session_start();

// Include the configuration file
require_once __DIR__ . '/../config.php';

// Include the Router class
require_once __DIR__ . '/../Router.php';

// Create router instance
$router = new Router();

// Load route definitions
require_once __DIR__ . '/../routes.php';

// Dispatch the request
$router->dispatch();
