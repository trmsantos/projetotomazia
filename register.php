<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['terms'])) {
        die("Erro: É necessário aceitar os Termos e Condições para se registar.");
    }

    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        die("Utilizador e password são obrigatórios.");
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        $db = getDbConnection();
        
        $stmt = $db->prepare('SELECT id FROM users WHERE username = :username');
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $result = $stmt->execute();

        if ($result->fetchArray()) {
            die("Erro: Este nome de utilizador já existe.");
        }

        $stmt = $db->prepare('INSERT INTO users (username, password) VALUES (:username, :password)');
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $stmt->bindValue(':password', $hashed_password, SQLITE3_TEXT);
        
        if ($stmt->execute()) {
            header("Location: index.php?registration=success#login");
            exit();
        } else {
            die("Erro ao registar o utilizador.");
        }

    } catch (Exception $e) {
        die("Erro na base de dados: " . $e->getMessage());
    }
}
?>