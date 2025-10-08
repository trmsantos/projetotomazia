<?php
session_start();
require_once 'config.php';

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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            overflow-x: hidden;
        }

        /* Hero Section with Video Background */
        .hero-section {
            position: relative;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            color: white;
            text-align: center;
        }

        .video-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -2;
        }

        .video-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: -1;
        }

        /* Navigation Menu */
        .nav-menu {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: rgba(165, 42, 42, 0.9);
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }

        .nav-menu a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            margin: 5px 0;
            border-radius: 5px;
            transition: background 0.3s;
            font-weight: bold;
        }

        .nav-menu a:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .hero-content {
            z-index: 1;
            padding: 20px;
        }

        .hero-content h1 {
            font-size: 3.5rem;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .hero-content p {
            font-size: 1.5rem;
            margin-bottom: 30px;
        }

        .btn-custom {
            background-color: #A52A2A;
            color: white;
            border: 2px solid #A52A2A;
            padding: 15px 30px;
            font-size: 1.2rem;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .btn-custom:hover {
            background-color: white;
            color: #A52A2A;
        }

        /* Sections */
        section {
            padding: 80px 20px;
            min-height: 100vh;
        }

        section h2 {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 40px;
            color: #A52A2A;
        }

        /* WiFi Section */
        .wifi-section {
            background-color: #f8f9fa;
        }

        .wifi-card {
            max-width: 400px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .wifi-card img {
            width: 150px;
            cursor: pointer;
            margin-bottom: 20px;
            transition: transform 0.3s;
        }

        .wifi-card img:hover {
            transform: scale(1.1);
        }

        .wifi-info {
            display: none;
            margin-top: 20px;
        }

        .wifi-info p {
            background-color: #A52A2A;
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }

        /* Events Section */
        .events-section {
            background-color: white;
        }

        .event-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .event-card:hover {
            transform: translateY(-5px);
        }

        .event-card h3 {
            color: #A52A2A;
            margin-bottom: 10px;
        }

        .event-card .event-date {
            font-weight: bold;
            color: #666;
            margin-bottom: 10px;
        }

        /* Map Section */
        .map-section {
            background-color: #f8f9fa;
        }

        .map-container {
            max-width: 900px;
            margin: 0 auto;
        }

        .map-container iframe {
            width: 100%;
            height: 450px;
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2rem;
            }

            .hero-content p {
                font-size: 1rem;
            }

            .nav-menu {
                top: 10px;
                right: 10px;
                padding: 10px;
            }

            .nav-menu a {
                padding: 8px 15px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Menu -->
    <div class="nav-menu">
        <a href="#home">Início</a>
        <a href="#menu">Menu</a>
        <a href="#fotos">Fotos</a>
        <a href="#eventos">Eventos</a>
        <a href="#localizacao">Onde nos encontrar</a>
    </div>

    <!-- Hero Section with Video Background -->
    <section id="home" class="hero-section">
        <!-- Placeholder for video background - you can add a video file later -->
        <div class="video-background" style="background: linear-gradient(135deg, #A52A2A 0%, #8B0000 100%);"></div>
        <div class="video-overlay"></div>
        <div class="hero-content">
            <h1>Bem-vindo, <?php echo htmlspecialchars($nome); ?>!</h1>
            <p>Estamos felizes em vê-lo(a) no Bar da Tomazia!</p>
            <a href="#wifi" class="btn btn-custom">Conectar ao WiFi</a>
        </div>
    </section>

    <!-- WiFi Section -->
    <section id="wifi" class="wifi-section">
        <div class="container">
            <h2>WiFi Gratuito</h2>
            <div class="wifi-card">
                <p>Clique na imagem para ver as credenciais do WiFi:</p>
                <img src="img/wifi.png" alt="WiFi" onclick="mostrarWiFi()">
                <div class="wifi-info" id="wifiInfo">
                    <p><strong>Rede:</strong> <?php echo htmlspecialchars(WIFI_REDE); ?></p>
                    <p><strong>Password:</strong> <?php echo htmlspecialchars(WIFI_PASSWORD); ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Menu Section -->
    <section id="menu" class="menu-section">
        <div class="container">
            <h2>Nosso Cardápio</h2>
            <div class="text-center">
                <p class="lead">Descubra nossa seleção de bebidas e petiscos</p>
                <a href="cardapio.php" class="btn btn-custom mt-3">Ver Cardápio Completo</a>
            </div>
        </div>
    </section>

    <!-- Fotos Section -->
    <section id="fotos" class="fotos-section" style="background-color: #f8f9fa;">
        <div class="container">
            <h2>Partilhe os seus momentos</h2>
            <div class="text-center">
                <p class="lead">Tire uma foto e partilhe connosco!</p>
                <a href="fotos.php" class="btn btn-custom mt-3">Enviar Foto</a>
            </div>
        </div>
    </section>

    <!-- Events Section -->
    <section id="eventos" class="events-section">
        <div class="container">
            <h2>Próximos Eventos</h2>
            <div class="row">
                <?php
                try {
                    $db = getDbConnection();
                    $stmt = $db->prepare('SELECT * FROM eventos WHERE data_evento >= date("now") ORDER BY data_evento ASC LIMIT 6');
                    $result = $stmt->execute();
                    $hasEvents = false;
                    
                    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                        $hasEvents = true;
                        $data = new DateTime($row['data_evento']);
                        echo '<div class="col-md-6 col-lg-4">';
                        echo '<div class="event-card">';
                        echo '<h3>' . htmlspecialchars($row['nome_evento']) . '</h3>';
                        echo '<p class="event-date">📅 ' . $data->format('d/m/Y') . '</p>';
                        echo '<p>' . htmlspecialchars($row['descricao']) . '</p>';
                        echo '</div>';
                        echo '</div>';
                    }
                    
                    if (!$hasEvents) {
                        echo '<div class="col-12 text-center">';
                        echo '<p class="lead">Não há eventos programados no momento. Fique atento!</p>';
                        echo '</div>';
                    }
                } catch (Exception $e) {
                    error_log("Error loading events: " . $e->getMessage());
                    echo '<div class="col-12 text-center">';
                    echo '<p>Erro ao carregar eventos.</p>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section id="localizacao" class="map-section">
        <div class="container">
            <h2>Onde nos encontrar</h2>
            <div class="map-container">
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3024.9537729415894!2d-8.4164!3d40.6446!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNDDCsDM4JzQwLjYiTiA4wrAyNCc1OS4wIlc!5e0!3m2!1spt-PT!2spt!4v1234567890123!5m2!1spt-PT!2spt" 
                    allowfullscreen="" 
                    loading="lazy" 
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
                <div class="text-center mt-3">
                    <p class="lead"><strong>Bar da Tomazia</strong></p>
                    <p>Visite-nos e desfrute de um ambiente único!</p>
                </div>
            </div>
        </div>
    </section>

    <script>
        function mostrarWiFi() {
            document.getElementById('wifiInfo').style.display = 'block';
        }

        // Smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>