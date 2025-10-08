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

function fetchMenuItems($db, $category) {
    $query = $db->prepare('SELECT * FROM produtos WHERE tipo = :tipo');
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
} catch (Exception $e) {
    error_log("Error in cardapio.php: " . $e->getMessage());
    die("Erro ao carregar o cardápio.");
}

$query = $db->query('SELECT DISTINCT tipo FROM produtos');
$categories = [];
while ($row = $query->fetchArray(SQLITE3_ASSOC)) {
    $categories[] = $row['tipo'];
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cardápio - Bar da Tomazia</title>
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
            background-color: #f8f9fa;
        }

        /* Header */
        .menu-header {
            background: linear-gradient(135deg, #A52A2A 0%, #8B0000 100%);
            color: white;
            padding: 30px 0;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .menu-header h1 {
            margin: 0;
            font-size: 2.5rem;
        }

        .menu-header p {
            margin: 10px 0 0;
            font-size: 1.1rem;
        }

        .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: background 0.3s;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            text-decoration: none;
        }

        /* Category Navigation */
        .category-nav {
            background: white;
            padding: 20px 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .category-nav .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
        }

        .category-btn {
            background: #f8f9fa;
            border: 2px solid #A52A2A;
            color: #A52A2A;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: bold;
        }

        .category-btn:hover,
        .category-btn.active {
            background: #A52A2A;
            color: white;
        }

        /* Menu Content */
        .menu-content {
            padding: 40px 0;
        }

        .menu-section {
            display: none;
            animation: fadeIn 0.5s;
        }

        .menu-section.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .section-title {
            color: #A52A2A;
            font-size: 2rem;
            margin-bottom: 30px;
            text-align: center;
            font-weight: bold;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .menu-item {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .menu-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .item-name {
            font-weight: bold;
            font-size: 1.1rem;
            color: #333;
        }

        .item-price {
            font-weight: bold;
            font-size: 1.2rem;
            color: #A52A2A;
        }

        .welcome-message {
            text-align: center;
            padding: 60px 20px;
        }

        .welcome-message h2 {
            color: #A52A2A;
            font-size: 2.5rem;
            margin-bottom: 20px;
        }

        .welcome-message p {
            font-size: 1.2rem;
            color: #666;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .menu-header h1 {
                font-size: 1.8rem;
            }

            .menu-grid {
                grid-template-columns: 1fr;
            }

            .category-nav .container {
                flex-direction: column;
            }

            .category-btn {
                width: 100%;
            }

            .back-btn {
                position: static;
                display: block;
                margin: 10px auto;
                width: fit-content;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="menu-header">
        <a href="bemvindo.php" class="back-btn">← Voltar</a>
        <h1>Cardápio Digital</h1>
        <p>Bar da Tomazia</p>
    </div>

    <!-- Category Navigation -->
    <div class="category-nav">
        <div class="container">
            <button class="category-btn active" onclick="showWelcome()">Início</button>
            <?php foreach ($categories as $category): ?>
                <button class="category-btn" onclick="showCategory('<?php echo htmlspecialchars($category); ?>')"><?php echo ucfirst(htmlspecialchars($category)); ?></button>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Menu Content -->
    <div class="menu-content">
        <div class="container">
            <!-- Welcome Message -->
            <div id="welcome-message" class="welcome-message">
                <h2>Bem-vindo ao nosso Cardápio!</h2>
                <p>Selecione uma categoria acima para ver as nossas opções.</p>
                <img src="img/tomazia.png" alt="Bar da Tomazia" style="max-width: 300px; margin-top: 30px;">
            </div>

            <?php
            // Fetch items for each category and display them
            foreach ($categories as $category) {
                $items = fetchMenuItems($db, $category);
                echo '<div class="menu-section" id="section-' . htmlspecialchars($category) . '">';
                echo '<h2 class="section-title">' . ucfirst(htmlspecialchars($category)) . '</h2>';
                echo '<div class="menu-grid">';
                foreach ($items as $item) {
                    echo '<div class="menu-item">';
                    echo '<span class="item-name">' . htmlspecialchars($item['nome_prod']) . '</span>';
                    echo '<span class="item-price">' . htmlspecialchars($item['preco']) . ' €</span>';
                    echo '</div>';
                }
                echo '</div>';
                echo '</div>';
            }
            ?>
        </div>
    </div>

    <script>
        function showCategory(category) {
            // Remove active class from all buttons
            document.querySelectorAll('.category-btn').forEach(btn => {
                btn.classList.remove('active');
            });

            // Hide all sections
            document.querySelectorAll('.menu-section').forEach(section => {
                section.classList.remove('active');
            });

            // Hide welcome message
            document.getElementById('welcome-message').style.display = 'none';

            // Show selected section
            const selectedSection = document.getElementById('section-' + category);
            if (selectedSection) {
                selectedSection.classList.add('active');
            }

            // Mark clicked button as active
            event.target.classList.add('active');
        }

        function showWelcome() {
            // Remove active class from all buttons
            document.querySelectorAll('.category-btn').forEach(btn => {
                btn.classList.remove('active');
            });

            // Hide all sections
            document.querySelectorAll('.menu-section').forEach(section => {
                section.classList.remove('active');
            });

            // Show welcome message
            document.getElementById('welcome-message').style.display = 'block';

            // Mark first button as active
            document.querySelector('.category-btn').classList.add('active');
        }
    </script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>