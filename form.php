<?php
session_start();

try {
    $db = new SQLite3(__DIR__ . '/bd/bd_teste.db');

    if (!isset($_COOKIE['user_id'])) {
        $userId = bin2hex(random_bytes(16)); 
        setcookie('user_id', $userId, time() + (10 * 365 * 24 * 60 * 60)); 
    } else {
        $userId = $_COOKIE['user_id']; 
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $telefone = $_POST['telefone'];
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
    echo "Erro: " . $e->getMessage();
}
?>