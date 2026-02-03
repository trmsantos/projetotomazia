<?php
/**
 * ProductController
 * 
 * Example controller demonstrating how to integrate controllers with the routing system.
 * Controllers provide a clean way to organize application logic separate from route definitions.
 * 
 * Usage in routes.php:
 *   require_once __DIR__ . '/controllers/ProductController.php';
 *   $productController = new ProductController();
 *   $router->get('/produto/{id}', [$productController, 'show']);
 */
class ProductController {
    
    /**
     * @var SQLite3|null Database connection
     */
    private $db = null;
    
    /**
     * Initialize the controller
     */
    public function __construct() {
        try {
            $this->db = getDbConnection();
        } catch (Exception $e) {
            error_log("ProductController: Failed to initialize database connection - " . $e->getMessage());
        }
    }
    
    /**
     * Display a product by ID
     * 
     * @param array $params Route parameters containing 'id'
     * @return void
     */
    public function show($params) {
        $productId = isset($params['id']) ? intval($params['id']) : 0;
        
        if ($productId <= 0) {
            $this->showError('ID de produto inválido');
            return;
        }
        
        // Fetch product from database
        $product = $this->getProductById($productId);
        
        if (!$product) {
            $this->showError('Produto não encontrado');
            return;
        }
        
        // Display product page
        $this->renderProductPage($product);
    }
    
    /**
     * Get product from database by ID
     * 
     * @param int $productId Product ID
     * @return array|null Product data or null if not found
     */
    private function getProductById($productId) {
        if (!$this->db) {
            return null;
        }
        
        try {
            $stmt = $this->db->prepare('SELECT * FROM produtos WHERE id_produto = :id');
            $stmt->bindValue(':id', $productId, SQLITE3_INTEGER);
            $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
            
            return $result ?: null;
        } catch (Exception $e) {
            error_log("ProductController: Error fetching product - " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Render the product page
     * 
     * @param array $product Product data
     * @return void
     */
    private function renderProductPage($product) {
        $nome = htmlspecialchars($product['nome_prod'] ?? 'Produto');
        $preco = number_format($product['preco'] ?? 0, 2, ',', '.');
        $tipo = htmlspecialchars($product['tipo'] ?? 'Geral');
        
        ?>
        <!DOCTYPE html>
        <html lang="pt">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo $nome; ?> - Bar da Tomazia</title>
            <link rel="icon" href="/img/tomazia.png" type="image/png">
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
            <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="/css/style.css">
            <style>
                body {
                    background-color: #5D1F3A;
                    color: #f0f0f0;
                    font-family: 'Montserrat', Arial, sans-serif;
                    min-height: 100vh;
                    padding: 20px;
                }
                .product-container {
                    max-width: 800px;
                    margin: 50px auto;
                    background: rgba(61, 15, 36, 0.9);
                    padding: 40px;
                    border-radius: 15px;
                    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
                    border: 1px solid rgba(212, 175, 55, 0.3);
                }
                h1 {
                    color: #D4AF37;
                    font-family: 'Playfair Display', serif;
                    margin-bottom: 30px;
                }
                .product-detail {
                    margin: 20px 0;
                }
                .label {
                    color: #D4AF37;
                    font-weight: 600;
                }
                .back-link {
                    display: inline-block;
                    margin-top: 30px;
                    color: #D4AF37;
                    text-decoration: none;
                }
                .back-link:hover {
                    text-decoration: underline;
                }
            </style>
        </head>
        <body>
            <div class="product-container">
                <h1><?php echo $nome; ?></h1>
                
                <div class="product-detail">
                    <span class="label">Preço:</span> 
                    <span>€<?php echo $preco; ?></span>
                </div>
                
                <div class="product-detail">
                    <span class="label">Tipo:</span> 
                    <span><?php echo $tipo; ?></span>
                </div>
                
                <div class="product-detail">
                    <p>Esta é uma demonstração de como usar controllers com o sistema de rotas.</p>
                    <p>O produto foi carregado dinamicamente da base de dados usando o ID da URL.</p>
                </div>
                
                <a href="/cardapio" class="back-link">← Voltar para o cardápio</a>
                <a href="/" class="back-link">← Voltar para o início</a>
            </div>
        </body>
        </html>
        <?php
    }
    
    /**
     * Display error message
     * 
     * @param string $message Error message to display
     * @return void
     */
    private function showError($message) {
        http_response_code(404);
        ?>
        <!DOCTYPE html>
        <html lang="pt">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Erro - Bar da Tomazia</title>
            <link rel="stylesheet" href="/css/style.css">
            <style>
                body {
                    background-color: #5D1F3A;
                    color: #f0f0f0;
                    font-family: Arial, sans-serif;
                    text-align: center;
                    padding: 50px;
                }
                h1 {
                    font-size: 48px;
                    color: #D4AF37;
                }
                p {
                    font-size: 20px;
                }
                a {
                    color: #D4AF37;
                    text-decoration: none;
                }
                a:hover {
                    text-decoration: underline;
                }
            </style>
        </head>
        <body>
            <h1>Erro</h1>
            <p><?php echo htmlspecialchars($message); ?></p>
            <a href="/">Voltar para a página inicial</a>
        </body>
        </html>
        <?php
    }
}
