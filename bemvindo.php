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

        .password-container {
            display: none;
            margin-top: 20px;
            font-size: 18px;
            font-weight: bold;
        }

        .image-container img {
            cursor: pointer;
            width: 200px;
            transition: opacity 0.3s ease;
        }

        .image-container img:hover {
            opacity: 0.7;
        }

        .password-container p {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            font-size: 20px;
        }
    </style>
</head>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="index.php"><img src="img/tomazia.png" height="100"></a>
</nav>
<body>
    <div class="container mt-5 text-center">
        <h1>Bem-vindo, <?php echo htmlspecialchars($nome); ?>!</h1> 
        <p>Estamos felizes em vê-lo(a) novamente no Bar da Tomazia!</p>

        <br>
        <br>
        <p>Clique na imagem para ver a password da Internet:</p>

        <div class="image-container">
            <img src="img/wifi.png" alt="Clique para ver a senha" onclick="mostrarPW()">
        </div>

        <div class="password-container" id="PWContainer">
            <p><strong>Rede:</strong> NOS-2B6E-5</p>
            <p><strong>Password:</strong> 5YV4UJC4</p>
        </div>

        <a href="cardapio.php" class="btn btn-primary mt-4">Ir para o Cardápio</a>
        <br>
        <br>
        <p> Queres enviar uma foto? <a href="fotos.php" style="text-decoration: underline; color: #A52A2A;"> Clica aqui</a> </p>
    </div>

    <script>
        function mostrarPW() {
            // Esconde a imagem
            document.querySelector('.image-container').style.display = 'none';
            // Exibe o conteúdo da pw
            document.getElementById('PWContainer').style.display = 'block';
        }
    </script>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>