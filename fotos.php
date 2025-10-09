<?php
session_start();

// Verifique se o nome do usuário está na sessão
if (!isset($_SESSION['nome'])) {
    // Redirecione para a página de erro
    header('Location: erro.php');
    exit();
}

$nome = $_SESSION['nome'];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Foto - Bar da Tomazia</title>
    <link rel="icon" href="img/pngico.png" type="image/png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .navbar-brand img {
            display: block;
            margin: 0 auto;
            transition: opacity 0.3s ease;
        }
        .navbar-brand img:hover {
            opacity: 0.7;
        }
        .container {
            text-align: center;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .qr-code {
            margin: 20px 0;
            display: flex;
            justify-content: center;
        }
    </style>
</head>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="index.php"><img src="img/tomazia.png" height="100" width="100"></a>
</nav>
<body>
    <div class="container">
        <h1>Enviar Foto</h1>
        <p>Bem-vindo <b> <?php echo htmlspecialchars($nome); ?> ,</b> Envia a tua foto usando o QR code abaixo.</p>
        <div class="qr-code">
            <img src="img/fotos.png" alt="QR Code" height="300" width="300">
        </div>
        <p>Após dar scan ao QR code, serás redirecionado automaticamente.</p>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>