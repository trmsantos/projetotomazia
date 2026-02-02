<?php
session_start();
require_once 'config.php';

// SMS Marketing constants
define('SMS_MIN_LENGTH', 10);
define('SMS_MAX_LENGTH', 160);

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
            header('Location: admin.php#produtos');
            exit;
        }
    }

    if (isset($_POST['delete_product'])) {
        if (!isset($_POST[CSRF_TOKEN_NAME]) || !verifyCsrfToken($_POST[CSRF_TOKEN_NAME])) die("Erro: Token CSRF inválido.");
        $id_produto = $_POST['id_produto'];
        $stmt = $db->prepare('DELETE FROM produtos WHERE id_produto = :id_produto');
        $stmt->bindValue(':id_produto', $id_produto, SQLITE3_INTEGER);
        $stmt->execute();
        header('Location: admin.php#produtos');
        exit;
    }

    // Eventos
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['form_type'] === 'evento') {
        if (!isset($_POST[CSRF_TOKEN_NAME]) || !verifyCsrfToken($_POST[CSRF_TOKEN_NAME])) die("Erro: Token CSRF inválido.");
        $nome_evento = trim($_POST['nome_evento'] ?? '');
        $data_evento = trim($_POST['data_evento'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $visivel = isset($_POST['visivel']) ? 1 : 0;
        if ($nome_evento) { // Data não é obrigatória!
            if (!empty($_POST['id_evento'])) {
                $id_evento = $_POST['id_evento'];
                $stmt = $db->prepare('UPDATE eventos SET nome_evento = :nome_evento, data_evento = :data_evento, descricao = :descricao, visivel = :visivel WHERE id = :id');
                $stmt->bindValue(':id', $id_evento, SQLITE3_INTEGER);
            } else {
                $stmt = $db->prepare('INSERT INTO eventos (nome_evento, data_evento, descricao, visivel) VALUES (:nome_evento, :data_evento, :descricao, :visivel)');
            }
            $stmt->bindValue(':nome_evento', $nome_evento, SQLITE3_TEXT);
            $stmt->bindValue(':data_evento', $data_evento, SQLITE3_TEXT);
            $stmt->bindValue(':descricao', $descricao, SQLITE3_TEXT);
            $stmt->bindValue(':visivel', $visivel, SQLITE3_INTEGER);
            $stmt->execute();
            header('Location: admin.php#eventos');
            exit;
        }
    }
    if (isset($_POST['delete_event'])) {
        if (!isset($_POST[CSRF_TOKEN_NAME]) || !verifyCsrfToken($_POST[CSRF_TOKEN_NAME])) die("Erro: Token CSRF inválido.");
        $id_evento = $_POST['id_evento'];
        $stmt = $db->prepare('DELETE FROM eventos WHERE id = :id');
        $stmt->bindValue(':id', $id_evento, SQLITE3_INTEGER);
        $stmt->execute();
        header('Location: admin.php#eventos');
        exit;
    }
    if (isset($_POST['toggle_event_visibility'])) {
        if (!isset($_POST[CSRF_TOKEN_NAME]) || !verifyCsrfToken($_POST[CSRF_TOKEN_NAME])) die("Erro: Token CSRF inválido.");
        $id_evento = $_POST['id_evento'];
        $stmt = $db->prepare('UPDATE eventos SET visivel = NOT visivel WHERE id = :id');
        $stmt->bindValue(':id', $id_evento, SQLITE3_INTEGER);
        $stmt->execute();
        header('Location: admin.php#eventos');
        exit;
    }

    // SMS Marketing
    if (isset($_POST['send_sms'])) {
        if (!isset($_POST[CSRF_TOKEN_NAME]) || !verifyCsrfToken($_POST[CSRF_TOKEN_NAME])) die("Erro: Token CSRF inválido.");
        
        $mensagem = trim($_POST['mensagem'] ?? '');
        $destinatarios = $_POST['destinatarios'] ?? 'all';
        
        if (strlen($mensagem) < SMS_MIN_LENGTH) {
            $_SESSION['sms_error'] = "A mensagem deve ter pelo menos " . SMS_MIN_LENGTH . " caracteres.";
            header('Location: admin.php#sms');
            exit;
        }
        
        // Buscar números de telefone
        $telefones = [];
        if ($destinatarios === 'all') {
            $result = $db->query('SELECT DISTINCT telemovel FROM tomazia_clientes WHERE telemovel IS NOT NULL AND telemovel != ""');
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                if (!empty($row['telemovel'])) {
                    $telefones[] = $row['telemovel'];
                }
            }
        }
        
        // Verificar se há números para enviar
        if (empty($telefones)) {
            $_SESSION['sms_error'] = "Nenhum número de telefone encontrado para envio.";
            header('Location: admin.php#sms');
            exit;
        }
        
        // Enviar SMS via API
        $sendResult = sendSmsViaApi($telefones, $mensagem);
        
        if ($sendResult['success']) {
            $message = "SMS enviado com sucesso para " . $sendResult['sent_count'] . " número(s)";
            if ($sendResult['failed_count'] > 0) {
                $message .= " (" . $sendResult['failed_count'] . " falhou)";
            }
            if (isset($sendResult['simulation']) && $sendResult['simulation']) {
                $message .= " [Modo de simulação - API não configurada]";
            }
            $_SESSION['sms_success'] = $message;
            // Armazenar apenas os primeiros 10 números para exibição (evitar sobrecarga de memória da sessão)
            $_SESSION['sms_phones'] = array_slice($telefones, 0, 10);
        } else {
            $_SESSION['sms_error'] = "Erro ao enviar SMS. " . implode('; ', $sendResult['errors']);
        }
        
        header('Location: admin.php#sms');
        exit;
    }

    // Fotos (Photo Gallery)
    if (isset($_POST['upload_photo'])) {
        if (!isset($_POST[CSRF_TOKEN_NAME]) || !verifyCsrfToken($_POST[CSRF_TOKEN_NAME])) die("Erro: Token CSRF inválido.");
        
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $descricao = trim($_POST['descricao'] ?? '');
            $visivel = isset($_POST['visivel']) ? 1 : 0;
            
            // Validar tipo de arquivo
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = $_FILES['foto']['type'];
            
            if (!in_array($fileType, $allowedTypes)) {
                $_SESSION['photo_error'] = "Tipo de arquivo não permitido. Use apenas JPEG, PNG, GIF ou WEBP.";
                header('Location: admin.php#fotos');
                exit;
            }
            
            // Validar tamanho (máximo 5MB)
            if ($_FILES['foto']['size'] > 5 * 1024 * 1024) {
                $_SESSION['photo_error'] = "Arquivo muito grande. Tamanho máximo: 5MB.";
                header('Location: admin.php#fotos');
                exit;
            }
            
            // Gerar nome único para o arquivo
            $extensao = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $nomeArquivo = 'foto_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $extensao;
            $caminhoDestino = __DIR__ . '/img/uploads/' . $nomeArquivo;
            $caminhoRelativo = 'img/uploads/' . $nomeArquivo;
            
            // Mover arquivo para diretório de uploads
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $caminhoDestino)) {
                // Salvar no banco de dados
                $stmt = $db->prepare('INSERT INTO fotos (nome_foto, caminho, descricao, visivel) VALUES (:nome_foto, :caminho, :descricao, :visivel)');
                $stmt->bindValue(':nome_foto', $_FILES['foto']['name'], SQLITE3_TEXT);
                $stmt->bindValue(':caminho', $caminhoRelativo, SQLITE3_TEXT);
                $stmt->bindValue(':descricao', $descricao, SQLITE3_TEXT);
                $stmt->bindValue(':visivel', $visivel, SQLITE3_INTEGER);
                $stmt->execute();
                
                $_SESSION['photo_success'] = "Foto enviada com sucesso!";
            } else {
                $_SESSION['photo_error'] = "Erro ao fazer upload da foto.";
            }
        } else {
            $_SESSION['photo_error'] = "Nenhuma foto foi selecionada ou ocorreu um erro no upload.";
        }
        
        header('Location: admin.php#fotos');
        exit;
    }
    
    if (isset($_POST['delete_photo'])) {
        if (!isset($_POST[CSRF_TOKEN_NAME]) || !verifyCsrfToken($_POST[CSRF_TOKEN_NAME])) die("Erro: Token CSRF inválido.");
        
        $id_foto = $_POST['id_foto'];
        
        // Obter caminho da foto antes de deletar
        $stmt = $db->prepare('SELECT caminho FROM fotos WHERE id = :id');
        $stmt->bindValue(':id', $id_foto, SQLITE3_INTEGER);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        
        if ($result) {
            $caminhoCompleto = __DIR__ . '/' . $result['caminho'];
            
            // Deletar do banco de dados
            $stmt = $db->prepare('DELETE FROM fotos WHERE id = :id');
            $stmt->bindValue(':id', $id_foto, SQLITE3_INTEGER);
            $stmt->execute();
            
            // Deletar arquivo físico
            if (file_exists($caminhoCompleto)) {
                unlink($caminhoCompleto);
            }
            
            $_SESSION['photo_success'] = "Foto deletada com sucesso!";
        }
        
        header('Location: admin.php#fotos');
        exit;
    }
    
    if (isset($_POST['toggle_photo_visibility'])) {
        if (!isset($_POST[CSRF_TOKEN_NAME]) || !verifyCsrfToken($_POST[CSRF_TOKEN_NAME])) die("Erro: Token CSRF inválido.");
        
        $id_foto = $_POST['id_foto'];
        $stmt = $db->prepare('UPDATE fotos SET visivel = NOT visivel WHERE id = :id');
        $stmt->bindValue(':id', $id_foto, SQLITE3_INTEGER);
        $stmt->execute();
        
        header('Location: admin.php#fotos');
        exit;
    }

    // Adesão: obter anos e dados
    $years = [];
    $stmt = $db->prepare('SELECT DISTINCT strftime("%Y", data_registro) as year FROM tomazia_clientes ORDER BY year DESC');
    $result = $stmt->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) { $years[] = $row['year']; }

    $year = isset($_GET['year']) ? $_GET['year'] : (count($years) ? $years[0] : date('Y'));
    $chart_data = [];
    $total_adherence = 0;
    for ($i = 1; $i <= 12; $i++) {
        $stmt = $db->prepare('SELECT COUNT(user_id) as count FROM tomazia_clientes WHERE strftime("%Y", data_registro) = :year AND strftime("%m", data_registro) = :month');
        $stmt->bindValue(':year', $year, SQLITE3_TEXT);
        $stmt->bindValue(':month', str_pad($i, 2, '0', STR_PAD_LEFT), SQLITE3_TEXT);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        $count = $result ? $result['count'] : 0;
        $chart_data[] = $count;
        $total_adherence += $count;
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --background-dark: #5D1F3A;
            --surface-dark: #3D0F24;
            --primary-gold: #D4AF37;
            --text-light: #f0f0f0;
            --text-medium: #a0a0a0;
            --border-color: rgba(212, 175, 55, 0.2);
        }
        body { background: var(--background-dark); color: var(--text-light); font-family: 'Montserrat', Arial, sans-serif; }
        .navbar { background: var(--surface-dark); }
        .navbar-brand img { height: 60px; }
        .admin-main { max-width: 1000px; margin: 40px auto 0 auto; }
        .tab-content { background: var(--surface-dark); border-radius: 12px; padding: 30px; box-shadow: 0 8px 32px #00000060; border: 1px solid var(--border-color);}
        .nav-tabs { border-bottom: none; margin-bottom: 0;}
        .nav-tabs .nav-link { color: var(--primary-gold); font-weight: 600; border-radius: 8px 8px 0 0; margin-right: 10px; background: none; border: none; }
        .nav-tabs .nav-link.active { background: var(--primary-gold); color: var(--surface-dark);}
        .section-title { font-family: 'Playfair Display', serif; color: var(--primary-gold); font-size: 2rem; margin-bottom: 1.2rem;}
        .form-section { margin-bottom: 2rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1.5rem;}
        .form-control, .custom-select { background: rgba(93, 31, 58, 0.3); color: var(--text-light); border: 1px solid var(--border-color);}
        .form-control:focus, .custom-select:focus { border-color: var(--primary-gold); box-shadow: none; }
        .btn-primary { background: var(--primary-gold); border-color: var(--primary-gold); color: var(--surface-dark); font-weight: 600;}
        .btn-primary:hover { background: #c8a030; border-color: #c8a030;}
        .btn-outline-danger, .btn-outline-warning { border-radius: 4px; font-weight: 500;}
        .table { color: var(--text-light);}
        .table th { color: var(--primary-gold); border-top: none; }
        .table td, .table th { border-color: var(--border-color);}
        .table-hover tbody tr:hover { background: rgba(212, 175, 55, 0.1); }
        .logout-form { text-align: right; margin-top: 20px;}
        .chart-container { min-height:320px; }
        .alert { border-radius: 8px; padding: 15px; margin-bottom: 20px; }
        .alert-success { background: rgba(40, 167, 69, 0.2); border: 1px solid rgba(40, 167, 69, 0.5); color: #66d98c; }
        .alert-danger { background: rgba(220, 53, 69, 0.2); border: 1px solid rgba(220, 53, 69, 0.5); color: #ff6b7f; }
        .alert-info { background: rgba(23, 162, 184, 0.2); border: 1px solid rgba(23, 162, 184, 0.5); color: #5bc0de; }
        .alert-warning { background: rgba(255, 193, 7, 0.2); border: 1px solid rgba(255, 193, 7, 0.5); color: #ffc107; }
        .btn-outline-info { border-color: #17a2b8; color: #17a2b8; }
        .btn-outline-info:hover { background: #17a2b8; color: white; }
        @media (max-width: 1000px) {
            .admin-main { max-width: 98vw; }
            .tab-content { padding: 18px;}
        }
        @media (max-width: 600px) {
            .admin-main { margin-top: 10px; }
            .form-section, .section-title { font-size: 1.2rem; }
            .tab-content { padding: 8px; }
            .chart-container { min-height:220px;}
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
        <li class="nav-item"><a class="nav-link active" id="tab-dashboard" data-toggle="tab" href="#dashboard" role="tab">Adesão</a></li>
        <li class="nav-item"><a class="nav-link" id="tab-produtos" data-toggle="tab" href="#produtos" role="tab">Produtos</a></li>
        <li class="nav-item"><a class="nav-link" id="tab-eventos" data-toggle="tab" href="#eventos" role="tab">Eventos</a></li>
        <li class="nav-item"><a class="nav-link" id="tab-fotos" data-toggle="tab" href="#fotos" role="tab">Fotos</a></li>
        <li class="nav-item"><a class="nav-link" id="tab-sms" data-toggle="tab" href="#sms" role="tab">SMS Marketing</a></li>
    </ul>
    <div class="tab-content">
        <!-- Adesão -->
        <div class="tab-pane fade show active" id="dashboard" role="tabpanel">
            <div class="form-section">
                <div class="section-title">Adesão de Clientes</div>
                <form method="get" class="mb-3">
                    <label for="yearSelect">Selecionar Ano</label>
                    <select class="custom-select w-auto d-inline-block" id="yearSelect" name="year" onchange="this.form.submit()">
                        <?php foreach ($years as $availableYear): ?>
                            <option value="<?php echo $availableYear; ?>" <?php echo ($availableYear == $year) ? 'selected' : ''; ?>><?php echo $availableYear; ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
                <div class="chart-container">
                    <canvas id="adherenceChart"></canvas>
                </div>
                <h4 class="mt-3">Total de Adesões em <?php echo $year; ?>: <span class="text-warning"><?php echo $total_adherence; ?></span></h4>
            </div>
        </div>
        <!-- Produtos -->
        <div class="tab-pane fade" id="produtos" role="tabpanel">
            <div class="form-section">
                <div class="section-title"><?php echo $edit_product ? 'Editar Produto' : 'Novo Produto'; ?></div>
                <form method="POST" action="admin.php#produtos">
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
                                <form method="POST" style="display:inline;" action="admin.php#produtos" onsubmit="return confirm('Eliminar este produto?');">
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
                <form method="POST" action="admin.php#eventos">
                    <input type="hidden" name="form_type" value="evento">
                    <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo generateCsrfToken(); ?>">
                    <input type="hidden" name="id_evento" value="<?php echo $edit_event['id'] ?? ''; ?>">
                    <div class="form-row">
                        <div class="form-group col-md-6"><input type="text" class="form-control" name="nome_evento" placeholder="Nome do Evento" value="<?php echo $edit_event['nome_evento'] ?? ''; ?>" required></div>
                        <div class="form-group col-md-3"><input type="date" class="form-control" name="data_evento" value="<?php echo $edit_event['data_evento'] ?? ''; ?>"></div>
                        <div class="form-group col-md-3"><input type="text" class="form-control" name="descricao" placeholder="Descrição" value="<?php echo $edit_event['descricao'] ?? ''; ?>"></div>
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="visivel" name="visivel" <?php 
                            $is_visible = !isset($edit_event) || (isset($edit_event['visivel']) && $edit_event['visivel'] == 1);
                            echo $is_visible ? 'checked' : ''; 
                        ?>>
                        <label class="form-check-label" for="visivel">Visível na página principal</label>
                    </div>
                    <button type="submit" class="btn btn-primary"><?php echo $edit_event ? 'Atualizar' : 'Adicionar'; ?></button>
                    <?php if ($edit_event): ?><a href="admin.php#eventos" class="btn btn-outline-warning ml-2">Cancelar</a><?php endif; ?>
                </form>
            </div>
            <div>
                <div class="section-title">Lista de Eventos</div>
                <table class="table table-hover">
                    <thead><tr><th>Nome</th><th>Data</th><th>Descrição</th><th>Visível</th><th>Ações</th></tr></thead>
                    <tbody>
                        <?php
                        $result = $db->query('SELECT * FROM eventos ORDER BY data_evento DESC');
                        while ($row = $result->fetchArray(SQLITE3_ASSOC)):
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['nome_evento']); ?></td>
                            <td>
                                <?php
                                if (!empty($row['data_evento'])) {
                                    $data = DateTime::createFromFormat('Y-m-d', $row['data_evento']);
                                    echo $data ? $data->format('d/m/Y') : '—';
                                } else {
                                    echo '—';
                                }
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['descricao']); ?></td>
                            <td>
                                <?php if ($row['visivel'] == 1): ?>
                                    <span style="color: #28a745;">✓ Sim</span>
                                <?php else: ?>
                                    <span style="color: #dc3545;">✗ Não</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="admin.php?edit_event=<?php echo $row['id']; ?>#eventos" class="btn btn-sm btn-outline-warning">Editar</a>
                                <form method="POST" style="display:inline;" action="admin.php#eventos">
                                    <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo generateCsrfToken(); ?>">
                                    <input type="hidden" name="id_evento" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="toggle_event_visibility" class="btn btn-sm btn-outline-info">
                                        <?php echo $row['visivel'] == 1 ? 'Ocultar' : 'Mostrar'; ?>
                                    </button>
                                </form>
                                <form method="POST" style="display:inline;" action="admin.php#eventos" onsubmit="return confirm('Eliminar este evento?');">
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
        <!-- SMS Marketing -->
        <div class="tab-pane fade" id="sms" role="tabpanel">
            <div class="form-section">
                <div class="section-title">Envio de SMS Marketing</div>
                
                <?php if (isset($_SESSION['sms_success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($_SESSION['sms_success']); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php 
                    if (isset($_SESSION['sms_phones']) && !empty($_SESSION['sms_phones'])): 
                        echo '<div class="alert alert-info"><strong>Números de destino:</strong> ' . implode(', ', array_slice($_SESSION['sms_phones'], 0, 10));
                        if (count($_SESSION['sms_phones']) > 10) {
                            echo ' e mais ' . (count($_SESSION['sms_phones']) - 10) . ' número(s)';
                        }
                        echo '</div>';
                    endif;
                    unset($_SESSION['sms_success']);
                    unset($_SESSION['sms_phones']);
                    unset($_SESSION['sms_message']);
                    ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['sms_error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($_SESSION['sms_error']); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php unset($_SESSION['sms_error']); ?>
                <?php endif; ?>
                
                <p class="text-info mb-3">
                    <strong>Nota:</strong> Configure as credenciais da API de SMS no arquivo config.php para enviar SMS reais. 
                    Atualmente, o sistema opera em modo de simulação.
                </p>
                
                <form method="POST" action="admin.php#sms" id="smsForm">
                    <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo generateCsrfToken(); ?>">
                    
                    <div class="form-group">
                        <label for="destinatarios">Destinatários</label>
                        <select class="custom-select" id="destinatarios" name="destinatarios" required>
                            <option value="all">Todos os clientes registados</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="mensagem">Mensagem SMS</label>
                        <textarea class="form-control" id="mensagem" name="mensagem" rows="5" 
                                  placeholder="Escreva a sua mensagem de marketing aqui..." 
                                  maxlength="<?php echo SMS_MAX_LENGTH; ?>" required></textarea>
                        <small class="form-text" style="color: var(--text-medium);">
                            Caracteres: <span id="charCount">0</span>/<?php echo SMS_MAX_LENGTH; ?>
                        </small>
                    </div>
                    
                    <button type="submit" name="send_sms" class="btn btn-primary" onclick="return confirm('Tem certeza que deseja enviar este SMS?');">
                        Enviar SMS
                    </button>
                </form>
            </div>
            
            <div>
                <div class="section-title">Números de Telefone Registados</div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead><tr><th>Nome</th><th>Telefone</th><th>Email</th><th>Data de Registo</th></tr></thead>
                        <tbody>
                            <?php
                            $result = $db->query('SELECT nome, telemovel, email, data_registro FROM tomazia_clientes WHERE telemovel IS NOT NULL AND telemovel != "" ORDER BY data_registro DESC');
                            $count = 0;
                            while ($row = $result->fetchArray(SQLITE3_ASSOC)):
                                $count++;
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['nome']); ?></td>
                                <td><?php echo htmlspecialchars($row['telemovel']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td>
                                    <?php 
                                    if (!empty($row['data_registro'])) {
                                        $data = DateTime::createFromFormat('Y-m-d H:i:s', $row['data_registro']);
                                        echo $data ? $data->format('d/m/Y H:i') : '—';
                                    } else {
                                        echo '—';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if ($count === 0): ?>
                                <tr><td colspan="4" class="text-center">Nenhum número de telefone registado.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <p class="text-info mt-2">Total de números registados: <strong><?php echo $count; ?></strong></p>
            </div>
        </div>
        <!-- Fotos (Photo Gallery) -->
        <div class="tab-pane fade" id="fotos" role="tabpanel">
            <div class="form-section">
                <div class="section-title">Upload de Nova Foto</div>
                
                <?php if (isset($_SESSION['photo_success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($_SESSION['photo_success']); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php unset($_SESSION['photo_success']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['photo_error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($_SESSION['photo_error']); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php unset($_SESSION['photo_error']); ?>
                <?php endif; ?>
                
                <form method="POST" action="admin.php#fotos" enctype="multipart/form-data">
                    <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo generateCsrfToken(); ?>">
                    
                    <div class="form-group">
                        <label for="foto">Selecionar Foto</label>
                        <input type="file" class="form-control-file" id="foto" name="foto" accept="image/*" required>
                        <small class="form-text" style="color: var(--text-medium);">
                            Formatos aceitos: JPEG, PNG, GIF, WEBP. Tamanho máximo: 5MB
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="descricao">Descrição (opcional)</label>
                        <input type="text" class="form-control" id="descricao" name="descricao" placeholder="Descrição da foto">
                    </div>
                    
                    <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="visivel" name="visivel" checked>
                        <label class="form-check-label" for="visivel">Visível na galeria</label>
                    </div>
                    
                    <button type="submit" name="upload_photo" class="btn btn-primary">Upload Foto</button>
                </form>
            </div>
            
            <div>
                <div class="section-title">Galeria de Fotos</div>
                <div class="row">
                    <?php
                    $result = $db->query('SELECT * FROM fotos ORDER BY data_upload DESC');
                    $count = 0;
                    while ($row = $result->fetchArray(SQLITE3_ASSOC)):
                        $count++;
                    ?>
                    <div class="col-md-4 col-lg-3 mb-4">
                        <div class="card" style="background: var(--surface-dark); border: 1px solid var(--border-color);">
                            <img src="<?php echo htmlspecialchars($row['caminho']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['nome_foto']); ?>" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h6 class="card-title" style="color: var(--primary-gold); font-size: 0.9rem;"><?php echo htmlspecialchars($row['nome_foto']); ?></h6>
                                <?php if (!empty($row['descricao'])): ?>
                                    <p class="card-text" style="color: var(--text-light); font-size: 0.85rem;"><?php echo htmlspecialchars($row['descricao']); ?></p>
                                <?php endif; ?>
                                <p class="card-text" style="font-size: 0.8rem; color: var(--text-medium);">
                                    <?php 
                                    if (!empty($row['data_upload'])) {
                                        $data = DateTime::createFromFormat('Y-m-d H:i:s', $row['data_upload']);
                                        echo $data ? $data->format('d/m/Y H:i') : '—';
                                    } else {
                                        echo '—';
                                    }
                                    ?>
                                </p>
                                <div class="btn-group btn-group-sm" role="group">
                                    <form method="POST" style="display:inline;" action="admin.php#fotos">
                                        <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo generateCsrfToken(); ?>">
                                        <input type="hidden" name="id_foto" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="toggle_photo_visibility" class="btn btn-sm btn-outline-info">
                                            <?php echo $row['visivel'] == 1 ? 'Ocultar' : 'Mostrar'; ?>
                                        </button>
                                    </form>
                                    <form method="POST" style="display:inline;" action="admin.php#fotos" onsubmit="return confirm('Deletar esta foto?');">
                                        <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo generateCsrfToken(); ?>">
                                        <input type="hidden" name="id_foto" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="delete_photo" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                    </form>
                                </div>
                                <span class="badge badge-<?php echo $row['visivel'] == 1 ? 'success' : 'secondary'; ?> mt-2">
                                    <?php echo $row['visivel'] == 1 ? 'Visível' : 'Oculta'; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                    <?php if ($count === 0): ?>
                        <div class="col-12 text-center">
                            <p class="lead" style="color: var(--text-medium);">Nenhuma foto carregada ainda.</p>
                        </div>
                    <?php endif; ?>
                </div>
                <p class="text-info mt-2">Total de fotos: <strong><?php echo $count; ?></strong></p>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    $(function() {
        var hash = window.location.hash || "#dashboard";
        $('#adminTab a[href="' + hash + '"]').tab('show');
        $('#adminTab a').on('click', function (e) {
            window.location.hash = $(this).attr('href');
        });
        var ctx = document.getElementById('adherenceChart').getContext('2d');
        var adherenceChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
                datasets: [{
                    label: 'Novos Clientes',
                    data: <?php echo json_encode($chart_data); ?>,
                    backgroundColor: 'rgba(212, 175, 55, 0.6)',
                    borderColor: 'rgba(212, 175, 55, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, ticks: { color: 'var(--text-medium)' } },
                    x: { ticks: { color: 'var(--text-medium)' } }
                },
                plugins: { legend: { display: false } }
            }
        });
        
        // SMS character counter
        $('#mensagem').on('input', function() {
            var count = $(this).val().length;
            var maxLength = <?php echo SMS_MAX_LENGTH; ?>;
            $('#charCount').text(count);
            if (count > maxLength) {
                $('#charCount').css('color', '#dc3545');
            } else if (count > (maxLength - 20)) {
                $('#charCount').css('color', '#ffc107');
            } else {
                $('#charCount').css('color', 'var(--text-medium)');
            }
        });
    });
</script>
</body>
</html>