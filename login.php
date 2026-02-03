<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar token CSRF
    if (!isset($_POST[CSRF_TOKEN_NAME]) || !verifyCsrfToken($_POST[CSRF_TOKEN_NAME])) {
        die("Erro: Token CSRF inválido.");
    }

    try {
        $db = getDbConnection();

        $username = trim($_POST['username']);
        $password = $_POST['password'];

        $stmt = $db->prepare('SELECT * FROM admin_users WHERE username = :username');
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

        if ($result && password_verify($password, $result['psw'])) {
            $_SESSION['loggedin'] = true;
            header('Location: /admin');
            exit;
        } else {
            $error = "Credenciais inválidas!";
        }
    } catch (Exception $e) {
        error_log("Error in login.php: " . $e->getMessage());
        $error = "Erro: Ocorreu um problema. Por favor, tente novamente.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Bar da Tomazia</title>
    <link rel="icon" href="/img/tomazia.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { background-color: #5D1F3A; color: #f0f0f0; font-family: 'Montserrat', Arial, sans-serif; min-height: 100vh; }
        .video-background { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; object-fit: cover; z-index: -2; }
        .video-overlay { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(93,31,58,0.8); z-index: -1; }
        .navbar { background-color: rgba(93,31,58,0.95)!important; border-bottom: 1px solid rgba(212,175,55,0.2);}
        .navbar-brand img { height: 80px; }
        .form-container { max-width: 420px; margin: 100px auto 0 auto; background: rgba(61,15,36,0.9); padding: 40px; border-radius: 15px; box-shadow: 0 8px 32px rgba(0,0,0,0.5); border: 1px solid rgba(212,175,55,0.3);}
        .form-container h2 { color: #D4AF37; font-family: 'Playfair Display', serif; text-align: center; margin-bottom: 30px; font-weight: 700;}
        .form-label { color: #f0f0f0; font-weight: 600;}
        .form-control { background-color: rgba(93,31,58,0.3); border: 1px solid rgba(212,175,55,0.4); color: #f0f0f0;}
        .form-control:focus { background-color: rgba(93,31,58,0.5); border-color: #D4AF37; color: #fff; box-shadow: none;}
        .btn-primary { background-color: #D4AF37; border-color: #D4AF37; color: #3D0F24; font-weight: 600; transition: all 0.3s;}
        .btn-primary:hover { background-color: transparent; border-color: #D4AF37; color: #D4AF37;}
        @media (max-width: 768px) { .form-container { margin: 40px auto; padding: 25px; } }
    </style>
</head>
<body>
    <video class="video-background" autoplay loop muted playsinline>
        <source src="/img/3772392-hd_1920_1080_25fps.mp4" type="video/mp4">
    </video>
    <div class="video-overlay"></div>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <a class="navbar-brand" href="index.php"><img src="/img/tomazia.png" alt="Tomazia"></a>
    </nav>
    <div class="container">
        <div class="form-container">
            <h2>Login de Administrador</h2>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST" action="login.php">
                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo generateCsrfToken(); ?>">
                <div class="form-group">
                    <label for="username" class="form-label">Nome de Utilizador</label>
                    <input type="text" class="form-control" id="username" name="username" required autocomplete="username">
                </div>
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
                </div>
                <button type="submit" class="btn btn-primary btn-block">Entrar</button>
            </form>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>