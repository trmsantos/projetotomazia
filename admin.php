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

<<<<<<< HEAD
    // Produtos
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['form_type'] === 'produto') {
        if (!isset($_POST[CSRF_TOKEN_NAME]) || !verifyCsrfToken($_POST[CSRF_TOKEN_NAME])) {
            die("Erro: Token CSRF inválido.");
        }
        $nome_prod = trim($_POST['nome_prod'] ?? '');
        $preco = floatval($_POST['preco']);
        $tipo = trim($_POST['tipo'] ?? '');
        if ($nome_prod && $preco >= 0 && $tipo) {
            if (!empty($_POST['id_produto'])) {
                $id_produto = $_POST['id_produto'];
                $stmt = $db->prepare('UPDATE produtos SET nome_prod = :nome_prod, preco = :preco, tipo = :tipo WHERE id_produto = :id_produto');
                $stmt->bindValue(':id_produto', $id_produto, SQLITE3_INTEGER);
            } else {
                $stmt = $db->prepare('INSERT INTO produtos (nome_prod, preco, tipo) VALUES (:nome_prod, :preco, :tipo)');
            }
            $stmt->bindValue(':nome_prod', $nome_prod, SQLITE3_TEXT);
            $stmt->bindValue(':preco', $preco, SQLITE3_FLOAT);
            $stmt->bindValue(':tipo', $tipo, SQLITE3_TEXT);
            $stmt->execute();
        }
    }

    if (isset($_POST['delete_product'])) {
        if (!isset($_POST[CSRF_TOKEN_NAME]) || !verifyCsrfToken($_POST[CSRF_TOKEN_NAME])) die("Erro: Token CSRF inválido.");
=======
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
        
>>>>>>> 0101abea04030b4a6aef8064caa511191fdfc879
        $id_produto = $_POST['id_produto'];
        $stmt = $db->prepare('DELETE FROM produtos WHERE id_produto = :id_produto');
        $stmt->bindValue(':id_produto', $id_produto, SQLITE3_INTEGER);
        $stmt->execute();
    }

<<<<<<< HEAD
    // Eventos
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['form_type'] === 'evento') {
        if (!isset($_POST[CSRF_TOKEN_NAME]) || !verifyCsrfToken($_POST[CSRF_TOKEN_NAME])) die("Erro: Token CSRF inválido.");
        $nome_evento = trim($_POST['nome_evento'] ?? '');
        $data_evento = trim($_POST['data_evento'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        if ($nome_evento && $data_evento) {
            if (!empty($_POST['id_evento'])) {
                $id_evento = $_POST['id_evento'];
                $stmt = $db->prepare('UPDATE eventos SET nome_evento = :nome_evento, data_evento = :data_evento, descricao = :descricao WHERE id = :id');
                $stmt->bindValue(':id', $id_evento, SQLITE3_INTEGER);
            } else {
                $stmt = $db->prepare('INSERT INTO eventos (nome_evento, data_evento, descricao) VALUES (:nome_evento, :data_evento, :descricao)');
            }
            $stmt->bindValue(':nome_evento', $nome_evento, SQLITE3_TEXT);
            $stmt->bindValue(':data_evento', $data_evento, SQLITE3_TEXT);
            $stmt->bindValue(':descricao', $descricao, SQLITE3_TEXT);
            $stmt->execute();
            header('Location: admin.php#tab-eventos');
            exit;
        }
    }
    if (isset($_POST['delete_event'])) {
        if (!isset($_POST[CSRF_TOKEN_NAME]) || !verifyCsrfToken($_POST[CSRF_TOKEN_NAME])) die("Erro: Token CSRF inválido.");
=======
    // Gestão de Eventos
    if (isset($_POST['nome_evento'], $_POST['data_evento'])) {
        $nome_evento = htmlspecialchars($_POST['nome_evento']);
        $data_evento = htmlspecialchars($_POST['data_evento']);
        $descricao = htmlspecialchars($_POST['descricao'] ?? '');
        $imagem_url = htmlspecialchars($_POST['imagem_url'] ?? '');

        if (isset($_POST['id_evento'])) {
            // Atualizar evento existente
            $id_evento = $_POST['id_evento'];
            $stmt = $db->prepare('UPDATE eventos SET nome_evento = :nome_evento, data_evento = :data_evento, descricao = :descricao, imagem_url = :imagem_url WHERE id = :id');
            $stmt->bindValue(':id', $id_evento, SQLITE3_INTEGER);
        } else {
            // Inserir novo evento
            $stmt = $db->prepare('INSERT INTO eventos (nome_evento, data_evento, descricao, imagem_url) VALUES (:nome_evento, :data_evento, :descricao, :imagem_url)');
        }
        $stmt->bindValue(':nome_evento', $nome_evento, SQLITE3_TEXT);
        $stmt->bindValue(':data_evento', $data_evento, SQLITE3_TEXT);
        $stmt->bindValue(':descricao', $descricao, SQLITE3_TEXT);
        $stmt->bindValue(':imagem_url', $imagem_url, SQLITE3_TEXT);
        $stmt->execute();
    }

    if (isset($_POST['delete_event'])) {
>>>>>>> 0101abea04030b4a6aef8064caa511191fdfc879
        $id_evento = $_POST['id_evento'];
        $stmt = $db->prepare('DELETE FROM eventos WHERE id = :id');
        $stmt->bindValue(':id', $id_evento, SQLITE3_INTEGER);
        $stmt->execute();
<<<<<<< HEAD
        header('Location: admin.php#tab-eventos');
        exit;
=======
>>>>>>> 0101abea04030b4a6aef8064caa511191fdfc879
    }
} catch (Exception $e) {
    error_log("Error in admin.php: " . $e->getMessage());
    die("Erro: Ocorreu um problema. Por favor, tente novamente.");
}

<<<<<<< HEAD
// Edição produto/evento
$edit_product = null;
if (isset($_GET['edit_product'])) {
    $stmt = $db->prepare('SELECT * FROM produtos WHERE id_produto = :id');
    $stmt->bindValue(':id', $_GET['edit_product'], SQLITE3_INTEGER);
    $edit_product = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
}
$edit_event = null;
if (isset($_GET['edit_event'])) {
    $stmt = $db->prepare('SELECT * FROM eventos WHERE id = :id');
    $stmt->bindValue(':id', $_GET['edit_event'], SQLITE3_INTEGER);
    $edit_event = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
=======
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

// Variáveis para edição de eventos
$edit_nome_evento = $edit_data_evento = $edit_descricao = $edit_imagem_url = '';
if (isset($_GET['edit_event']) || isset($_GET['delete_event'])) {
    $id_evento = isset($_GET['edit_event']) ? $_GET['edit_event'] : $_GET['delete_event'];
    $stmt = $db->prepare('SELECT * FROM eventos WHERE id = :id');
    $stmt->bindValue(':id', $id_evento, SQLITE3_INTEGER);
    $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    if ($result) {
        $edit_nome_evento = $result['nome_evento'];
        $edit_data_evento = $result['data_evento'];
        $edit_descricao = $result['descricao'];
        $edit_imagem_url = $result['imagem_url'];
    }
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
>>>>>>> 0101abea04030b4a6aef8064caa511191fdfc879
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Bar da Tomazia</title>
    <link rel="icon" href="img/tomazia.png" type="image/png">
<<<<<<< HEAD
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        :root {
            --background-dark: #1a1a1a;
            --surface-dark: #232323;
            --primary-gold: #D4AF37;
            --text-light: #f0f0f0;
            --text-medium: #a0a0a0;
            --border-color: #333;
        }
        body { background: var(--background-dark); color: var(--text-light); font-family: 'Montserrat', Arial, sans-serif; }
        .navbar { background: var(--surface-dark); }
        .navbar-brand img { height: 60px; }
        .admin-main { max-width: 900px; margin: 40px auto 0 auto; }
        .tab-content { background: var(--surface-dark); border-radius: 12px; padding: 30px; box-shadow: 0 8px 32px #00000060; border: 1px solid var(--border-color);}
        .nav-tabs { border-bottom: none; margin-bottom: 0;}
        .nav-tabs .nav-link { color: var(--primary-gold); font-weight: 600; border-radius: 8px 8px 0 0; margin-right: 10px; background: none; border: none; }
        .nav-tabs .nav-link.active { background: var(--primary-gold); color: var(--background-dark);}
        .section-title { font-family: 'Playfair Display', serif; color: var(--primary-gold); font-size: 2rem; margin-bottom: 1.2rem;}
        .form-section { margin-bottom: 2rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1.5rem;}
        .form-control, .custom-select { background: var(--background-dark); color: var(--text-light); border: 1px solid var(--border-color);}
        .form-control:focus, .custom-select:focus { border-color: var(--primary-gold); box-shadow: none; }
        .btn-primary { background: var(--primary-gold); border-color: var(--primary-gold); color: var(--background-dark); font-weight: 600;}
        .btn-primary:hover { background: #c8a030; border-color: #c8a030;}
        .btn-outline-danger, .btn-outline-warning { border-radius: 4px; font-weight: 500;}
        .table { color: var(--text-light);}
        .table th { color: var(--primary-gold); border-top: none; }
        .table td, .table th { border-color: var(--border-color);}
        .table-hover tbody tr:hover { background: #2a2a2a; }
        .logout-form { text-align: right; margin-top: 20px;}
        @media (max-width: 900px) {
            .admin-main { max-width: 98vw; }
            .tab-content { padding: 18px;}
        }
        @media (max-width: 600px) {
            .admin-main { margin-top: 10px; }
            .form-section, .section-title { font-size: 1.2rem; }
            .tab-content { padding: 8px; }
=======
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
>>>>>>> 0101abea04030b4a6aef8064caa511191fdfc879
        }
    </style>
</head>
<body>
<<<<<<< HEAD
<nav class="navbar navbar-dark">
    <a class="navbar-brand" href="index.php"><img src="img/tomazia.png" alt="Tomazia"></a>
    <form method="POST" class="logout-form">
        <button type="submit" name="logout" class="btn btn-outline-danger">Logout</button>
    </form>
</nav>
<div class="admin-main">
    <ul class="nav nav-tabs" id="adminTab" role="tablist">
        <li class="nav-item"><a class="nav-link active" id="tab-produtos" data-toggle="tab" href="#produtos" role="tab">Produtos</a></li>
        <li class="nav-item"><a class="nav-link" id="tab-eventos" data-toggle="tab" href="#eventos" role="tab">Eventos</a></li>
    </ul>
    <div class="tab-content">
        <!-- Produtos -->
        <div class="tab-pane fade show active" id="produtos" role="tabpanel">
            <div class="form-section">
                <div class="section-title"><?php echo $edit_product ? 'Editar Produto' : 'Novo Produto'; ?></div>
                <form method="POST" action="admin.php">
                    <input type="hidden" name="form_type" value="produto">
                    <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo generateCsrfToken(); ?>">
                    <input type="hidden" name="id_produto" value="<?php echo $edit_product['id_produto'] ?? ''; ?>">
                    <div class="form-row">
                        <div class="form-group col-md-5"><input type="text" class="form-control" name="nome_prod" placeholder="Nome" value="<?php echo $edit_product['nome_prod'] ?? ''; ?>" required></div>
                        <div class="form-group col-md-3"><input type="number" step="0.01" class="form-control" name="preco" placeholder="Preço (€)" value="<?php echo $edit_product['preco'] ?? ''; ?>" required min="0"></div>
                        <div class="form-group col-md-4"><input type="text" class="form-control" name="tipo" placeholder="Tipo" value="<?php echo $edit_product['tipo'] ?? ''; ?>" required></div>
                    </div>
                    <button type="submit" class="btn btn-primary"><?php echo $edit_product ? 'Atualizar' : 'Adicionar'; ?></button>
                    <?php if ($edit_product): ?><a href="admin.php#produtos" class="btn btn-outline-warning ml-2">Cancelar</a><?php endif; ?>
                </form>
            </div>
            <div>
                <div class="section-title">Lista de Produtos</div>
                <table class="table table-hover">
                    <thead><tr><th>Nome</th><th>Preço</th><th>Tipo</th><th>Ações</th></tr></thead>
                    <tbody>
                        <?php
                        $result = $db->query('SELECT * FROM produtos ORDER BY tipo, nome_prod');
                        while ($row = $result->fetchArray(SQLITE3_ASSOC)):
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['nome_prod']); ?></td>
                            <td><?php echo htmlspecialchars(number_format($row['preco'], 2, ',', '.')); ?> €</td>
                            <td><?php echo htmlspecialchars($row['tipo']); ?></td>
                            <td>
                                <a href="admin.php?edit_product=<?php echo $row['id_produto']; ?>#produtos" class="btn btn-sm btn-outline-warning">Editar</a>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Eliminar este produto?');">
                                    <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo generateCsrfToken(); ?>">
                                    <input type="hidden" name="id_produto" value="<?php echo $row['id_produto']; ?>">
                                    <button type="submit" name="delete_product" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
=======
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <form method="POST" class="ml-auto">
        <button type="submit" name="logout" class="btn btn-primary">Logout</button>
    </form>
</nav>

<div class="container">
    <div class="sidebar">
        <img src="img/tomazia.png" alt="Tomazia">
        <a href="#" onclick="showSection('insert')">Inserir Produto</a>
        <a href="#" onclick="showSection('manage')">Gerir Produtos</a>
        <a href="#" onclick="showSection('events')">Gerir Eventos</a>
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

        <!-- Gestão de Eventos -->
        <div id="events" class="section">
            <h2>Gestão de Eventos</h2>
            
            <!-- Formulário para Inserir/Editar Eventos -->
            <div class="form-section mb-4">
                <h3><?php echo isset($_GET['edit_event']) ? 'Editar Evento' : 'Inserir Novo Evento'; ?></h3>
                <form method="POST" action="admin.php">
                    <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo generateCsrfToken(); ?>">
                    <?php if (isset($_GET['edit_event'])): ?>
                        <input type="hidden" name="id_evento" value="<?php echo htmlspecialchars($_GET['edit_event']); ?>">
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="nome_evento">Nome do Evento</label>
                        <input type="text" class="form-control" id="nome_evento" name="nome_evento" value="<?php echo htmlspecialchars($edit_nome_evento); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="data_evento">Data do Evento</label>
                        <input type="date" class="form-control" id="data_evento" name="data_evento" value="<?php echo htmlspecialchars($edit_data_evento); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="descricao">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="3"><?php echo htmlspecialchars($edit_descricao); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="imagem_url">URL da Imagem (opcional)</label>
                        <input type="text" class="form-control" id="imagem_url" name="imagem_url" value="<?php echo htmlspecialchars($edit_imagem_url); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary"><?php echo isset($_GET['edit_event']) ? 'Atualizar' : 'Inserir'; ?></button>
                    <?php if (isset($_GET['edit_event'])): ?>
                        <a href="admin.php#events" class="btn btn-secondary">Cancelar</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Tabela de Eventos -->
            <div class="table-section">
                <h3>Lista de Eventos</h3>
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nome do Evento</th>
                            <th>Data</th>
                            <th>Descrição</th>
                            <th>Imagem URL</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $db->prepare('SELECT * FROM eventos ORDER BY data_evento DESC');
                        $result = $stmt->execute();
                        $hasEvents = false;
                        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                            $hasEvents = true;
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($row['id']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['nome_evento']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['data_evento']) . '</td>';
                            echo '<td>' . htmlspecialchars(substr($row['descricao'], 0, 50)) . (strlen($row['descricao']) > 50 ? '...' : '') . '</td>';
                            echo '<td>' . htmlspecialchars($row['imagem_url']) . '</td>';
                            echo '<td>';
                            echo '<a href="admin.php?edit_event=' . htmlspecialchars($row['id']) . '#events" class="btn btn-warning btn-sm">Editar</a> ';
                            echo '<form method="POST" action="admin.php" style="display:inline;">';
                            echo '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . generateCsrfToken() . '">';
                            echo '<input type="hidden" name="id_evento" value="' . htmlspecialchars($row['id']) . '">';
                            echo '<button type="submit" name="delete_event" class="btn btn-danger btn-sm" onclick="return confirm(\'Tem certeza que deseja eliminar este evento?\')">Eliminar</button>';
                            echo '</form>';
                            echo '</td>';
                            echo '</tr>';
                        }
                        if (!$hasEvents) {
                            echo '<tr><td colspan="6" class="text-center">Não existem eventos cadastrados</td></tr>';
                        }
                        ?>
>>>>>>> 0101abea04030b4a6aef8064caa511191fdfc879
                    </tbody>
                </table>
            </div>
        </div>

<<<<<<< HEAD
        <!-- Eventos -->
        <div class="tab-pane fade" id="eventos" role="tabpanel">
            <div class="form-section">
                <div class="section-title"><?php echo $edit_event ? 'Editar Evento' : 'Novo Evento'; ?></div>
                <form method="POST" action="admin.php">
                    <input type="hidden" name="form_type" value="evento">
                    <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo generateCsrfToken(); ?>">
                    <input type="hidden" name="id_evento" value="<?php echo $edit_event['id'] ?? ''; ?>">
                    <div class="form-row">
                        <div class="form-group col-md-6"><input type="text" class="form-control" name="nome_evento" placeholder="Nome do Evento" value="<?php echo $edit_event['nome_evento'] ?? ''; ?>" required></div>
                        <div class="form-group col-md-3"><input type="date" class="form-control" name="data_evento" value="<?php echo $edit_event['data_evento'] ?? ''; ?>" required></div>
                        <div class="form-group col-md-3"><input type="text" class="form-control" name="descricao" placeholder="Descrição" value="<?php echo $edit_event['descricao'] ?? ''; ?>"></div>
                    </div>
                    <button type="submit" class="btn btn-primary"><?php echo $edit_event ? 'Atualizar' : 'Adicionar'; ?></button>
                    <?php if ($edit_event): ?><a href="admin.php#eventos" class="btn btn-outline-warning ml-2">Cancelar</a><?php endif; ?>
                </form>
            </div>
            <div>
                <div class="section-title">Lista de Eventos</div>
                <table class="table table-hover">
                    <thead><tr><th>Nome</th><th>Data</th><th>Descrição</th><th>Ações</th></tr></thead>
                    <tbody>
                        <?php
                        $result = $db->query('SELECT * FROM eventos ORDER BY data_evento DESC');
                        while ($row = $result->fetchArray(SQLITE3_ASSOC)):
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['nome_evento']); ?></td>
                            <td><?php echo (new DateTime($row['data_evento']))->format('d/m/Y'); ?></td>
                            <td><?php echo htmlspecialchars($row['descricao']); ?></td>
                            <td>
                                <a href="admin.php?edit_event=<?php echo $row['id']; ?>#eventos" class="btn btn-sm btn-outline-warning">Editar</a>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Eliminar este evento?');">
                                    <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo generateCsrfToken(); ?>">
                                    <input type="hidden" name="id_evento" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="delete_event" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
=======
   
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
>>>>>>> 0101abea04030b4a6aef8064caa511191fdfc879
            </div>
        </div>
    </div>
</div>
<<<<<<< HEAD
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    // Tab hash navigation
    $(function() {
        var hash = window.location.hash;
        if (hash) {
            $('#adminTab a[href="' + hash + '"]').tab('show');
        }
        $('#adminTab a').on('click', function (e) {
            window.location.hash = $(this).attr('href');
        });
    });
</script>
=======

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
    const hash = window.location.hash.substring(1);
    
    if (urlParams.has('filter_tipo')) {
        showSection('manage');
        document.getElementById('filter_tipo').value = urlParams.get('filter_tipo');
    } else if (urlParams.has('edit') || urlParams.has('delete')) {
        showSection('edit');
    } else if (urlParams.has('edit_event') || urlParams.has('delete_event') || hash === 'events') {
        showSection('events');
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
>>>>>>> 0101abea04030b4a6aef8064caa511191fdfc879
</body>
</html>