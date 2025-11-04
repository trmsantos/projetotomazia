<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erro - Bar da Tomazia</title>
    <link rel="icon" href="img/tomazia.png" type="image/png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #1a1a1a;
            color: #f0f0f0;
            font-family: 'Montserrat', Arial, sans-serif;
            min-height: 100vh;
        }
        .video-background {
            position: fixed;
            top: 0; left: 0; width: 100vw; height: 100vh;
            object-fit: cover; z-index: -2;
        }
        .video-overlay {
            position: fixed;
            top: 0; left: 0; width: 100vw; height: 100vh;
            background: rgba(0,0,0,0.7); z-index: -1;
        }
        .navbar {
            background-color: rgba(26,26,26,0.92)!important;
            border-bottom: 1px solid rgba(139,69,19,0.2);
        }
        .navbar-brand img { height: 80px; }
        .error-container {
            max-width: 480px;
            margin: 120px auto 0 auto;
            background: rgba(26,26,26,0.95);
            padding: 40px 32px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.5);
            border: 1px solid rgba(139,69,19,0.3);
            text-align: center;
        }
        .error-container h1 {
            color: #8B4513;
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            margin-bottom: 18px;
        }
        .error-container p {
            color: #cccccc;
            margin-bottom: 30px;
            font-size: 1.2rem;
        }
        .btn-primary {
            background-color: #8B4513;
            border-color: #8B4513;
            color: #1a1a1a;
            font-weight: 600;
            font-size: 1.1rem;
            padding: 12px 28px;
            border-radius: 6px;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            background-color: transparent;
            color: #8B4513;
        }
    </style>
</head>
<body>
    <video class="video-background" autoplay loop muted playsinline>
        <source src="img/3772392-hd_1920_1080_25fps.mp4" type="video/mp4">
    </video>
    <div class="video-overlay"></div>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <a class="navbar-brand" href="index.php"><img src="img/tomazia.png" alt="Tomazia"></a>
    </nav>
    <div class="container">
        <div class="error-container">
            <h1>Erro</h1>
            <p>Ocorreu um erro. Por favor, tente novamente.</p>
            <a href="index.php" class="btn btn-primary">Voltar para a página inicial</a>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>