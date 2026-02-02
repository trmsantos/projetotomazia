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

        // Validação robusta dos dados
        $errors = [];
        
        // Validar Nome
        $nome = trim($_POST['nome'] ?? '');
        if (strlen($nome) < 3) {
            $errors[] = "O nome deve ter pelo menos 3 caracteres.";
        } elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s]+$/u', $nome)) {
            $errors[] = "O nome deve conter apenas letras e espaços.";
        }
        
        // Validar Email
        $email = trim($_POST['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Por favor, insira um endereço de email válido.";
        }
        
        // Validar Telefone (formato português: 9 dígitos, começando por 9 - números móveis)
        $telefone = trim($_POST['telefone'] ?? '');
        if (!preg_match('/^9\d{8}$/', $telefone)) {
            $errors[] = "Por favor, insira um número de telemóvel português válido (9 dígitos, começando por 9).";
        }
        
        // Se houver erros, mostrar mensagem
        if (!empty($errors)) {
            die("Erros de validação:<br><br>" . implode("<br>", $errors));
        }
        
        // Sanitizar dados
        $nome = htmlspecialchars($nome);
        $email = htmlspecialchars($email);
        $telefone = htmlspecialchars($telefone);
        $data_registro = date('Y-m-d H:i:s');

        // Verificar se já existe registro com este email ou telefone
        $query = $db->prepare('SELECT id, user_id FROM tomazia_clientes WHERE email = :email OR telemovel = :telefone LIMIT 1');
        $query->bindValue(':email', $email, SQLITE3_TEXT);
        $query->bindValue(':telefone', $telefone, SQLITE3_TEXT);
        $result = $query->execute()->fetchArray(SQLITE3_ASSOC);

        if ($result) {
            // Registro duplicado encontrado - atualizar dados existentes
            $query = $db->prepare('UPDATE tomazia_clientes SET user_id = :user_id, nome = :nome, email = :email, telemovel = :telefone, data_registro = :data_registro WHERE id = :id');
            $query->bindValue(':id', $result['id'], SQLITE3_INTEGER);
            $query->bindValue(':user_id', $userId, SQLITE3_TEXT);
            $query->bindValue(':nome', $nome, SQLITE3_TEXT);
            $query->bindValue(':email', $email, SQLITE3_TEXT);
            $query->bindValue(':telefone', $telefone, SQLITE3_TEXT);
            $query->bindValue(':data_registro', $data_registro, SQLITE3_TEXT);
            $query->execute();
            
            $_SESSION['nome'] = $nome;
            $_SESSION['updated'] = true; // Indicar que foi atualização
            header("location: bemvindo.php");
            exit;
        } else {
            // Inserir novo registro
            $query = $db->prepare('INSERT INTO tomazia_clientes (user_id, nome, email, telemovel, data_registro) VALUES (:user_id, :nome, :email, :telefone, :data_registro)');
            $query->bindValue(':user_id', $userId, SQLITE3_TEXT);
            $query->bindValue(':nome', $nome, SQLITE3_TEXT);
            $query->bindValue(':email', $email, SQLITE3_TEXT);
            $query->bindValue(':telefone', $telefone, SQLITE3_TEXT);
            $query->bindValue(':data_registro', $data_registro, SQLITE3_TEXT);
            $query->execute();

            $_SESSION['nome'] = $nome;
            header("location: bemvindo.php");
            exit;
        }
    }
} catch (Exception $e) {
    error_log("Error in form.php: " . $e->getMessage());
    echo "Erro: Ocorreu um problema. Por favor, tente novamente.";
}
?>