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
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Inter:wght@400;500;600;700&family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: 'Inter', 'Montserrat', -apple-system, BlinkMacSystemFont, sans-serif;
            overflow-x: hidden;
            background-color: #5D1F3A;
            color: #f0f0f0;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
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
            padding: 20px;
            border-radius: 16px;
            border: 1px solid rgba(212, 175, 55, 0.3);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .nav-menu a {
            display: block;
            color: #f0f0f0;
            text-decoration: none;
            padding: 12px 24px;
            margin: 6px 0;
            border-radius: 8px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 600;
            font-size: 1rem;
            letter-spacing: 0.01em;
        }

        .nav-menu a:hover {
            background: #D4AF37;
            color: #3D0F24;
            transform: translateX(-4px);
            box-shadow: 0 4px 12px rgba(212, 175, 55, 0.3);
        }
        
        .hero-content h1 {
            font-family: 'Playfair Display', serif;
            font-size: 4rem;
            font-weight: 900;
            margin-bottom: 24px;
            text-shadow: 2px 2px 12px rgba(0, 0, 0, 0.7);
            letter-spacing: -0.03em;
        }

        .hero-content p {
            font-family: 'Inter', 'Montserrat', sans-serif;
            font-size: 1.625rem;
            margin-bottom: 36px;
            font-weight: 400;
            letter-spacing: -0.01em;
            line-height: 1.6;
        }

        .btn-custom {
            background-color: #D4AF37;
            color: #3D0F24;
            border: 2px solid #D4AF37;
            padding: 16px 36px;
            font-size: 1.25rem;
            border-radius: 10px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 600;
            letter-spacing: 0.02em;
            box-shadow: 0 4px 16px rgba(212, 175, 55, 0.3);
            text-decoration: none;
            display: inline-block;
        }

        .btn-custom:hover {
            background-color: transparent;
            color: #D4AF37;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(212, 175, 55, 0.4);
            text-decoration: none;
        }

        .btn-custom:active {
            transform: translateY(-1px);
            box-shadow: 0 3px 12px rgba(212, 175, 55, 0.3);
        }
        
        section { 
            padding: 100px 20px; 
        }
        section h2 {
            text-align: center;
            font-size: 3rem;
            margin-bottom: 48px;
            font-family: 'Playfair Display', serif;
            color: #D4AF37;
            font-weight: 900;
            letter-spacing: -0.03em;
        }
        
        .wifi-section { background-color: #5D1F3A; }
        .events-section { background-color: var(--secondary-burgundy); }
        .map-section { background-color: #5D1F3A; }

        .wifi-card, .event-card {
            background: #3D0F24;
            color: #f0f0f0;
            border: 1px solid rgba(212, 175, 55, 0.2);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
            border-radius: 16px;
            padding: 2rem;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }
        
        .event-card {
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .event-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(180deg, #D4AF37, #FFD700);
            border-radius: 16px 0 0 16px;
        }
        .event-card h3 { 
            color: #D4AF37; 
            font-size: 1.625rem;
            font-weight: 700;
            margin-bottom: 16px;
            letter-spacing: -0.01em;
        }
        .event-card .event-date { 
            color: #FFD700; 
            font-weight: 600;
            font-size: 1.0625rem;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .event-card .event-description {
            color: #e0e0e0;
            line-height: 1.7;
            font-size: 1rem;
        }
        .event-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #D4AF37, #FFD700);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.625rem;
            margin-bottom: 18px;
            box-shadow: 0 4px 12px rgba(212, 175, 55, 0.3);
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

        /* Slideshow Styles */
        .slideshow-container {
            max-width: 1000px;
            margin: 0 auto;
            position: relative;
        }
        
        .carousel-fade .carousel-item {
            opacity: 0;
            transition: opacity 0.8s ease-in-out;
        }
        
        .carousel-fade .carousel-item.active {
            opacity: 1;
        }
        
        .carousel-inner {
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 12px 48px rgba(0, 0, 0, 0.6);
            background: #000;
        }
        
        .carousel-img-wrapper {
            position: relative;
            width: 100%;
            padding-top: 66.67%; /* 3:2 aspect ratio */
            overflow: hidden;
            background: linear-gradient(135deg, #1a1a1a 0%, #000 100%);
        }
        
        .carousel-img-wrapper img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: contain;
            transition: transform 0.3s ease;
        }
        
        .carousel-item:hover .carousel-img-wrapper img {
            transform: scale(1.02);
        }
        
        .custom-caption {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(61, 15, 36, 0.95), transparent);
            padding: 30px 20px 20px;
            text-align: center;
        }
        
        .caption-text {
            color: #f0f0f0;
            font-size: 1.125rem;
            font-weight: 500;
            margin: 0;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.8);
            line-height: 1.5;
        }
        
        .custom-indicators {
            bottom: -40px;
        }
        
        .custom-indicators li {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: rgba(212, 175, 55, 0.4);
            border: 2px solid rgba(212, 175, 55, 0.6);
            transition: all 0.3s ease;
        }
        
        .custom-indicators li.active {
            background-color: #D4AF37;
            border-color: #D4AF37;
            transform: scale(1.3);
        }
        
        .custom-control {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: rgba(61, 15, 36, 0.9);
            border: 2px solid rgba(212, 175, 55, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            opacity: 0.7;
        }
        
        .custom-control:hover {
            background: rgba(212, 175, 55, 0.95);
            border-color: #D4AF37;
            opacity: 1;
            transform: scale(1.1);
        }
        
        .custom-control .carousel-control-prev-icon,
        .custom-control .carousel-control-next-icon {
            width: 24px;
            height: 24px;
        }
        
        .photo-counter {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(61, 15, 36, 0.9);
            color: #D4AF37;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            border: 1px solid rgba(212, 175, 55, 0.5);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
        }

        @media (max-width: 768px) {
            .hero-content h1 { 
                font-size: 2.75rem; 
                margin-bottom: 20px;
            }
            .hero-content p { 
                font-size: 1.375rem;
                margin-bottom: 28px;
            }
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
            
            .custom-control {
                width: 40px;
                height: 40px;
            }
            
            .custom-control .carousel-control-prev-icon,
            .custom-control .carousel-control-next-icon {
                width: 20px;
                height: 20px;
            }
            
            .caption-text {
                font-size: 0.95rem;
            }
            
            .photo-counter {
                font-size: 0.8rem;
                padding: 6px 12px;
            }
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
        <a href="#galeria">Galeria</a>
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
                <img src="img/wifi.png" alt="WiFi" onclick="toggleWiFi()" style="width: 160px; cursor: pointer; transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1); filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.3));" onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='scale(1)'">
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

    <section id="galeria" class="gallery-section" style="background-color: #5D1F3A;">
        <div class="container">
            <h2>📸 Galeria de Fotos 📸</h2>
            <?php
            try {
                $db = getDbConnection();
                $stmt = $db->prepare("SELECT * FROM fotos WHERE visivel = 1 AND status = 'aprovado' ORDER BY data_upload DESC");
                $result = $stmt->execute();
                $fotos = [];
                while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                    $fotos[] = $row;
                }
                
                if (count($fotos) > 0):
            ?>
            <div class="slideshow-container">
                <div id="photoCarousel" class="carousel slide carousel-fade" data-ride="carousel" data-interval="4000" data-pause="hover">
                    <ol class="carousel-indicators custom-indicators">
                        <?php for ($i = 0; $i < count($fotos); $i++): ?>
                            <li data-target="#photoCarousel" data-slide-to="<?php echo $i; ?>" class="<?php echo $i === 0 ? 'active' : ''; ?>"></li>
                        <?php endfor; ?>
                    </ol>
                    <div class="carousel-inner">
                        <?php foreach ($fotos as $index => $foto): ?>
                            <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                <div class="carousel-img-wrapper">
                                    <img src="<?php echo htmlspecialchars($foto['caminho']); ?>" class="d-block w-100" alt="<?php echo htmlspecialchars($foto['nome_foto']); ?>" loading="lazy">
                                </div>
                                <?php if (!empty($foto['descricao'])): ?>
                                    <div class="carousel-caption custom-caption">
                                        <p class="caption-text"><?php echo htmlspecialchars($foto['descricao']); ?></p>
                                    </div>
                                <?php endif; ?>
                                <div class="photo-counter">
                                    <?php echo ($index + 1) . ' / ' . count($fotos); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <a class="carousel-control-prev custom-control" href="#photoCarousel" role="button" data-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="sr-only">Anterior</span>
                    </a>
                    <a class="carousel-control-next custom-control" href="#photoCarousel" role="button" data-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="sr-only">Próxima</span>
                    </a>
                </div>
            </div>
            <?php else: ?>
                <div class="text-center">
                    <p class="lead" style="color: #a0a0a0;">Ainda não há fotos na galeria.</p>
                    <p style="color: #888;">Adicione fotos através do painel administrativo.</p>
                </div>
            <?php endif;
            } catch (Exception $e) {
                error_log("Error loading photos: " . $e->getMessage());
                echo '<div class="text-center"><p style="color: #a0a0a0;">Erro ao carregar fotos.</p></div>';
            }
            ?>
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
                        echo '<div class="event-card p-4 h-100" style="transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 8px 24px rgba(212, 175, 55, 0.3);" onmouseover="this.style.transform=\'translateY(-10px)\'; this.style.boxShadow=\'0 12px 32px rgba(212, 175, 55, 0.5)\';" onmouseout="this.style.transform=\'translateY(0)\'; this.style.boxShadow=\'0 8px 24px rgba(212, 175, 55, 0.3)\';">';
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