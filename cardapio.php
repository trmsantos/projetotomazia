<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['nome'])) {
    header('Location: /erro');
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
    <link rel="icon" href="/img/tomazia.png" type="image/png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Inter:wght@400;500;600;700&family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #5D1F3A;
            color: #f0f0f0;
            font-family: 'Inter', 'Montserrat', -apple-system, BlinkMacSystemFont, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        .header-cardapio {
            background-color: #3D0F24;
            padding: 2.5rem 1rem;
            text-align: center;
            border-bottom: 2px solid rgba(212, 175, 55, 0.25);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }
        .header-cardapio h1 {
            color: #D4AF37;
            font-family: 'Playfair Display', serif;
            font-size: 3.25rem;
            font-weight: 900;
            margin: 0;
            letter-spacing: -0.03em;
        }
        .header-cardapio a {
            position: absolute;
            top: 2.75rem;
            left: 1.75rem;
            color: #D4AF37;
            font-size: 1.25rem;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .header-cardapio a:hover {
            color: #fff;
            transform: translateX(-4px);
        }
        .category-nav-wrapper {
            background-color: #5D1F3A;
            padding: 1.25rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 2px solid rgba(212, 175, 55, 0.2);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        .category-nav {
            display: flex;
            overflow-x: auto;
            scrollbar-width: none; 
            -ms-overflow-style: none;  
            padding: 0.5rem 1rem;
        }
        .category-nav::-webkit-scrollbar {
            display: none;
        }
        .category-btn {
            background: transparent;
            border: 2px solid rgba(212, 175, 55, 0.4);
            color: #D4AF37;
            padding: 0.75rem 1.5rem;
            border-radius: 32px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 600;
            white-space: nowrap;
            margin-right: 1rem;
            font-size: 1rem;
            letter-spacing: 0.02em;
        }
        .category-btn:hover, .category-btn.active {
            background: #D4AF37;
            color: #3D0F24;
            border-color: #D4AF37;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(212, 175, 55, 0.3);
        }
        .menu-section {
            display: none;
            padding: 3rem 1rem;
            animation: fadeIn 0.5s ease-in-out;
        }
        .menu-section.active {
            display: block;
        }
        .welcome-message {
            text-align: center;
            padding: 5rem 1rem;
        }
        .welcome-message h2 {
            font-family: 'Playfair Display', serif;
            color: #D4AF37;
            font-size: 2.75rem;
            font-weight: 900;
            margin-bottom: 1.5rem;
            letter-spacing: -0.02em;
        }
        .welcome-message p {
            font-size: 1.25rem;
            line-height: 1.7;
        }
        .menu-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }
        .menu-item {
            background-color: #3D0F24;
            border: 1px solid rgba(212, 175, 55, 0.15);
            border-radius: 16px;
            padding: 2rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }
        .menu-item:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 28px rgba(0,0,0,0.4), 0 4px 12px rgba(212, 175, 55, 0.2);
            border-color: rgba(212, 175, 55, 0.3);
        }
        .item-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid rgba(212, 175, 55, 0.2);
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }
        .item-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            color: #f0f0f0;
            font-weight: 700;
            letter-spacing: -0.01em;
        }
        .item-price {
            font-weight: 700;
            font-size: 1.375rem;
            color: #D4AF37;
            white-space: nowrap;
            padding-left: 1.25rem;
        }
        .item-description {
            color: #b0b0b0;
            font-size: 1rem;
            line-height: 1.7;
        }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeOut { from { opacity: 1; } to { opacity: 0; } }
        .menu-section.fade-out { animation: fadeOut 0.3s ease-out forwards; }
        
        /* Media Queries para responsividade */
        @media (min-width: 768px) {
            .menu-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (min-width: 992px) {
            .menu-grid { grid-template-columns: repeat(3, 1fr); }
        }
    </style>
</head>
<body>
    <header class="header-cardapio">
        <a href="bemvindo.php">← Voltar</a>
        <h1>Cardápio</h1>
    </header>

    <div class="category-nav-wrapper">
        <div class="category-nav">
            <button class="category-btn active" onclick="switchCategory('welcome-message')">Início</button>
            <?php foreach ($categories as $category): ?>
                <button class="category-btn" onclick="switchCategory('section-<?php echo htmlspecialchars($category); ?>')"><?php echo ucfirst(htmlspecialchars($category)); ?></button>
            <?php endforeach; ?>
        </div>
    </div>

    <main class="container-fluid">
        <div id="welcome-message" class="menu-section active welcome-message">
            <h2>Bem-vindo ao nosso Cardápio!</h2>
            <p class="text-white-50">Selecione uma categoria acima para ver as nossas opções.</p>
            <img src="/img/tomazia.png" alt="Bar da Tomazia" style="max-width: 250px; margin-top: 2rem; opacity: 0.8;">
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
                                <span class="item-name"><?php echo htmlspecialchars($item['nome_prod']); ?></span>
                                <span class="item-price"><?php echo htmlspecialchars(number_format($item['preco'], 2, ',', '.')); ?> €</span>
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
        let currentSectionId = 'welcome-message';

        function switchCategory(targetId) {
            if (currentSectionId === targetId) return;

            const currentActiveButton = document.querySelector('.category-btn.active');
            const targetButton = document.querySelector(`button[onclick="switchCategory('${targetId}')"]`);
            
            if (currentActiveButton) currentActiveButton.classList.remove('active');
            if (targetButton) targetButton.classList.add('active');

            const currentSection = document.getElementById(currentSectionId);
            const targetSection = document.getElementById(targetId);

            if (currentSection) {
                currentSection.classList.add('fade-out');
                currentSection.addEventListener('animationend', () => {
                    currentSection.classList.remove('active', 'fade-out');
                    if (targetSection) {
                        targetSection.classList.add('active');
                    }
                    currentSectionId = targetId;
                }, { once: true });
            } else if (targetSection) {
                targetSection.classList.add('active');
                currentSectionId = targetId;
            }
        }
    </script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>