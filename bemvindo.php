<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['nome'])) {
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
    <link rel="icon" href="img/tomazia.png" type="image/png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            overflow-x: hidden;
            background-color: #5D1F3A;
            color: #f0f0f0;
        }

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
            top: 50%;
            left: 50%;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: translate(-50%, -50%);
            z-index: -2;
        }

        .video-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(93, 31, 58, 0.7);
            z-index: -1;
        }
        
        .nav-menu {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: rgba(61, 15, 36, 0.95);
            padding: 15px;
            border-radius: 10px;
            border: 1px solid rgba(212, 175, 55, 0.3);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }

        .nav-menu a {
            display: block;
            color: #f0f0f0;
            text-decoration: none;
            padding: 10px 20px;
            margin: 5px 0;
            border-radius: 5px;
            transition: background 0.3s, color 0.3s;
            font-weight: bold;
        }

        .nav-menu a:hover {
            background: #D4AF37;
            color: #3D0F24;
        }
        
        .hero-content h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            margin-bottom: 20px;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.7);
        }

        .hero-content p {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.5rem;
            margin-bottom: 30px;
        }

        .btn-custom {
            background-color: #D4AF37;
            color: #3D0F24;
            border: 2px solid #D4AF37;
            padding: 15px 30px;
            font-size: 1.2rem;
            border-radius: 5px;
            transition: all 0.3s;
            font-weight: 600;
        }

        .btn-custom:hover {
            background-color: transparent;
            color: #D4AF37;
        }
        
        section { padding: 80px 20px; }
        section h2 {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 40px;
            font-family: 'Playfair Display', serif;
            color: #D4AF37;
        }
        
        .wifi-section { background-color: #5D1F3A; }
        .events-section { background-color: var(--secondary-burgundy); }
        .map-section { background-color: #5D1F3A; }

        .wifi-card, .event-card {
            background: #3D0F24;
            color: #f0f0f0;
            border: 1px solid rgba(212, 175, 55, 0.2);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
            border-radius: 10px;
        }
        
        .event-card {
            position: relative;
            overflow: hidden;
        }
        .event-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(180deg, #D4AF37, #FFD700);
        }
        .event-card h3 { 
            color: #D4AF37; 
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .event-card .event-date { 
            color: #FFD700; 
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .event-card .event-description {
            color: #e0e0e0;
            line-height: 1.6;
        }
        .event-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #D4AF37, #FFD700);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }

        .btn-copy-icon {
            background: none;
            border: none;
            color: #3D0F24;
            cursor: pointer;
            padding: 5px;
            opacity: 0.8;
            transition: opacity 0.2s;
        }
        .btn-copy-icon:hover {
            opacity: 1;
        }
        .btn-copy-icon svg {
            width: 20px;
            height: 20px;
        }

        .hamburger-menu {
            display: none;
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1002;
            background: none;
            border: none;
            cursor: pointer;
            padding: 10px;
        }
        .hamburger-menu span {
            display: block;
            width: 30px;
            height: 3px;
            margin: 6px 0;
            background-color: #D4AF37;
            border-radius: 3px;
            transition: all 0.3s ease-in-out;
        }
        .hamburger-menu.open span:nth-child(1) { transform: translateY(9px) rotate(45deg); }
        .hamburger-menu.open span:nth-child(2) { opacity: 0; }
        .hamburger-menu.open span:nth-child(3) { transform: translateY(-9px) rotate(-45deg); }

        @media (max-width: 768px) {
            .hero-content h1 { font-size: 2.5rem; }
            .hero-content p { font-size: 1.2rem; }
            .hamburger-menu { display: block; }
            .nav-menu {
                top: 0;
                right: 0;
                width: 70%;
                height: 100vh;
                padding-top: 80px;
                transform: translateX(100%);
                transition: transform 0.3s ease-in-out;
                border-radius: 0;
                background: rgba(61, 15, 36, 0.98);
                backdrop-filter: blur(5px);
            }
            .nav-menu.open { transform: translateX(0); }
        }
    </style>
</head>
<body>
    <button class="hamburger-menu" aria-label="Toggle menu">
        <span></span>
        <span></span>
        <span></span>
    </button>
    <div class="nav-menu">
        <a href="#home">Início</a>
        <a href="#menu">Menu</a>
        <a href="#eventos">Eventos</a>
        <a href="#localizacao">Onde nos encontrar</a>
    </div>

    <section id="home" class="hero-section">
        <video class="video-background" autoplay loop muted playsinline>
            <source src="img/3772392-hd_1920_1080_25fps.mp4" type="video/mp4">
            O teu navegador não suporta vídeos.
        </video>
        <div class="video-overlay"></div>
        <div class="hero-content">
            <h1>Bem-vindo(a), <?php echo htmlspecialchars($nome); ?>!</h1>
            <p>Estamos felizes em vê-lo(a) no Bar da Tomazia!</p>
            <a href="#wifi" class="btn btn-custom">Conectar ao WiFi</a>
        </div>
    </section>

    <section id="wifi" class="wifi-section">
        <div class="container">
            <h2>WiFi Gratuito</h2>
            <div class="wifi-card text-center p-4" style="max-width: 400px; margin: 0 auto;">
                <p>Clique na imagem para mostrar os dados WI-FI:</p>
                <img src="img/wifi.png" alt="WiFi" onclick="toggleWiFi()" style="width: 150px; cursor: pointer; transition: transform 0.3s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                <div class="wifi-info" id="wifiInfo" style="display: none; margin-top: 20px;">
                    <p style="text-align: left; background-color: #D4AF37; color: #1a1a1a; padding: 15px; border-radius: 5px; margin: 10px 0; font-weight: 600;"><strong>Rede:</strong> <?php echo htmlspecialchars(WIFI_REDE); ?></p>
                    <div style="display: flex; align-items: center; justify-content: space-between; background-color: #D4AF37; color: #1a1a1a; padding: 10px 15px; border-radius: 5px; margin: 10px 0; font-weight: 600;">
                        <span><strong>Password:</strong> <span id="wifiPassword"><?php echo htmlspecialchars(WIFI_PASSWORD); ?></span></span>
                        <button onclick="copyWifiPassword()" class="btn-copy-icon" aria-label="Copiar password">
                            <svg id="copyIcon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"/><path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3z"/></svg>
                            <svg id="checkIcon" style="display: none;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="menu" class="menu-section" style="background-color: #4A1830;">
        <div class="container">
            <h2>Nosso Cardápio</h2>
            <div class="text-center">
                <p class="lead" style="color: #a0a0a0;">Descubra a nossa seleção</p>
                <a href="cardapio.php" class="btn btn-custom mt-3">Ver Cardápio Completo</a>
            </div>
        </div>
    </section>

    <section id="eventos" class="events-section">
        <div class="container">
            <h2>🎉 Próximos Eventos 🎉</h2>
            <div class="row">
                <?php
                try {
                    $db = getDbConnection();
                    $stmt = $db->prepare('SELECT * FROM eventos WHERE visivel = 1 ORDER BY data_evento DESC');
                    $result = $stmt->execute();
                    $hasEvents = false;
                    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                        $hasEvents = true;
                        echo '<div class="col-md-6 col-lg-4 mb-4">';
                        echo '<div class="event-card p-4 h-100" style="transition: all 0.3s; box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);" onmouseover="this.style.transform=\'translateY(-8px)\'; this.style.boxShadow=\'0 8px 25px rgba(212, 175, 55, 0.5)\';" onmouseout="this.style.transform=\'translateY(0)\'; this.style.boxShadow=\'0 4px 15px rgba(212, 175, 55, 0.3)\';">';
                        echo '<div class="event-icon">🎊</div>';
                        echo '<h3>' . htmlspecialchars($row['nome_evento']) . '</h3>';
                        if (!empty($row['data_evento'])) {
                            $data = DateTime::createFromFormat('Y-m-d', $row['data_evento']);
                            if ($data) {
                                echo '<p class="event-date">📅 ' . $data->format('d/m/Y') . '</p>';
                            }
                        }
                        if (!empty($row['descricao'])) {
                            echo '<p class="event-description">' . htmlspecialchars($row['descricao']) . '</p>';
                        }
                        echo '</div>';
                        echo '</div>';
                    }
                    if (!$hasEvents) {
                        echo '<div class="col-12 text-center"><p class="lead" style="color: #a0a0a0;">Ainda não existem eventos registados.</p></div>';
                    }
                } catch (Exception $e) {
                    error_log("Error loading events: " . $e->getMessage());
                    echo '<div class="col-12 text-center"><p>Erro ao carregar eventos.</p></div>';
                }
                ?>
            </div>
        </div>
    </section>

    <section id="localizacao" class="map-section">
        <div class="container">
            <h2>Onde nos encontrar</h2>
            <div class="map-container" style="max-width: 900px; margin: 0 auto; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3718.9549633581555!2d-8.5703455!3d40.753472699999996!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xd239c78582a88fb%3A0xe64810ad61360cd7!2sBar%20da%20Tom%C3%A1zia%20-%20Estarreja!5e1!3m2!1spt-PT!2spt!4v1760017757627!5m2!1spt-PT!2spt" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
            <div class="text-center mt-4" style="color: #f0f0f0;">
                <p class="lead"><strong>Bar da Tomazia - Estarreja</strong></p>
                <p>Visite-nos e desfrute de um ambiente único!</p>
            </div>
        </div>
    </section>

    <script>
        const hamburger = document.querySelector('.hamburger-menu');
        const navMenu = document.querySelector('.nav-menu');
        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('open');
            navMenu.classList.toggle('open');
        });
        document.querySelectorAll('.nav-menu a').forEach(link => {
            link.addEventListener('click', () => {
                hamburger.classList.remove('open');
                navMenu.classList.remove('open');
            });
        });

        function toggleWiFi() {
            const wifiInfo = document.getElementById('wifiInfo');
            if (wifiInfo.style.display === 'none' || wifiInfo.style.display === '') {
                wifiInfo.style.display = 'block';
            } else {
                wifiInfo.style.display = 'none';
            }
        }

        function copyWifiPassword() {
            const passwordText = document.getElementById('wifiPassword').innerText;
            const copyIcon = document.getElementById('copyIcon');
            const checkIcon = document.getElementById('checkIcon');
            
            navigator.clipboard.writeText(passwordText).then(() => {
                copyIcon.style.display = 'none';
                checkIcon.style.display = 'inline-block';
                setTimeout(() => {
                    copyIcon.style.display = 'inline-block';
                    checkIcon.style.display = 'none';
                }, 2000);
            }).catch(err => {
                console.error('Falha ao copiar a password: ', err);
                alert('Não foi possível copiar a password.');
            });
        }

        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    </script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>