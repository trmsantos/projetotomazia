<?php

define('DB_PATH', __DIR__ . '/bd/bd_teste.db');

define('WIFI_REDE', 'NOS-2B6E-5');
define('WIFI_PASSWORD', '5YV4UJC4');

define('CSRF_TOKEN_NAME', 'csrf_token');

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
?>
