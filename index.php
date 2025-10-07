<?php
session_start();

try {
    $db = new SQLite3(__DIR__ . '/bd/bd_teste.db');
    

    // Verificar se o cookie 'user_id' existe
    if (!isset($_COOKIE['user_id'])) {
        $userId = bin2hex(random_bytes(16)); 
        setcookie('user_id', $userId, time() + (10 * 365 * 24 * 60 * 60)); 
    } else {
        $userId = $_COOKIE['user_id']; 
    }

    // Verificar se há dados associados ao user_id na base de dados
    $query = $db->prepare('SELECT nome FROM tomazia_clientes WHERE user_id = :user_id');
    $query->bindValue(':user_id', $userId, SQLITE3_TEXT);
    $result = $query->execute()->fetchArray(SQLITE3_ASSOC);

    if ($result) {
        // Armazenar o nome na sessão e redirecionar para a página de boas-vindas
        $_SESSION['nome'] = htmlspecialchars($result['nome']);
        header("Location: bemvindo.php");
        exit;
    }
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bar da Tomazia</title>
    <link rel="icon" href="img/pngico.png" type="image/png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css"> 
    <style>
        .form-container {
            max-width: 400px;
            margin: 0 auto;
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
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="index.php"><img src="img/tomazia.png" height="100"></a>
</nav>
<body>
    <div class="container">
        <div class="form-container">
            <h2>Bem-vindo ao Bar da Tomazia</h2>
            <form method="POST" action="form.php">
                <div class="form-group">
                    <label for="nome">Nome</label>
                    <input type="text" class="form-control" id="nome" name="nome" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="telefone">Telefone</label>
                    <input type="text" class="form-control" id="telefone" name="telefone" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Enviar</button>
            </form>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>