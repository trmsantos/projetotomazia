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
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', Arial, sans-serif;
            background-color: #5D1F3A;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            color: #f0f0f0;
        }
        .navbar {
            background-color: rgba(93, 31, 58, 0.95) !important;
            border-bottom: 1px solid rgba(212, 175, 55, 0.2);
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
            background-color: rgba(61, 15, 36, 0.95);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(212, 175, 55, 0.3);
        }
        .container h1 {
            color: #D4AF37;
            font-family: 'Playfair Display', serif;
            font-weight: 700;
        }
        .container p {
            color: #cccccc;
        }
        .container p b {
            color: #D4AF37;
        }
        .qr-code {
            margin: 20px 0;
            display: flex;
            justify-content: center;
        }
    </style>
</head>
<nav class="navbar navbar-expand-lg navbar-dark">
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