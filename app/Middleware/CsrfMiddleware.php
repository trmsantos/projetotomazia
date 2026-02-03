<?php

namespace App\Middleware;

use App\Helpers\SecurityHelper;

/**
 * CsrfMiddleware - Verify CSRF token on POST requests
 */
class CsrfMiddleware implements Middleware
{
    public function handle(): bool
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tokenName = $_ENV['CSRF_TOKEN_NAME'] ?? 'csrf_token';
            $token = $_POST[$tokenName] ?? '';
            
            if (!SecurityHelper::verifyCsrfToken($token)) {
                http_response_code(403);
                die("Erro: Token CSRF inválido.");
            }
        }
        
        return true;
    }
}
