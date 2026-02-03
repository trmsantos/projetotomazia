<?php

define('DB_PATH', __DIR__ . '/bd/bd_teste.db');

define('WIFI_REDE', 'NOS-2B6E-5');
define('WIFI_PASSWORD', '5YV4UJC4');

define('CSRF_TOKEN_NAME', 'csrf_token');

// SMS API Configuration
// Para usar Twilio, configure estas variáveis de ambiente ou descomente e preencha:
// define('SMS_API_ENABLED', getenv('SMS_API_ENABLED') ?: false);
// define('SMS_API_KEY', getenv('SMS_API_KEY') ?: '');
// define('SMS_API_SECRET', getenv('SMS_API_SECRET') ?: '');
// define('SMS_API_FROM', getenv('SMS_API_FROM') ?: '');
// define('SMS_API_ENDPOINT', getenv('SMS_API_ENDPOINT') ?: 'https://api.example.com/sms');

// Para teste, deixar SMS_API_ENABLED como false para modo de simulação
define('SMS_API_ENABLED', false);
define('SMS_API_KEY', '');
define('SMS_API_SECRET', '');
define('SMS_API_FROM', '');
define('SMS_API_ENDPOINT', 'https://api.example.com/sms');
define('SMS_API_COUNTRY_CODE', '+351'); // Código do país para Portugal
define('SMS_API_TIMEOUT', 30); // Timeout em segundos para requisições HTTP

/**
 * Função para obter conexão com a base de dados
 * @return SQLite3
 * @throws Exception
 */
function getDbConnection() {
    try {
        $db = new SQLite3(DB_PATH);
        $db->enableExceptions(true);
        return $db;
    } catch (Exception $e) {
        error_log("Database connection error: " . $e->getMessage());
        throw new Exception("Erro ao conectar à base de dados.");
    }
}

/**
 * Gerar token CSRF
 * @return string
 */
function generateCsrfToken() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Verificar token CSRF
 * @param string $token
 * @return bool
 */
function verifyCsrfToken($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Configurar cookies seguros
 * @param string $name
 * @param string $value
 * @param int $expire
 */
function setSecureCookie($name, $value, $expire = 0) {
    $options = [
        'expires' => $expire,
        'path' => '/',
        'secure' => true,      
        'httponly' => true,    
        'samesite' => 'Strict' 
    ];
    setcookie($name, $value, $options);
}

/**
 * Enviar SMS via API
 * @param array $telefones Array de números de telefone
 * @param string $mensagem Mensagem a enviar
 * @return array Array com 'success' (bool), 'sent_count' (int), 'failed_count' (int), 'errors' (array)
 */
function sendSmsViaApi($telefones, $mensagem) {
    $result = [
        'success' => true,
        'sent_count' => 0,
        'failed_count' => 0,
        'errors' => []
    ];
    
    // Se API não estiver habilitada, retornar modo de simulação
    if (!SMS_API_ENABLED) {
        $result['sent_count'] = count($telefones);
        $result['simulation'] = true;
        error_log("SMS API em modo de simulação. " . count($telefones) . " números processados.");
        return $result;
    }
    
    // Validar configuração da API
    if (empty(SMS_API_KEY) || empty(SMS_API_ENDPOINT)) {
        $result['success'] = false;
        $result['errors'][] = "Configuração da API de SMS incompleta.";
        error_log("SMS API configuration incomplete");
        return $result;
    }
    
    // Enviar SMS para cada número
    foreach ($telefones as $telefone) {
        try {
            // Preparar dados para API
            $postData = [
                'to' => SMS_API_COUNTRY_CODE . $telefone, // Usar código do país configurável
                'from' => SMS_API_FROM,
                'message' => $mensagem
            ];
            
            // Configurar requisição HTTP
            $ch = curl_init(SMS_API_ENDPOINT);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . SMS_API_KEY
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, SMS_API_TIMEOUT); // Usar timeout configurável
            
            // Executar requisição
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if (curl_errno($ch)) {
                throw new Exception(curl_error($ch));
            }
            
            curl_close($ch);
            
            // Verificar resposta
            if ($httpCode >= 200 && $httpCode < 300) {
                $result['sent_count']++;
                error_log("SMS enviado com sucesso para {$telefone}");
            } else {
                throw new Exception("HTTP {$httpCode}: {$response}");
            }
            
        } catch (Exception $e) {
            $result['failed_count']++;
            $result['errors'][] = "Erro ao enviar para {$telefone}: " . $e->getMessage();
            error_log("Erro ao enviar SMS para {$telefone}: " . $e->getMessage());
        }
    }
    
    // Se todos falharam, marcar como falha geral
    if ($result['failed_count'] > 0 && $result['sent_count'] === 0) {
        $result['success'] = false;
    }
    
    return $result;
}
?>
