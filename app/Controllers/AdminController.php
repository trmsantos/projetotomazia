<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Product;
use App\Models\Event;
use App\Models\Customer;
use App\Helpers\SecurityHelper;
use App\Helpers\ValidationHelper;
use App\Helpers\Logger;

/**
 * AdminController - Handles admin operations
 */
class AdminController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        
        // Check authentication
        if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
            header('Location: /login');
            exit;
        }
    }

    /**
     * Show admin dashboard
     */
    public function dashboard(array $params = []): void
    {
        $this->view('admin.php');
    }

    /**
     * Save product (create or update)
     */
    public function saveProduct(array $params = []): void
    {
        $this->verifyCsrf();

        $nomeProd = ValidationHelper::sanitizeForDb($_POST['nome_prod'] ?? '');
        $preco = floatval($_POST['preco'] ?? 0);
        $tipo = ValidationHelper::sanitizeForDb($_POST['tipo'] ?? '');
        $idProduto = $_POST['id_produto'] ?? null;

        if (empty($nomeProd) || $preco < 0 || empty($tipo)) {
            die("Erro: Dados inválidos.");
        }

        try {
            $product = new Product();
            $data = [
                'nome_prod' => $nomeProd,
                'preco' => $preco,
                'tipo' => $tipo
            ];

            if (!empty($idProduto)) {
                $product->update($idProduto, $data);
                Logger::info("Product updated", ['product_id' => $idProduto]);
            } else {
                $newId = $product->insert($data);
                Logger::info("Product created", ['product_id' => $newId]);
            }

            $this->redirect('/admin#produtos');
        } catch (\Exception $e) {
            Logger::error("Product save error", ['error' => $e->getMessage()]);
            die("Erro ao salvar produto.");
        }
    }

    /**
     * Delete product
     */
    public function deleteProduct(array $params = []): void
    {
        $this->verifyCsrf();

        $idProduto = $_POST['id_produto'] ?? null;

        if (empty($idProduto)) {
            die("Erro: ID do produto não fornecido.");
        }

        try {
            $product = new Product();
            $product->delete($idProduto);
            
            Logger::info("Product deleted", ['product_id' => $idProduto]);
            
            $this->redirect('/admin#produtos');
        } catch (\Exception $e) {
            Logger::error("Product delete error", ['error' => $e->getMessage()]);
            die("Erro ao deletar produto.");
        }
    }

    /**
     * Save event (create or update)
     */
    public function saveEvent(array $params = []): void
    {
        $this->verifyCsrf();

        $nomeEvento = ValidationHelper::sanitizeForDb($_POST['nome_evento'] ?? '');
        $dataEvento = ValidationHelper::sanitizeForDb($_POST['data_evento'] ?? '');
        $descricao = ValidationHelper::sanitizeForDb($_POST['descricao'] ?? '');
        $visivel = isset($_POST['visivel']) ? 1 : 0;
        $idEvento = $_POST['id_evento'] ?? null;

        if (empty($nomeEvento)) {
            die("Erro: Nome do evento é obrigatório.");
        }

        try {
            $event = new Event();
            $data = [
                'nome_evento' => $nomeEvento,
                'data_evento' => $dataEvento,
                'descricao' => $descricao,
                'visivel' => $visivel
            ];

            if (!empty($idEvento)) {
                $event->update($idEvento, $data);
                Logger::info("Event updated", ['event_id' => $idEvento]);
            } else {
                $newId = $event->insert($data);
                Logger::info("Event created", ['event_id' => $newId]);
            }

            $this->redirect('/admin#eventos');
        } catch (\Exception $e) {
            Logger::error("Event save error", ['error' => $e->getMessage()]);
            die("Erro ao salvar evento.");
        }
    }

    /**
     * Delete event
     */
    public function deleteEvent(array $params = []): void
    {
        $this->verifyCsrf();

        $idEvento = $_POST['id_evento'] ?? null;

        if (empty($idEvento)) {
            die("Erro: ID do evento não fornecido.");
        }

        try {
            $event = new Event();
            $event->delete($idEvento);
            
            Logger::info("Event deleted", ['event_id' => $idEvento]);
            
            $this->redirect('/admin#eventos');
        } catch (\Exception $e) {
            Logger::error("Event delete error", ['error' => $e->getMessage()]);
            die("Erro ao deletar evento.");
        }
    }

    /**
     * Toggle event visibility
     */
    public function toggleEvent(array $params = []): void
    {
        $this->verifyCsrf();

        $idEvento = $_POST['id_evento'] ?? null;

        if (empty($idEvento)) {
            die("Erro: ID do evento não fornecido.");
        }

        try {
            $event = new Event();
            $event->toggleVisibility($idEvento);
            
            Logger::info("Event visibility toggled", ['event_id' => $idEvento]);
            
            $this->redirect('/admin#eventos');
        } catch (\Exception $e) {
            Logger::error("Event toggle error", ['error' => $e->getMessage()]);
            die("Erro ao alterar visibilidade do evento.");
        }
    }

    /**
     * Send SMS to customers
     */
    public function sendSms(array $params = []): void
    {
        $this->verifyCsrf();

        $mensagem = ValidationHelper::sanitizeForDb($_POST['mensagem'] ?? '');

        if (empty($mensagem)) {
            die("Erro: Mensagem é obrigatória.");
        }

        if (strlen($mensagem) < 10 || strlen($mensagem) > 160) {
            die("Erro: A mensagem deve ter entre 10 e 160 caracteres.");
        }

        try {
            $customer = new Customer();
            $customers = $customer->getAllWithPhones();
            
            $telefones = array_map(fn($c) => $c['telemovel'], $customers);

            // Use legacy SMS function
            require_once __DIR__ . '/../../config.php';
            $result = sendSmsViaApi($telefones, $mensagem);

            if ($result['success']) {
                Logger::info("SMS sent", [
                    'recipients' => count($telefones),
                    'sent' => $result['sent_count'],
                    'failed' => $result['failed_count']
                ]);
                
                $_SESSION['sms_result'] = $result;
            } else {
                Logger::error("SMS send failed", ['errors' => $result['errors']]);
                $_SESSION['sms_error'] = $result['errors'];
            }

            $this->redirect('/admin#sms');
        } catch (\Exception $e) {
            Logger::error("SMS send error", ['error' => $e->getMessage()]);
            die("Erro ao enviar SMS.");
        }
    }

    /**
     * Moderate photo
     */
    public function moderatePhoto(array $params = []): void
    {
        $this->verifyCsrf();

        $photoId = $_POST['photo_id'] ?? null;
        $action = $_POST['action'] ?? null;

        if (empty($photoId) || empty($action)) {
            die("Erro: Dados inválidos.");
        }

        try {
            $sql = "UPDATE photos SET status = :status WHERE id = :id";
            $status = ($action === 'approve') ? 'approved' : 'rejected';
            
            $this->db->execute($sql, [
                'status' => $status,
                'id' => $photoId
            ]);

            Logger::info("Photo moderated", ['photo_id' => $photoId, 'action' => $action]);
            
            $this->redirect('/admin#fotos');
        } catch (\Exception $e) {
            Logger::error("Photo moderation error", ['error' => $e->getMessage()]);
            die("Erro ao moderar foto.");
        }
    }

    /**
     * Verify CSRF token
     */
    private function verifyCsrf(): void
    {
        $tokenName = $_ENV['CSRF_TOKEN_NAME'] ?? 'csrf_token';
        if (!isset($_POST[$tokenName]) || !SecurityHelper::verifyCsrfToken($_POST[$tokenName])) {
            die("Erro: Token CSRF inválido.");
        }
    }
}
