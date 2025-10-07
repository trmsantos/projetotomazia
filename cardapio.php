<?php
session_start();

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

$db = new SQLite3(__DIR__ . '/bd/bd_teste.db');

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
    <style>
        body {
            display: flex;
            flex-direction: column;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }
        .sidebar {
            width: 100%;
            background-color: #f8f9fa;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .sidebar img {
            max-width: 100%;
            height: auto;
            margin-bottom: 20px;
            transition: opacity 0.3s ease;
        }
        .sidebar img:hover {
            opacity: 0.7;
        }
        .sidebar a {
            display: block;
            padding: 10px;
            color: #333;
            text-decoration: none;
            font-weight: bold;
            text-align: center;
            width: 100%;
        }
        .sidebar a:hover {
            background-color: #ddd;
        }
        .content {
            flex: 1;
            padding: 20px;
        }
        .menu-section {
            margin-bottom: 40px;
            display: none; 
        }
        .menu-list {
            list-style: none;
            padding: 0;
        }
        .menu-list li {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .menu-list li:last-child {
            border-bottom: none;
        }
        .menu-list .item-name {
            font-weight: bold;
        }
        .menu-list .item-price {
            float: right;
            font-weight: bold;
        }
        @media (max-width: 768px) {
            .menu-list li {
                font-size: 14px;
                padding: 8px;
            }
            .sidebar {
                width: 100%;
                padding: 10px;
            }
            .sidebar a {
                padding: 8px;
                font-size: 14px;
            }
            .content {
                padding: 10px;
            }
        }
        .section-title {
            color: #A52A2A;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <a href="index.php">
            <img src="img/tomazia.png" href ="index.php" alt="Tomazia">
        </a>
        <?php foreach ($categories as $category): ?>
            <a href="#section-<?php echo htmlspecialchars($category); ?>" onclick="showCategory('<?php echo htmlspecialchars($category); ?>')"><?php echo ucfirst(htmlspecialchars($category)); ?></a>
        <?php endforeach; ?>
    </div>
    <div class="content">
        <div class="container">
            <?php
            // Fetch items for each category and display them
            foreach ($categories as $category) {
                $items = fetchMenuItems($db, $category);
                echo '<div class="menu-section" id="section-' . htmlspecialchars($category) . '">';
                echo '<h2 class="section-title">' . ucfirst(htmlspecialchars($category)) . '</h2>';
                echo '<ul class="menu-list">';
                foreach ($items as $item) {
                    echo '<li>';
                    echo '<span class="item-name">' . htmlspecialchars($item['nome_prod']) . '</span>';
                    echo '<span class="item-price">' . htmlspecialchars($item['preco']) . ' €</span>';
                    echo '</li>';
                }
                echo '</ul>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
    <script>
        function showCategory(category) {
            // Esconder todas as seções do menu
            var sections = document.querySelectorAll('.menu-section');
            sections.forEach(function(section) {
                section.style.display = 'none';
            });

            // Mostrar a seção selecionada
            var selectedSection = document.getElementById('section-' + category);
            if (selectedSection) {
                selectedSection.style.display = 'block';
            }

            // Esconder a mensagem de boas-vindas
            document.getElementById('welcome-message').style.display = 'none';
        }
    </script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>