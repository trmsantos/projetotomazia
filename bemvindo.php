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
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600;700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --burgundy: #5D1F3A;
            --burgundy-dark: #3D0F24;
            --burgundy-medium: #4A1830;
            --gold: #D4AF37;
            --gold-light: #E8C76F;
            --gold-dark: #B8942F;
            --text-primary: #f0f0f0;
            --text-secondary: #cccccc;
            --text-tertiary: #a0a0a0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, var(--burgundy) 0%, var(--burgundy-dark) 100%);
            color: var(--text-primary);
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* Hero Section */
        .hero-section {
            position: relative;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
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
            background: linear-gradient(135deg, rgba(93, 31, 58, 0.88) 0%, rgba(61, 15, 36, 0.92) 100%);
            z-index: -1;
        }

        .hero-content {
            text-align: center;
            max-width: 800px;
            padding: 2rem;
            animation: fadeInUp 1s ease-out;
        }

        .hero-content h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(2.5rem, 7vw, 4.5rem);
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: #ffffff;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
            letter-spacing: -0.02em;
        }

        .hero-content p {
            font-size: clamp(1.125rem, 2.5vw, 1.5rem);
            margin-bottom: 2.5rem;
            color: rgba(255, 255, 255, 0.95);
            font-weight: 400;
        }

        /* Modern Navigation Sidebar */
        .nav-sidebar {
            position: fixed;
            top: 50%;
            right: 2rem;
            transform: translateY(-50%);
            z-index: 1000;
            background: rgba(61, 15, 36, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            padding: 1.5rem 1rem;
            border-radius: 20px;
            border: 1px solid rgba(212, 175, 55, 0.25);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
            transition: all 0.3s ease;
        }

        .nav-sidebar:hover {
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.5);
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.875rem 1.25rem;
            margin: 0.5rem 0;
            border-radius: 12px;
            color: var(--text-primary);
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 500;
            font-size: 0.95rem;
            position: relative;
            overflow: hidden;
        }

        .nav-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 3px;
            background: var(--gold);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .nav-item:hover {
            background: rgba(212, 175, 55, 0.15);
            color: var(--gold);
            transform: translateX(-4px);
            text-decoration: none;
        }

        .nav-item:hover::before {
            transform: scaleY(1);
        }

        .nav-item i {
            font-size: 1.125rem;
            width: 22px;
            text-align: center;
        }

        /* Button Styles */
        .btn-modern {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-dark) 100%);
            color: var(--burgundy-dark);
            padding: 1rem 2.25rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.0625rem;
            border: none;
            box-shadow: 0 4px 16px rgba(212, 175, 55, 0.3);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }

        .btn-modern::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            transform: translate(-50%, -50%);
            transition: width 0.5s, height 0.5s;
        }

        .btn-modern:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-modern:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(212, 175, 55, 0.4);
            text-decoration: none;
            color: var(--burgundy-dark);
        }

        /* Section Styles */
        section {
            padding: clamp(3rem, 10vw, 7rem) 0;
            position: relative;
        }

        .section-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(2rem, 5vw, 3.5rem);
            font-weight: 600;
            text-align: center;
            margin-bottom: clamp(2.5rem, 6vw, 4rem);
            color: var(--gold);
            position: relative;
            animation: fadeIn 0.8s ease-out;
        }

        .section-title::after {
            content: '';
            display: block;
            width: 100px;
            height: 3px;
            background: linear-gradient(90deg, transparent, var(--gold), transparent);
            margin: 1.5rem auto 0;
            border-radius: 10px;
        }

        /* WiFi Card */
        .wifi-card {
            max-width: 450px;
            margin: 0 auto;
            background: rgba(61, 15, 36, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(212, 175, 55, 0.25);
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
            transition: all 0.4s ease;
        }

        .wifi-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.5);
        }

        .wifi-icon-wrapper {
            width: 120px;
            height: 120px;
            margin: 0 auto 2rem;
            position: relative;
            cursor: pointer;
            transition: transform 0.4s ease;
        }

        .wifi-icon-wrapper:hover {
            transform: scale(1.08) rotate(5deg);
        }

        .wifi-icon-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: drop-shadow(0 8px 20px rgba(212, 175, 55, 0.3));
        }

        .wifi-info {
            display: none;
            margin-top: 2rem;
            animation: slideDown 0.3s ease-out;
        }

        .wifi-info-item {
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-dark) 100%);
            color: #1a1a1a;
            padding: 1.125rem 1.5rem;
            border-radius: 12px;
            margin: 0.75rem 0;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(212, 175, 55, 0.25);
        }

        /* Gallery Carousel */
        .carousel-inner {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.5);
        }

        .carousel-item img {
            max-height: 550px;
            object-fit: contain;
            background: #000;
        }

        .carousel-caption {
            background: rgba(61, 15, 36, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 1.25rem;
        }

        .empty-gallery {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-gallery-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            opacity: 0.3;
        }

        /* Events Grid */
        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
        }

        .event-card {
            background: rgba(61, 15, 36, 0.7);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-radius: 16px;
            padding: 2rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .event-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(180deg, var(--gold), var(--gold-light));
        }

        .event-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.5);
            border-color: rgba(212, 175, 55, 0.4);
        }

        .event-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, var(--gold), var(--gold-light));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1.25rem;
            box-shadow: 0 6px 20px rgba(212, 175, 55, 0.35);
        }

        .event-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.625rem;
            font-weight: 600;
            color: var(--gold);
            margin-bottom: 0.875rem;
        }

        .event-date {
            color: var(--gold-light);
            font-weight: 500;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .event-description {
            color: var(--text-secondary);
            line-height: 1.6;
        }

        /* Map Container */
        .map-container {
            max-width: 900px;
            margin: 0 auto;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(212, 175, 55, 0.25);
        }

        /* Mobile Menu */
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 1.5rem;
            right: 1.5rem;
            z-index: 1002;
            background: rgba(61, 15, 36, 0.95);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(212, 175, 55, 0.25);
            border-radius: 12px;
            padding: 0.75rem;
            cursor: pointer;
        }

        .mobile-menu-toggle span {
            display: block;
            width: 26px;
            height: 2px;
            margin: 5px 0;
            background: var(--gold);
            border-radius: 2px;
            transition: all 0.3s ease;
        }

        .mobile-menu-toggle.active span:nth-child(1) {
            transform: translateY(7px) rotate(45deg);
        }

        .mobile-menu-toggle.active span:nth-child(2) {
            opacity: 0;
        }

        .mobile-menu-toggle.active span:nth-child(3) {
            transform: translateY(-7px) rotate(-45deg);
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-section {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.8s ease, transform 0.8s ease;
        }

        .fade-in-section.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-sidebar {
                display: none;
                position: fixed;
                top: 0;
                right: 0;
                width: 75%;
                max-width: 320px;
                height: 100vh;
                transform: translateX(100%);
                border-radius: 0;
                padding-top: 5rem;
            }

            .nav-sidebar.active {
                display: block;
                transform: translateX(0);
            }

            .mobile-menu-toggle {
                display: block;
            }

            .events-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <button class="mobile-menu-toggle">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <nav class="nav-sidebar">
        <a href="#home" class="nav-item">
            <i class="fas fa-home"></i>
            <span>Início</span>
        </a>
        <a href="#galeria" class="nav-item">
            <i class="fas fa-images"></i>
            <span>Galeria</span>
        </a>
        <a href="fotos.php" class="nav-item">
            <i class="fas fa-camera"></i>
            <span>Enviar Fotos</span>
        </a>
        <a href="#menu" class="nav-item">
            <i class="fas fa-utensils"></i>
            <span>Menu</span>
        </a>
        <a href="#eventos" class="nav-item">
            <i class="fas fa-calendar-alt"></i>
            <span>Eventos</span>
        </a>
        <a href="#localizacao" class="nav-item">
            <i class="fas fa-map-marker-alt"></i>
            <span>Localização</span>
        </a>
    </nav>

    <section id="home" class="hero-section">
        <video class="video-background" autoplay loop muted playsinline>
            <source src="img/3772392-hd_1920_1080_25fps.mp4" type="video/mp4">
        </video>
        <div class="video-overlay"></div>
        <div class="hero-content">
            <h1>Bem-vindo, <?php echo htmlspecialchars($nome); ?></h1>
            <p>Desfrute da experiência única do Bar da Tomazia</p>
            <a href="#wifi" class="btn-modern">
                <i class="fas fa-wifi"></i>
                Conectar WiFi
            </a>
        </div>
    </section>

    <section id="wifi" class="fade-in-section" style="background: linear-gradient(135deg, var(--burgundy) 0%, var(--burgundy-medium) 100%);">
        <div class="container">
            <h2 class="section-title">WiFi Gratuito</h2>
            <div class="wifi-card text-center">
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem; font-size: 1.0625rem;">
                    Clique no ícone para revelar as credenciais
                </p>
                <div class="wifi-icon-wrapper" onclick="toggleWiFi()">
                    <img src="img/wifi.png" alt="WiFi">
                </div>
                <div class="wifi-info" id="wifiInfo">
                    <div class="wifi-info-item">
                        <span><strong>Rede:</strong> <?php echo htmlspecialchars(WIFI_REDE); ?></span>
                    </div>
                    <div class="wifi-info-item">
                        <span><strong>Password:</strong> <span id="wifiPassword"><?php echo htmlspecialchars(WIFI_PASSWORD); ?></span></span>
                        <button onclick="copyWifiPassword()" style="background: none; border: none; color: #1a1a1a; cursor: pointer; padding: 0.5rem;">
                            <i class="fas fa-copy" id="copyIcon"></i>
                            <i class="fas fa-check" id="checkIcon" style="display: none;"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="galeria" class="fade-in-section" style="background: linear-gradient(135deg, var(--burgundy-dark) 0%, var(--burgundy) 100%);">
        <div class="container">
            <h2 class="section-title">Galeria de Momentos</h2>
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
            <div id="photoCarousel" class="carousel slide" data-ride="carousel" data-interval="4000">
                <ol class="carousel-indicators">
                    <?php for ($i = 0; $i < count($fotos); $i++): ?>
                        <li data-target="#photoCarousel" data-slide-to="<?php echo $i; ?>" class="<?php echo $i === 0 ? 'active' : ''; ?>"></li>
                    <?php endfor; ?>
                </ol>
                <div class="carousel-inner">
                    <?php foreach ($fotos as $index => $foto): ?>
                        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                            <img src="<?php echo htmlspecialchars($foto['caminho']); ?>" class="d-block w-100" alt="<?php echo htmlspecialchars($foto['nome_foto']); ?>">
                            <?php if (!empty($foto['descricao'])): ?>
                                <div class="carousel-caption d-none d-md-block">
                                    <p><?php echo htmlspecialchars($foto['descricao']); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <a class="carousel-control-prev" href="#photoCarousel" role="button" data-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                </a>
                <a class="carousel-control-next" href="#photoCarousel" role="button" data-slide="next">
                    <span class="carousel-control-next-icon"></span>
                </a>
            </div>
            <?php else: ?>
                <div class="empty-gallery">
                    <svg class="empty-gallery-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                    </svg>
                    <p style="color: var(--text-tertiary); font-size: 1.125rem;">Sem imagens disponíveis</p>
                </div>
            <?php endif;
            } catch (Exception $e) {
                error_log("Error loading photos: " . $e->getMessage());
                echo '<div class="empty-gallery"><p style="color: var(--text-tertiary);">Erro ao carregar imagens</p></div>';
            }
            ?>
        </div>
    </section>

    <section id="menu" class="fade-in-section" style="background: linear-gradient(135deg, var(--burgundy-medium) 0%, var(--burgundy-dark) 100%);">
        <div class="container text-center">
            <h2 class="section-title">Nosso Cardápio</h2>
            <p class="lead" style="color: var(--text-secondary); margin-bottom: 2.5rem; max-width: 600px; margin-left: auto; margin-right: auto;">
                Descubra nossa seleção exclusiva de bebidas e petiscos
            </p>
            <a href="cardapio.php" class="btn-modern">
                <i class="fas fa-book-open"></i>
                Ver Cardápio Completo
            </a>
        </div>
    </section>

    <section id="eventos" class="fade-in-section" style="background: linear-gradient(135deg, var(--burgundy) 0%, var(--burgundy-medium) 100%);">
        <div class="container">
            <h2 class="section-title">Próximos Eventos</h2>
            <div class="events-grid">
                <?php
                try {
                    $db = getDbConnection();
                    $stmt = $db->prepare('SELECT * FROM eventos WHERE visivel = 1 ORDER BY data_evento DESC');
                    $result = $stmt->execute();
                    $hasEvents = false;
                    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                        $hasEvents = true;
                        echo '<div class="event-card">';
                        echo '<div class="event-icon"><i class="fas fa-star"></i></div>';
                        echo '<h3 class="event-title">' . htmlspecialchars($row['nome_evento']) . '</h3>';
                        if (!empty($row['data_evento'])) {
                            $data = DateTime::createFromFormat('Y-m-d', $row['data_evento']);
                            if ($data) {
                                echo '<p class="event-date"><i class="far fa-calendar"></i> ' . $data->format('d/m/Y') . '</p>';
                            }
                        }
                        if (!empty($row['descricao'])) {
                            echo '<p class="event-description">' . htmlspecialchars($row['descricao']) . '</p>';
                        }
                        echo '</div>';
                    }
                    if (!$hasEvents) {
                        echo '<div class="col-12 text-center" style="grid-column: 1 / -1;"><p class="lead" style="color: var(--text-tertiary);">Nenhum evento agendado no momento</p></div>';
                    }
                } catch (Exception $e) {
                    error_log("Error loading events: " . $e->getMessage());
                }
                ?>
            </div>
        </div>
    </section>

    <section id="localizacao" class="fade-in-section" style="background: linear-gradient(135deg, var(--burgundy-dark) 0%, var(--burgundy) 100%);">
        <div class="container">
            <h2 class="section-title">Onde Estamos</h2>
            <div class="map-container">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3718.9549633581555!2d-8.5703455!3d40.753472699999996!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xd239c78582a88fb%3A0xe64810ad61360cd7!2sBar%20da%20Tom%C3%A1zia%20-%20Estarreja!5e1!3m2!1spt-PT!2spt!4v1760017757627!5m2!1spt-PT!2spt" 
                        width="100%" 
                        height="450" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy">
                </iframe>
            </div>
            <div class="text-center mt-5">
                <p class="lead" style="font-weight: 600; color: var(--gold); margin-bottom: 0.5rem;">
                    <i class="fas fa-map-marker-alt"></i> Bar da Tomazia - Estarreja
                </p>
                <p style="color: var(--text-secondary);">Visite-nos e desfrute de um ambiente único</p>
            </div>
        </div>
    </section>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Mobile Menu Toggle
        const menuToggle = document.querySelector('.mobile-menu-toggle');
        const navSidebar = document.querySelector('.nav-sidebar');
        
        menuToggle.addEventListener('click', () => {
            menuToggle.classList.toggle('active');
            navSidebar.classList.toggle('active');
        });

        document.querySelectorAll('.nav-item').forEach(link => {
            link.addEventListener('click', () => {
                menuToggle.classList.remove('active');
                navSidebar.classList.remove('active');
            });
        });

        // WiFi Toggle
        function toggleWiFi() {
            const wifiInfo = document.getElementById('wifiInfo');
            wifiInfo.style.display = wifiInfo.style.display === 'none' || wifiInfo.style.display === '' ? 'block' : 'none';
        }

        // Copy WiFi Password
        function copyWifiPassword() {
            const password = document.getElementById('wifiPassword').innerText;
            const copyIcon = document.getElementById('copyIcon');
            const checkIcon = document.getElementById('checkIcon');
            
            navigator.clipboard.writeText(password).then(() => {
                copyIcon.style.display = 'none';
                checkIcon.style.display = 'inline-block';
                setTimeout(() => {
                    copyIcon.style.display = 'inline-block';
                    checkIcon.style.display = 'none';
                }, 2000);
            });
        }

        // Smooth Scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        // Scroll Animation Observer
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.fade-in-section').forEach(section => {
            observer.observe(section);
        });
    </script>
</body>
</html>