<?php

/**
 * Route Configuration
 * 
 * This file defines all application routes in a centralized location.
 * Routes are organized by functionality (public, auth, admin).
 * 
 * Route format: ['method', 'path', 'handler', 'name' (optional)]
 * 
 * @package App\Config
 */

return [
    /**
     * Public Routes
     * 
     * Routes accessible to all visitors without authentication.
     */
    'public' => [
        // Home and registration
        ['GET', '/', 'HomeController@index', 'home'],
        ['POST', '/register', 'CustomerController@register', 'register'],
        
        // Customer pages
        ['GET', '/bemvindo', 'CustomerController@welcome', 'welcome'],
        ['GET', '/cardapio', 'CustomerController@menu', 'menu'],
        ['GET', '/fotos', 'CustomerController@photos', 'photos'],
        ['GET', '/termos', 'CustomerController@terms', 'terms'],
    ],

    /**
     * Authentication Routes
     * 
     * Routes for login, logout, and session management.
     */
    'auth' => [
        ['GET', '/login', 'AuthController@showLogin', 'login'],
        ['POST', '/login', 'AuthController@login', 'login.post'],
        ['POST', '/logout', 'AuthController@logout', 'logout'],
    ],

    /**
     * Admin Routes
     * 
     * Routes requiring admin authentication.
     * All routes here are protected by AdminMiddleware.
     */
    'admin' => [
        // Dashboard
        ['GET', '/admin', 'AdminController@dashboard', 'admin.dashboard'],
        
        // Product management
        ['POST', '/admin/product', 'AdminController@saveProduct', 'admin.product.save'],
        ['POST', '/admin/product/delete', 'AdminController@deleteProduct', 'admin.product.delete'],
        
        // Event management
        ['POST', '/admin/event', 'AdminController@saveEvent', 'admin.event.save'],
        ['POST', '/admin/event/delete', 'AdminController@deleteEvent', 'admin.event.delete'],
        ['POST', '/admin/event/toggle', 'AdminController@toggleEvent', 'admin.event.toggle'],
        
        // SMS marketing
        ['POST', '/admin/sms', 'AdminController@sendSms', 'admin.sms'],
        
        // Photo moderation
        ['POST', '/admin/photo/moderate', 'AdminController@moderatePhoto', 'admin.photo.moderate'],
    ],

    /**
     * Legacy URL Mappings
     * 
     * Maps old .php URLs to new clean URLs for backward compatibility.
     * These are used by the 404 handler for redirects.
     */
    'legacy' => [
        '/index.php' => '/',
        '/login.php' => '/login',
        '/bemvindo.php' => '/bemvindo',
        '/cardapio.php' => '/cardapio',
        '/fotos.php' => '/fotos',
        '/termos.php' => '/termos',
        '/admin.php' => '/admin',
        '/form.php' => '/register',
    ],
];
