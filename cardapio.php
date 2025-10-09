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
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #1a1a1a;
            color: #f0f0f0;
            font-family: 'Montserrat', sans-serif;
        }
        .header-cardapio {
            background-color: #1e1e1e;
            padding: 2rem 1rem;
            text-align: center;
            border-bottom: 1px solid rgba(212, 175, 55, 0.2);
        }
        .header-cardapio h1 {
            color: #D4AF37;
            font-family: 'Playfair Display', serif;
            font-size: 2.8rem;
            margin: 0;
        }
        .header-cardapio a {
            position: absolute;
            top: 2.5rem;
            left: 1.5rem;
            color: #D4AF37;
            font-size: 1.2rem;
            text-decoration: none;
        }
        .header-cardapio a:hover {
            color: #fff;
        }
        .category-nav-wrapper {
            background-color: #1a1a1a;
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid rgba(212, 175, 55, 0.2);
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
            border: 1px solid rgba(212, 175, 55, 0.5);
            color: #D4AF37;
            padding: 0.6rem 1.2rem;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            white-space: nowrap;
            margin-right: 0.8rem;
        }
        .category-btn:hover, .category-btn.active {
            background: #D4AF37;
            color: #1a1a1a;
            border-color: #D4AF37;
        }
        .menu-section {
            display: none;
            padding: 2rem 1rem;
            animation: fadeIn 0.6s ease-in-out;
        }
        .menu-section.active {
            display: block;
        }
        .welcome-message {
            text-align: center;
            padding: 4rem 1rem;
        }
        .welcome-message h2 {
            font-family: 'Playfair Display', serif;
            color: #D4AF37;
        }
        .menu-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        .menu-item {
            background-color: #1e1e1e;
            border: 1px solid rgba(212, 175, 55, 0.1);
            border-radius: 10px;
            padding: 1.5rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .menu-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
        }
        .item-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 1px solid rgba(212, 175, 55, 0.2);
            padding-bottom: 0.8rem;
            margin-bottom: 0.8rem;
        }
        .item-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            color: #f0f0f0;
            font-weight: 700;
        }
        .item-price {
            font-weight: 600;
            font-size: 1.3rem;
            color: #D4AF37;
            white-space: nowrap;
            padding-left: 1rem;
        }
        .item-description {
            color: #a0a0a0;
            font-size: 0.95rem;
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
            <img src="img/tomazia.png" alt="Bar da Tomazia" style="max-width: 250px; margin-top: 2rem; opacity: 0.8;">
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