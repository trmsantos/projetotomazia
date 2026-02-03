<?php

namespace App\Middleware;

/**
 * AdminMiddleware - Check if user is admin
 */
class AdminMiddleware implements Middleware
{
    public function handle(): bool
    {
        if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
            header('Location: /login');
            exit;
        }
        
        if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
            http_response_code(403);
            echo "403 - Acesso Negado";
            exit;
        }
        
        return true;
    }
}
