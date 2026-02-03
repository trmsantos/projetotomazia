<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Customer;
use App\Models\Event;
use App\Models\Product;
use App\Helpers\SecurityHelper;
use App\Helpers\ValidationHelper;
use App\Helpers\Logger;

/**
 * CustomerController - Handles customer-facing pages
 */
class CustomerController extends BaseController
{
    /**
     * Show welcome page
     */
    public function welcome(array $params = []): void
    {
        $this->view('bemvindo.php');
    }

    /**
     * Show menu page
     */
    public function menu(array $params = []): void
    {
        $this->view('cardapio.php');
    }

    /**
     * Show photos page
     */
    public function photos(array $params = []): void
    {
        $this->view('fotos.php');
    }

    /**
     * Show terms page
     */
    public function terms(array $params = []): void
    {
        $this->view('termos.php');
    }

    /**
     * Handle customer registration
     */
    public function register(array $params = []): void
    {
        // Verify CSRF token
        $tokenName = $_ENV['CSRF_TOKEN_NAME'] ?? 'csrf_token';
        if (!isset($_POST[$tokenName]) || !SecurityHelper::verifyCsrfToken($_POST[$tokenName])) {
            die("Erro: Token CSRF inválido.");
        }

        // Check terms acceptance
        if (!isset($_POST['termos']) || $_POST['termos'] !== 'on') {
            die("Erro: Você deve aceitar os Termos e Condições para continuar.");
        }

        // Validate input
        $validator = ValidationHelper::validate($_POST, [
            'nome' => ['required', 'minLength:3', 'pattern:/^[a-zA-ZÀ-ÿ\s]+$/u'],
            'email' => ['required', 'email'],
            'telefone' => ['required', 'phone']
        ]);

        if ($validator->fails()) {
            $errors = [];
            foreach ($validator->errors() as $field => $fieldErrors) {
                $errors = array_merge($errors, $fieldErrors);
            }
            die("Erros de validação:<br><br>" . implode("<br>", $errors));
        }

        // Get or create user ID
        if (!isset($_COOKIE['user_id'])) {
            $userId = bin2hex(random_bytes(16));
            SecurityHelper::setSecureCookie('user_id', $userId, time() + (10 * 365 * 24 * 60 * 60));
        } else {
            $userId = $_COOKIE['user_id'];
        }

        // Sanitize data
        $data = [
            'nome' => ValidationHelper::sanitizeForDb($_POST['nome']),
            'email' => ValidationHelper::sanitizeForDb($_POST['email']),
            'telemovel' => ValidationHelper::sanitizeForDb($_POST['telefone'])
        ];

        try {
            $customer = new Customer();
            $customerId = $customer->createOrUpdate($userId, $data);

            $_SESSION['nome'] = $data['nome'];
            
            if (isset($_SESSION['updated'])) {
                Logger::info("Customer updated", ['customer_id' => $customerId]);
            } else {
                Logger::info("Customer registered", ['customer_id' => $customerId]);
            }

            $this->redirect('/bemvindo');
        } catch (\Exception $e) {
            Logger::error("Customer registration error", ['error' => $e->getMessage()]);
            die("Erro: Ocorreu um problema. Por favor, tente novamente.");
        }
    }
}
