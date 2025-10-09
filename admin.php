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
        $id_produto = $_POST['id_produto'];
        $stmt = $db->prepare('DELETE FROM produtos WHERE id_produto = :id_produto');
        $stmt->bindValue(':id_produto', $id_produto, SQLITE3_INTEGER);
        $stmt->execute();
    }

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
        $id_evento = $_POST['id_evento'];
        $stmt = $db->prepare('DELETE FROM eventos WHERE id = :id');
        $stmt->bindValue(':id', $id_evento, SQLITE3_INTEGER);
        $stmt->execute();
        header('Location: admin.php#tab-eventos');
        exit;
    }
} catch (Exception $e) {
    error_log("Error in admin.php: " . $e->getMessage());
    die("Erro: Ocorreu um problema. Por favor, tente novamente.");
}

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
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Bar da Tomazia</title>
    <link rel="icon" href="img/tomazia.png" type="image/png">
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
        }
    </style>
</head>
<body>
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
                    </tbody>
                </table>
            </div>
        </div>

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
            </div>
        </div>
    </div>
</div>
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
</body>
</html>