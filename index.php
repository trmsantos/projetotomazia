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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css"> 
    <style>
        body {
            position: relative;
            min-height: 100vh;
            background-color: #1a1a1a;
        }

        .navbar {
            position: relative;
            z-index: 10;
            background-color: rgba(26, 26, 26, 0.95) !important;
        }

        .container {
            position: relative;
            z-index: 1;
            padding-top: 50px;
            padding-bottom: 50px;
        }

        .form-container {
            max-width: 450px;
            margin: 0 auto;
            background-color: rgba(26, 26, 26, 0.9);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(212, 175, 55, 0.3);
        }

        .form-container h2 {
            color: #D4AF37;
            text-align: center;
            margin-bottom: 30px;
            font-family: 'Playfair Display', serif;
            font-weight: 700;
        }

        .form-group label {
            color: #f0f0f0;
            font-weight: 600;
        }

        .form-control {
            background-color: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(212, 175, 55, 0.3);
            color: #f0f0f0;
        }

        .form-control:focus {
            background-color: rgba(255, 255, 255, 0.1);
            border-color: #D4AF37;
            color: #ffffff;
        }

        .btn {
            background-color: #D4AF37;
            color: #1a1a1a;
            border-color: #D4AF37;
            font-weight: 600;
            padding: 12px;
            transition: all 0.3s ease;
        }

        .btn:hover {
            color: #D4AF37;
            background-color: transparent;
            border-color: #D4AF37;
        }

        .form-check-label {
            color: #cccccc;
            font-size: 0.9rem;
        }

        .form-check-label a {
            color: #D4AF37;
            text-decoration: none;
        }

        .form-check-label a:hover {
            color: #ffffff;
            text-decoration: underline;
        }

        .form-check-input {
            background-color: rgba(255, 255, 255, 0.1);
            border: 2px solid #D4AF37;
        }

        .form-check-input:checked {
            background-color: #D4AF37;
            border-color: #D4AF37;
        }

        #termsError {
            color: #ff6b6b;
            font-size: 0.85rem;
            margin-top: 5px;
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
    
    <nav class="navbar navbar-expand-lg navbar-dark">
        <a class="navbar-brand" href="index.php"><img src="img/tomazia.png" height="100"></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Início</a>
                </li>
            </ul>
        </div>
    </nav>
    <div class="container">
        <div class="form-container">
            <h2>Bem-vindo ao Bar da Tomazia</h2>
            <form method="POST" action="form.php" id="registrationForm">
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
                <div class="form-group form-check">
                    <input type="checkbox" class="form-check-input" id="termos" name="termos" required>
                    <label class="form-check-label" for="termos">
                        Eu li e aceito os <a href="termos.php" target="_blank">Termos e Condições</a>
                    </label>
                    <div id="termsError" style="display:none;">Você deve aceitar os Termos e Condições para continuar.</div>
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