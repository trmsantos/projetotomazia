<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Customer;
use App\Helpers\SecurityHelper;

/**
 * HomeController - Handles home page and registration
 */
class HomeController extends BaseController
{
    /**
     * Show home page
     */
    public function index(array $params = []): void
    {
        // Check if user already registered
        if (isset($_COOKIE['user_id'])) {
            $customer = new Customer();
            $userId = $_COOKIE['user_id'];
            
            $result = $customer->findByUserId($userId);
            
            if ($result) {
                $_SESSION['nome'] = SecurityHelper::escape($result['nome']);
                $this->redirect('/bemvindo');
                return;
            }
        } else {
            // Generate new user ID
            $userId = bin2hex(random_bytes(16));
            SecurityHelper::setSecureCookie('user_id', $userId, time() + (10 * 365 * 24 * 60 * 60));
        }
        
        // Show registration form
        $this->view('index.php');
    }
}
