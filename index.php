<?php
session_start();
require_once 'config.php';

try {
    $db = getDbConnection();
    
    // Verificar se o cookie 'user_id' existe
    if (!isset($_COOKIE['user_id'])) {
        $userId = bin2hex(random_bytes(16)); 
        setSecureCookie('user_id', $userId, time() + (10 * 365 * 24 * 60 * 60));
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
    error_log("Error in index.php: " . $e->getMessage());
    echo "Erro: Ocorreu um problema. Por favor, tente novamente.";
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
        body {
            position: relative;
            min-height: 100vh;
        }

        .navbar {
            position: relative;
            z-index: 10;
        }

        .container {
            position: relative;
            z-index: 1;
            padding-top: 50px;
            padding-bottom: 50px;
        }

        .form-container {
            max-width: 400px;
            margin: 0 auto;
            background-color: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
        }

        .form-container h2 {
            color: #A52A2A;
            text-align: center;
            margin-bottom: 25px;
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
    <!-- Video Background -->
    <!-- Note: Change 'cocktail-video.mp4' to match your actual video filename -->
    <video class="video-background" autoplay loop muted playsinline>
        <source src="img/cocktail-video.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>
    <div class="video-overlay"></div>
    
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="index.php"><img src="img/tomazia.png" height="100"></a>
    </nav>
    <div class="container">
        <div class="form-container">
            <h2>Bem-vindo ao Bar da Tomazia</h2>
            <form method="POST" action="form.php">
                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo generateCsrfToken(); ?>">
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
    <script src="js/main.js"></script>
</body>
</html>