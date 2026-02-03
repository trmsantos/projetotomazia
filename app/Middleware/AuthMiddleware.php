<?php

namespace App\Middleware;

use App\Helpers\SecurityHelper;

/**
 * AuthMiddleware - Check if user is authenticated
 */
class AuthMiddleware implements Middleware
{
    public function handle(): bool
    {
        if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
            header('Location: /login');
            exit;
        }
        
        return true;
    }
}
