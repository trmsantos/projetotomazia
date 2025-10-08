<?php
session_start();
require_once 'config.php';

try {
    $db = getDbConnection();

    if (!isset($_COOKIE['user_id'])) {
        $userId = bin2hex(random_bytes(16)); 
        setSecureCookie('user_id', $userId, time() + (10 * 365 * 24 * 60 * 60));
    } else {
        $userId = $_COOKIE['user_id']; 
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Verificar token CSRF
        if (!isset($_POST[CSRF_TOKEN_NAME]) || !verifyCsrfToken($_POST[CSRF_TOKEN_NAME])) {
            die("Erro: Token CSRF inválido.");
        }

        // Verificar se os termos e condições foram aceitos
        if (!isset($_POST['termos']) || $_POST['termos'] !== 'on') {
            die("Erro: Você deve aceitar os Termos e Condições para continuar.");
        }

        $nome = htmlspecialchars($_POST['nome']);
        $email = htmlspecialchars($_POST['email']);
        $telefone = htmlspecialchars($_POST['telefone']);
        $data_registro = date('Y-m-d H:i:s');

        $query = $db->prepare('SELECT COUNT(*) as count FROM tomazia_clientes WHERE user_id = :user_id');
        $query->bindValue(':user_id', $userId, SQLITE3_TEXT);
        $result = $query->execute()->fetchArray(SQLITE3_ASSOC);

        if ($result['count'] > 0) {
            echo "Dados já foram introduzidos para este dispositivo!";
        } else {
            $query = $db->prepare('INSERT INTO tomazia_clientes (user_id, nome, email, telemovel, data_registro) VALUES (:user_id, :nome, :email, :telefone, :data_registro)');
            $query->bindValue(':user_id', $userId, SQLITE3_TEXT);
            $query->bindValue(':nome', $nome, SQLITE3_TEXT);
            $query->bindValue(':email', $email, SQLITE3_TEXT);
            $query->bindValue(':telefone', $telefone, SQLITE3_TEXT);
            $query->bindValue(':data_registro', $data_registro, SQLITE3_TEXT);
            $query->execute();

            $_SESSION['nome'] = $nome; // Armazena o nome na sessão
            header("location: bemvindo.php"); // Redireciona para a página de boas-vindas
            exit;
        }
    }
} catch (Exception $e) {
    error_log("Error in form.php: " . $e->getMessage());
    echo "Erro: Ocorreu um problema. Por favor, tente novamente.";
}
?>