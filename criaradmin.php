<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $db = getDbConnection();
            
            // Verificar se o username já existe
            $checkStmt = $db->prepare('SELECT id FROM admin_users WHERE username = :username');
            $checkStmt->bindValue(':username', $username, SQLITE3_TEXT);
            $result = $checkStmt->execute()->fetchArray(SQLITE3_ASSOC);
            
            if ($result) {
                // Username já existe - atualizar password
                $stmt = $db->prepare('UPDATE admin_users SET psw = :password WHERE username = :username');
                $stmt->bindValue(':username', $username, SQLITE3_TEXT);
                $stmt->bindValue(':password', $hash, SQLITE3_TEXT);
                $stmt->execute();
                echo "<p style='color:green;font-weight:bold'>Password do utilizador atualizada com sucesso!</p>";
            } else {
                // Inserir novo utilizador
                $stmt = $db->prepare('INSERT INTO admin_users (username, psw) VALUES (:username, :password)');
                $stmt->bindValue(':username', $username, SQLITE3_TEXT);
                $stmt->bindValue(':password', $hash, SQLITE3_TEXT);
                $stmt->execute();
                echo "<p style='color:green;font-weight:bold'>Utilizador criado com sucesso!</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color:red'>Erro: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color:red'>Preencha todos os campos!</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Criar Admin</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { background: #121212; color: #f0f0f0; font-family: Montserrat, Arial, sans-serif;}
        .form-container { max-width: 400px; margin: 80px auto; background: #232323; padding: 30px; border-radius: 8px;}
        label { color: #D4AF37; }
        .btn-primary { background: #D4AF37; border: none; color: #1a1a1a; font-weight: bold;}
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Criar Utilizador Admin</h2>
        <form method="POST">
            <div class="form-group">
                <label for="username">Nome de Utilizador</label>
                <input type="text" class="form-control" name="username" id="username" required autocomplete="off">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" name="password" id="password" required autocomplete="off">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Criar Admin</button>
        </form>
        <p class="mt-3 text-warning" style="font-size:0.9em;">Apaga este ficheiro depois de criares o admin!</p>
    </div>
</body>
</html>