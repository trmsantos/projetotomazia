<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

try {
    $db = getDbConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['logout'])) {
        // Verificar token CSRF
        if (!isset($_POST[CSRF_TOKEN_NAME]) || !verifyCsrfToken($_POST[CSRF_TOKEN_NAME])) {
            die("Erro: Token CSRF inválido.");
        }

        if (isset($_POST['nome_prod'], $_POST['preco'], $_POST['tipo'])) {
            $nome_prod = htmlspecialchars($_POST['nome_prod']);
            $preco = $_POST['preco'];
            $tipo = htmlspecialchars($_POST['tipo']);

            if ($preco >= 0) {
                if (isset($_POST['id_produto'])) {
                    // Atualizar produto existente
                    $id_produto = $_POST['id_produto'];
                    $stmt = $db->prepare('UPDATE produtos SET nome_prod = :nome_prod, preco = :preco, tipo = :tipo WHERE id_produto = :id_produto');
                    $stmt->bindValue(':id_produto', $id_produto, SQLITE3_INTEGER);
                } else {
                    // Inserir novo produto
                    $stmt = $db->prepare('INSERT INTO produtos (nome_prod, preco, tipo) VALUES (:nome_prod, :preco, :tipo)');
                }
                $stmt->bindValue(':nome_prod', $nome_prod, SQLITE3_TEXT);
                $stmt->bindValue(':preco', $preco, SQLITE3_FLOAT);
                $stmt->bindValue(':tipo', $tipo, SQLITE3_TEXT);
                $stmt->execute();
            } else {
                echo "<script>alert('O preço não pode ser negativo');</script>";
            }
        } else {
            echo "<script>alert('Produto Eliminado Com Sucesso!');</script>";
        }
    }

    if (isset($_POST['delete'])) {
        // Verificar token CSRF
        if (!isset($_POST[CSRF_TOKEN_NAME]) || !verifyCsrfToken($_POST[CSRF_TOKEN_NAME])) {
            die("Erro: Token CSRF inválido.");
        }
        
        $id_produto = $_POST['id_produto'];
        $stmt = $db->prepare('DELETE FROM produtos WHERE id_produto = :id_produto');
        $stmt->bindValue(':id_produto', $id_produto, SQLITE3_INTEGER);
        $stmt->execute();
    }
} catch (Exception $e) {
    error_log("Error in admin.php: " . $e->getMessage());
    die("Erro: Ocorreu um problema. Por favor, tente novamente.");
}

$edit_nome_prod = $edit_preco = $edit_tipo = '';
if (isset($_GET['edit']) || isset($_GET['delete'])) {
    $id_produto = isset($_GET['edit']) ? $_GET['edit'] : $_GET['delete'];
    $stmt = $db->prepare('SELECT * FROM produtos WHERE id_produto = :id_produto');
    $stmt->bindValue(':id_produto', $id_produto, SQLITE3_INTEGER);
    $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    $edit_nome_prod = $result['nome_prod'];
    $edit_preco = $result['preco'];
    $edit_tipo = $result['tipo'];
}

// Obter anos disponíveis na base de dados
$years = [];
$stmt = $db->prepare('SELECT DISTINCT strftime("%Y", data_registro) as year FROM tomazia_clientes ORDER BY year');
$result = $stmt->execute();
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $years[] = $row['year'];
}

// Obter dados de adesão para o gráfico
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$data = [];
$total = 0;
$debugInfo = [];

for ($i = 1; $i <= 12; $i++) {
    $stmt = $db->prepare('SELECT COUNT(user_id) as count FROM tomazia_clientes WHERE strftime("%Y", data_registro) = :year AND strftime("%m", data_registro) = :month');
    $stmt->bindValue(':year', $year, SQLITE3_TEXT);
    $stmt->bindValue(':month', str_pad($i, 2, '0', STR_PAD_LEFT), SQLITE3_TEXT);
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);
    $count = $row ? $row['count'] : 0;
    $data[] = $count;
    $total += $count;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Bar da Tomazia</title>
    <link rel="icon" href="img/tomazia.png" type="image/png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .navbar {
            margin-bottom: 20px;
        }
        .container {
            display: flex;
        }
        .sidebar {
            width: 200px;
            background-color:#A52A2A;
            padding: 15px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .sidebar img {
            width: 150px;
            margin-bottom: 20px;
            background-color: transparent;
        }
        .sidebar a {
            color: white;
            display: block;
            padding: 10px;
            text-decoration: none;
            width: 100%;
            text-align: center;
        }
        .sidebar a:hover {
            background-color:lightgray;
            color:#A52A2A;
        }
        .content {
            margin-left: 220px;
            padding: 20px;
            flex: 1;
        }
        .section {
            display: none;
        }
        .section.active {
            display: block;
        }
        .form-section, .chart-section, .table-section {
            margin: 10px 0;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .no-products {
            color: red;
            font-weight: bold;
        }

        .btn-primary {
            background-color:#A52A2A;
            border-color: #A52a2a;
        }

        .btn-primary:hover {
            background-color:lightgray;
            color:#A52A2A;
            border-color: #A52A2A;
        }

        .chart-container {
            position: relative;
            height: 500px; 
            width: 100%;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <form method="POST" class="ml-auto">
        <button type="submit" name="logout" class="btn btn-primary">Logout</button>
    </form>
</nav>

<div class="container">
    <div class="sidebar">
        <img src="img/tomazia.png" alt="Tomazia">
        <a href="#" onclick="showSection('insert')">Inserir</a>
        <a href="#" onclick="showSection('manage')">Gerir</a>
        <a href="#" onclick="showSection('adherence')">Adesão</a>
    </div>
    <div class="content">
        <!-- Formulário para Inserir Dados -->
        <div id="insert" class="section form-section">
            <h2>Inserir Novo Produto</h2>
            <form id="productForm" method="POST" action="admin.php">
                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo generateCsrfToken(); ?>">
                <div class="form-group">
                    <label for="nome_prod">Nome do Produto</label>
                    <input type="text" class="form-control" id="nome_prod" name="nome_prod" required>
                </div>
                <div class="form-group">
                    <label for="preco">Preço</label>
                    <input type="number" step="0.01" class="form-control" id="preco" name="preco" required min="0">
                </div>
                <div class="form-group">
                    <label for="tipo">Tipo</label>
                    <select class="form-control" id="tipo" name="tipo" required>
                        <option value="espirituosas">Espirituosas</option>
                        <option value="licores">Licores</option>
                        <option value="portos">Portos</option>
                        <option value="gin">Gin</option>
                        <option value="bebidas">Bebidas</option>
                        <option value="cafetaria">Cafetaria</option>
                        <option value="comidas">Comidas</option>
                        <option value="aguardentes">Aguardentes</option>
                        <option value="brandy">Brandy</option>
                        <option value="martinis">Martinis</option>
                        <option value="whiskys">Whisky's</option>
                        <option value="bebidas">Bebidas</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Inserir</button>
            </form>
        </div>

        <!-- Formulário para Editar/Eliminar Dados -->
        <div id="edit" class="section form-section">
            <h2><?php echo isset($_GET['edit']) ? 'Editar Produto' : 'Eliminar Produto'; ?></h2>
            <form id="editForm" method="POST" action="admin.php">
                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo generateCsrfToken(); ?>">
                <input type="hidden" name="id_produto" value="<?php echo htmlspecialchars($id_produto); ?>">
                <div class="form-group">
                    <label for="nome_prod">Nome do Produto</label>
                    <input type="text" class="form-control" id="nome_prod" name="nome_prod" value="<?php echo htmlspecialchars($edit_nome_prod); ?>" <?php echo isset($_GET['delete']) ? 'readonly' : ''; ?> required>
                </div>
                <div class="form-group">
                    <label for="preco">Preço</label>
                    <input type="number" step="0.01" class="form-control" id="preco" name="preco" value="<?php echo htmlspecialchars($edit_preco); ?>" <?php echo isset($_GET['delete']) ? 'readonly' : ''; ?> required min="0">
                </div>
                <div class="form-group">
                    <label for="tipo">Tipo</label>
                    <select class="form-control" id="tipo" name="tipo" <?php echo isset($_GET['delete']) ? 'disabled' : ''; ?> required>
                        <option value="espirituosas" <?php echo $edit_tipo == 'espirituosas' ? 'selected' : ''; ?>>Espirituosas</option>
                        <option value="licores" <?php echo $edit_tipo == 'licores' ? 'selected' : ''; ?>>Licores</option>
                        <option value="portos" <?php echo $edit_tipo == 'portos' ? 'selected' : ''; ?>>Portos</option>
                        <option value="gin" <?php echo $edit_tipo == 'gin' ? 'selected' : ''; ?>>Gin</option>
                        <option value="bebidas" <?php echo $edit_tipo == 'bebidas' ? 'selected' : ''; ?>>Bebidas</option>
                        <option value="cafetaria" <?php echo $edit_tipo == 'cafetaria' ? 'selected' : ''; ?>>Cafetaria</option>
                        <option value="comidas" <?php echo $edit_tipo == 'comidas' ? 'selected' : ''; ?>>Comidas</option>
                        <option value="aguardentes" <?php echo $edit_tipo == 'aguardentes' ? 'selected' : ''; ?>> Aguardentes </option>
                        <option value="brandy" <?php echo $edit_tipo == 'brandy' ? 'selected' : ''; ?>> Brandy </option>
                        <option value="martinis" <?php echo $edit_tipo == 'martinis' ? 'selected' : ''; ?>> Martinis </option>
                        <option value="whiskys" <?php echo $edit_tipo == 'whiskys' ? 'selected' : ''; ?>> Whisky's</option>
                        <option value="bebidas" <?php echo $edit_tipo == 'bebidas' ? 'selected' : ''; ?>> Bebidas</option>
                    </select>
                </div>
                <?php if (isset($_GET['edit'])): ?>
                    <button type="submit" class="btn btn-primary">Atualizar</button>
                <?php elseif (isset($_GET['delete'])): ?>
                    <button type="submit" name="delete" class="btn btn-danger">Eliminar</button>
                <?php endif; ?>
            </form>
        </div>

        <!-- Tabela de Produtos -->
        <div id="manage" class="section table-section">
            <h2>Produtos</h2>
            <div class="form-group">
                <label for="filter_tipo">Filtrar por Tipo</label>
                <select class="form-control" id="filter_tipo" name="filter_tipo" onchange="filterProducts()">
                    <option value="">Todos</option>
                    <option value="espirituosas">Espirituosas</option>
                    <option value="licores">Licores</option>
                    <option value="portos">Portos</option>
                    <option value="gin">Gin</option>
                    <option value="bebidas">Bebidas</option>
                    <option value="cafetaria">Cafetaria</option>
                    <option value="comidas">Comidas</option>
                    <option value="aguardentes">Aguardentes</option>
                    <option value="brandy">Brandy</option>
                    <option value="martinis">Martinis</option>
                    <option value="whiskys">Whisky's</option>
                    <option value="bebidas">Bebidas</option>
                </select>
            </div>
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Preço</th>
                        <th>Tipo</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="productTableBody">
                    <?php
                    $filter_tipo = isset($_GET['filter_tipo']) ? $_GET['filter_tipo'] : '';
                    $query = 'SELECT * FROM produtos';
                    if ($filter_tipo) {
                        $query .= ' WHERE tipo = :filter_tipo';
                    }
                    $stmt = $db->prepare($query);
                    if ($filter_tipo) {
                        $stmt->bindValue(':filter_tipo', $filter_tipo, SQLITE3_TEXT);
                    }
                    $result = $stmt->execute();
                    $hasProducts = false;
                    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                        $hasProducts = true;
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($row['id_produto']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['nome_prod']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['preco']) . '€</td>';
                        echo '<td>' . htmlspecialchars($row['tipo']) . '</td>';
                        echo '<td>';
                        echo '<a href="admin.php?edit=' . htmlspecialchars($row['id_produto']) . '" class="btn btn-warning btn-sm">Editar</a> ';
                        echo '<a href="admin.php?delete=' . htmlspecialchars($row['id_produto']) . '" class="btn btn-danger btn-sm">Eliminar</a>';
                        echo '</td>';
                        echo '</tr>';
                    }
                    if (!$hasProducts) {
                        echo '<tr><td colspan="5" class="no-products">Não existem produtos para a filtragem aplicada</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>

   
        <!-- Gráfico de Aderência -->
        <div id="adherence" class="section chart-section">
            <h2>Pessoas que Aderiram ao Website</h2>
            <div class="form-group">
                <label for="yearSelect">Selecionar Ano</label>
                <select class="form-control" id="yearSelect" onchange="updateChart()">
                    <?php
                    foreach ($years as $availableYear) {
                        echo '<option value="' . htmlspecialchars($availableYear) . '"' . ($availableYear == $year ? ' selected' : '') . '>' . htmlspecialchars($availableYear) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="chart-container">
                <canvas id="adherenceChart"></canvas>
            </div>
            <div id="totalAdherence" class="mt-3">
                <h4>Total de Pessoas que Aderiram: <span id="totalAdherenceCount"><?php echo $total; ?></span></h4>
            </div>
        </div>
    </div>
</div>

<script>
function showSection(sectionId) {
    const sections = document.querySelectorAll('.section');
    sections.forEach(section => {
        section.classList.remove('active');
    });
    document.getElementById(sectionId).classList.add('active');
}

// Mostrar a seção "Gerir" por padrão se houver filtragem, caso contrário, "Inserir"
document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('filter_tipo')) {
        showSection('manage');
        document.getElementById('filter_tipo').value = urlParams.get('filter_tipo');
    } else if (urlParams.has('edit') || urlParams.has('delete')) {
        showSection('edit');
    } else if (urlParams.has('year')) {
        showSection('adherence');
    } else {
        showSection('insert');
    }

    const ctx = document.getElementById('adherenceChart').getContext('2d');
    const adherenceChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
            datasets: [{
                label: 'Número de Pessoas',
                data: <?php echo json_encode($data); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    window.updateChart = function() {
        const year = document.getElementById('yearSelect').value;
        window.location.href = 'admin.php?year=' + year + '#adherence';
    }
});

function filterProducts() {
    const filterTipo = document.getElementById('filter_tipo').value;
    window.location.href = 'admin.php?filter_tipo=' + filterTipo + '#manage';
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>