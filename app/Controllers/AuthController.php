<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\AdminUser;
use App\Helpers\SecurityHelper;
use App\Helpers\ValidationHelper;
use App\Helpers\Logger;

/**
 * AuthController - Handles authentication
 */
class AuthController extends BaseController
{
    /**
     * Show login form
     */
    public function showLogin(array $params = []): void
    {
        // Redirect if already logged in
        if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
            $this->redirect('/admin');
            return;
        }
        
        $this->view('login.php');
    }

    /**
     * Process login
     */
    public function login(array $params = []): void
    {
        // Verify CSRF token
        $tokenName = $_ENV['CSRF_TOKEN_NAME'] ?? 'csrf_token';
        if (!isset($_POST[$tokenName]) || !SecurityHelper::verifyCsrfToken($_POST[$tokenName])) {
            die("Erro: Token CSRF inválido.");
        }

        $username = ValidationHelper::sanitizeForDb($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // Check rate limiting
        $rateLimitKey = 'login_' . $username;
        if (!SecurityHelper::checkRateLimit($rateLimitKey, 5, 300)) {
            Logger::warning("Rate limit exceeded for login", ['username' => $username]);
            $error = "Muitas tentativas de login. Por favor, tente novamente em 5 minutos.";
            $this->view('login.php', ['error' => $error]);
            return;
        }

        // Validate credentials
        $adminModel = new AdminUser();
        $admin = $adminModel->verifyCredentials($username, $password);

        if ($admin) {
            // Successful login
            SecurityHelper::regenerateSession();
            SecurityHelper::resetRateLimit($rateLimitKey);
            
            $_SESSION['loggedin'] = true;
            $_SESSION['is_admin'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            
            Logger::info("Admin login successful", ['username' => $username]);
            
            $this->redirect('/admin');
        } else {
            // Failed login
            Logger::warning("Failed login attempt", ['username' => $username]);
            $error = "Credenciais inválidas!";
            $this->view('login.php', ['error' => $error]);
        }
    }

    /**
     * Process logout
     */
    public function logout(array $params = []): void
    {
        // Verify CSRF token
        $tokenName = $_ENV['CSRF_TOKEN_NAME'] ?? 'csrf_token';
        if (!isset($_POST[$tokenName]) || !SecurityHelper::verifyCsrfToken($_POST[$tokenName])) {
            die("Erro: Token CSRF inválido.");
        }

        $username = $_SESSION['admin_username'] ?? 'unknown';
        
        session_destroy();
        
        Logger::info("Admin logout", ['username' => $username]);
        
        $this->redirect('/login');
    }
}
