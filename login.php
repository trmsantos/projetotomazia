<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['register'])) {
        // Registro de novo administrador
        $username = $_POST['username'];
        $password = $_POST['password'];
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Conexão com a base de dados SQLite
        $db = new SQLite3('C:\Users\sara1\OneDrive\Desktop\BD\bd_teste.db');

        // Inserir novo administrador na base de dados
        $stmt = $db->prepare('INSERT INTO admin_users (username, psw) VALUES (:username, :password)');
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $stmt->bindValue(':password', $hashed_password, SQLITE3_TEXT);
        $stmt->execute();

        $success = "Administrador registrado com sucesso!";
    } else {
        // Login de administrador
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Conexão com a base de dados SQLite
        $db = new SQLite3('C:\Users\sara1\OneDrive\Desktop\BD\bd_teste.db');

        // Verificar as credenciais na base de dados
        $stmt = $db->prepare('SELECT * FROM admin_users WHERE username = :username');
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

        if ($result && password_verify($password, $result['psw'])) {
            $_SESSION['loggedin'] = true;
            header('Location: admin.php');
            exit;
        } else {
            $error = "Credenciais inválidas!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Bar da Tomazia</title>
    <link rel="icon" href="img/pngico.png" type="image/png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .form-container {
            max-width: 400px;
            margin: 0 auto;
            margin-top: 100px;
        }
        .btn {
            background-color: #A52A2A;
            color: white;
            border-color: #A52A2A;
        }
        .btn:hover {
            color: black;
            background-color: white;
            border-color: #A52A2A;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="index.php"><img src="img/tomazia.png" height="100"></a>
</nav>
<div class="container">
    <div class="form-container">
        <h2>Login de Administrador</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="username">Nome de Utilizador</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Entrar</button>
        </form>
        <hr>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>