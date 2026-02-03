<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['nome'])) {
    header('Location: erro.php');
    exit();
}

$nome = $_SESSION['nome'];

function fetchMenuItems($db, $category) {
    $query = $db->prepare('SELECT * FROM produtos WHERE tipo = :tipo ORDER BY nome_prod ASC');
    $query->bindValue(':tipo', $category, SQLITE3_TEXT);
    $result = $query->execute();
    $items = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $items[] = $row;
    }
    return $items;
}

try {
    $db = getDbConnection();
    $query = $db->query('SELECT DISTINCT tipo FROM produtos');
    $categories = [];
    while ($row = $query->fetchArray(SQLITE3_ASSOC)) {
        $categories[] = $row['tipo'];
    }
} catch (Exception $e) {
    error_log("Error in cardapio.php: " . $e->getMessage());
    die("Erro ao carregar o cardápio.");
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cardápio - Bar da Tomazia</title>
    <link rel="icon" href="img/tomazia.png" type="image/png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600;700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --burgundy: #5D1F3A;
            --burgundy-dark: #3D0F24;
            --gold: #D4AF37;
            --gold-light: #E8C76F;
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
            font-family: 'DM Sans', sans-serif;
            background: linear-gradient(135deg, var(--burgundy) 0%, var(--burgundy-dark) 100%);
            color: var(--text-primary);
            min-height: 100vh;
        }

        /* Header */
        .header-menu {
            background: rgba(61, 15, 36, 0.95);
            backdrop-filter: blur(20px);
            padding: 2rem 0;
            border-bottom: 1px solid rgba(212, 175, 55, 0.25);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .back-btn {
            position: absolute;
            left: 1.5rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--gold);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem;
            border-radius: 8px;
        }

        .back-btn:hover {
            background: rgba(212, 175, 55, 0.15);
            color: var(--gold-light);
            text-decoration: none;
            transform: translateX(-4px);
        }

        .header-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(2rem, 5vw, 3rem);
            font-weight: 600;
            color: var(--gold);
            margin: 0;
        }

        /* Category Navigation */
        .category-nav-wrapper {
            background: rgba(93, 31, 58, 0.5);
            backdrop-filter: blur(15px);
            padding: 1.5rem 0;
            position: sticky;
            top: calc(2rem + 76px);
            z-index: 99;
            border-bottom: 1px solid rgba(212, 175, 55, 0.15);
        }

        .category-nav {
            display: flex;
            overflow-x: auto;
            gap: 1rem;
            padding: 0 1.5rem;
            scrollbar-width: none;
        }

        .category-nav::-webkit-scrollbar {
            display: none;
        }

        .category-btn {
            flex-shrink: 0;
            background: rgba(61, 15, 36, 0.6);
            border: 2px solid rgba(212, 175, 55, 0.3);
            color: var(--gold);
            padding: 0.875rem 1.75rem;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 600;
            font-size: 1rem;
            white-space: nowrap;
        }

        .category-btn:hover,
        .category-btn.active {
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
            color: var(--burgundy-dark);
            border-color: var(--gold);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(212, 175, 55, 0.3);
        }

        /* Menu Section */
        .menu-section {
            display: none;
            padding: 4rem 1.5rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .menu-section.active {
            display: block;
            animation: fadeInUp 0.5s ease-out;
        }

        .welcome-section {
            text-align: center;
            padding: 6rem 2rem;
            max-width: 700px;
            margin: 0 auto;
        }

        .welcome-section h2 {
            font-family: 'Cormorant Garamond', serif;
            color: var(--gold);
            font-size: clamp(2rem, 5vw, 3rem);
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        .welcome-section p {
            color: var(--text-secondary);
            font-size: 1.125rem;
            margin-bottom: 3rem;
        }

        .welcome-section img {
            max-width: 200px;
            opacity: 0.7;
            animation: float 3s ease-in-out infinite;
        }

        /* Menu Grid */
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 2rem;
        }

        .menu-item {
            background: rgba(61, 15, 36, 0.7);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-radius: 16px;
            padding: 2rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .menu-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(212, 175, 55, 0.05) 0%, transparent 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .menu-item:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.5);
            border-color: rgba(212, 175, 55, 0.4);
        }

        .menu-item:hover::before {
            opacity: 1;
        }

        .item-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            border-bottom: 2px solid rgba(212, 175, 55, 0.2);
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }

        .item-name {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            line-height: 1.3;
        }

        .item-price {
            font-size: 1.375rem;
            font-weight: 700;
            color: var(--gold);
            white-space: nowrap;
        }

        .item-description {
            color: var(--text-secondary);
            line-height: 1.6;
            font-size: 0.9375rem;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-15px);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
            }

            .back-btn {
                position: static;
            }

            .menu-grid {
                grid-template-columns: 1fr;
            }

            .category-nav-wrapper {
                top: calc(2rem + 130px);
            }
        }
    </style>
</head>
<body>
    <header class="header-menu">
        <div class="header-content">
            <a href="bemvindo.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Voltar
            </a>
            <h1 class="header-title">Cardápio</h1>
        </div>
    </header>

    <div class="category-nav-wrapper">
        <div class="category-nav">
            <button class="category-btn active" onclick="switchCategory('welcome')">
                <i class="fas fa-home"></i> Início
            </button>
            <?php foreach ($categories as $category): ?>
                <button class="category-btn" onclick="switchCategory('section-<?php echo htmlspecialchars($category); ?>')">
                    <?php echo ucfirst(htmlspecialchars($category)); ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <main>
        <div id="welcome" class="menu-section active welcome-section">
            <h2>Bem-vindo ao Nosso Cardápio</h2>
            <p>Selecione uma categoria acima para explorar nossa seleção</p>
            <img src="img/tomazia.png" alt="Bar da Tomazia">
        </div>

        <?php foreach ($categories as $category): ?>
            <div class="menu-section" id="section-<?php echo htmlspecialchars($category); ?>">
                <div class="menu-grid">
                    <?php
                    $items = fetchMenuItems($db, $category);
                    foreach ($items as $item):
                    ?>
                        <div class="menu-item">
                            <div class="item-header">
                                <h3 class="item-name"><?php echo htmlspecialchars($item['nome_prod']); ?></h3>
                                <span class="item-price"><?php echo number_format($item['preco'], 2, ',', '.'); ?>€</span>
                            </div>
                            <?php if (!empty($item['descricao'])): ?>
                                <p class="item-description"><?php echo htmlspecialchars($item['descricao']); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </main>

    <script>
        let currentSection = 'welcome';

        function switchCategory(targetId) {
            if (currentSection === targetId) return;

            // Update buttons
            document.querySelectorAll('.category-btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');

            // Switch sections
            const current = document.getElementById(currentSection);
            const target = document.getElementById(targetId);

            if (current) {
                current.style.display = 'none';
                current.classList.remove('active');
            }

            if (target) {
                target.style.display = 'block';
                setTimeout(() => target.classList.add('active'), 10);
            }

            currentSection = targetId;

            // Smooth scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Add stagger animation to menu items
        document.querySelectorAll('.menu-item').forEach((item, index) => {
            item.style.animationDelay = `${index * 0.05}s`;
        });
    </script>
</body>
</html>