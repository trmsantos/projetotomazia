<?php
session_start();
require_once 'config.php';

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
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type']) && $_POST['form_type'] === 'produto') {
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
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type']) && $_POST['form_type'] === 'evento') {
        if (!isset($_POST[CSRF_TOKEN_NAME]) || !verifyCsrfToken($_POST[CSRF_TOKEN_NAME])) die("Erro: Token CSRF inválido.");
        $nome_evento = trim($_POST['nome_evento'] ?? '');
        $data_evento = trim($_POST['data_evento'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $visivel = isset($_POST['visivel']) ? 1 : 0;
        if ($nome_evento) {
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
        
        $telefones = [];
        if ($destinatarios === 'all') {
            $result = $db->query('SELECT DISTINCT telemovel FROM tomazia_clientes WHERE telemovel IS NOT NULL AND telemovel != ""');
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                if (!empty($row['telemovel'])) {
                    $telefones[] = $row['telemovel'];
                }
            }
        }
        
        if (empty($telefones)) {
            $_SESSION['sms_error'] = "Nenhum número de telefone encontrado para envio.";
            header('Location: admin.php#sms');
            exit;
        }
        
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
            $_SESSION['sms_phones'] = array_slice($telefones, 0, 10);
        } else {
            $_SESSION['sms_error'] = "Erro ao enviar SMS. " . implode('; ', $sendResult['errors']);
        }
        
        header('Location: admin.php#sms');
        exit;
    }

    // Fotos - Upload
    if (isset($_POST['upload_photo'])) {
        if (!isset($_POST[CSRF_TOKEN_NAME]) || !verifyCsrfToken($_POST[CSRF_TOKEN_NAME])) die("Erro: Token CSRF inválido.");
        
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $descricao = trim($_POST['descricao'] ?? '');
            $visivel = isset($_POST['visivel']) ? 1 : 0;
            
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = $_FILES['foto']['type'];
            
            if (!in_array($fileType, $allowedTypes)) {
                $_SESSION['photo_error'] = "Tipo de arquivo não permitido. Use apenas JPEG, PNG, GIF ou WEBP.";
                header('Location: admin.php#fotos');
                exit;
            }
            
            if ($_FILES['foto']['size'] > 5 * 1024 * 1024) {
                $_SESSION['photo_error'] = "Arquivo muito grande. Tamanho máximo: 5MB.";
                header('Location: admin.php#fotos');
                exit;
            }
            
            $extensao = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $nomeArquivo = 'foto_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $extensao;
            $caminhoDestino = __DIR__ . '/img_users/' . $nomeArquivo;
            $caminhoRelativo = 'img_users/' . $nomeArquivo;
            
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $caminhoDestino)) {
                $stmt = $db->prepare('INSERT INTO fotos (nome_foto, caminho, descricao, visivel, status, uploaded_by, is_admin_upload) VALUES (:nome_foto, :caminho, :descricao, :visivel, :status, :uploaded_by, 1)');
                $stmt->bindValue(':nome_foto', $_FILES['foto']['name'], SQLITE3_TEXT);
                $stmt->bindValue(':caminho', $caminhoRelativo, SQLITE3_TEXT);
                $stmt->bindValue(':descricao', $descricao, SQLITE3_TEXT);
                $stmt->bindValue(':visivel', $visivel, SQLITE3_INTEGER);
                $stmt->bindValue(':status', 'aprovado', SQLITE3_TEXT);
                $stmt->bindValue(':uploaded_by', 'Admin', SQLITE3_TEXT);
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
    
    // Aprovar foto - MOVE TO img_users
    if (isset($_POST['approve_photo'])) {
        if (!isset($_POST[CSRF_TOKEN_NAME]) || !verifyCsrfToken($_POST[CSRF_TOKEN_NAME])) die("Erro: Token CSRF inválido.");
        
        $id_foto = $_POST['id_foto'];
        
        // Get current photo path
        $stmt = $db->prepare('SELECT caminho FROM fotos WHERE id = :id');
        $stmt->bindValue(':id', $id_foto, SQLITE3_INTEGER);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        
        if ($result) {
            $oldPath = __DIR__ . '/' . $result['caminho'];
            $filename = basename($result['caminho']);
            $newPath = __DIR__ . '/img_users/' . $filename;
            $newRelativePath = 'img_users/' . $filename;
            
            // Move file to img_users
            if (file_exists($oldPath)) {
                if (!file_exists(dirname($newPath))) {
                    mkdir(dirname($newPath), 0755, true);
                }
                
                if (rename($oldPath, $newPath)) {
                    // Update database with new path
                    $stmt = $db->prepare("UPDATE fotos SET status = 'aprovado', visivel = 1, caminho = :caminho WHERE id = :id");
                    $stmt->bindValue(':id', $id_foto, SQLITE3_INTEGER);
                    $stmt->bindValue(':caminho', $newRelativePath, SQLITE3_TEXT);
                    $stmt->execute();
                    
                    $_SESSION['photo_success'] = "Foto aprovada e movida para img_users com sucesso!";
                } else {
                    $_SESSION['photo_error'] = "Erro ao mover o arquivo.";
                }
            }
        }
        
        header('Location: admin.php#fotos');
        exit;
    }
    
    if (isset($_POST['reject_photo'])) {
        if (!isset($_POST[CSRF_TOKEN_NAME]) || !verifyCsrfToken($_POST[CSRF_TOKEN_NAME])) die("Erro: Token CSRF inválido.");
        
        $id_foto = $_POST['id_foto'];
        $stmt = $db->prepare("UPDATE fotos SET status = 'rejeitado', visivel = 0 WHERE id = :id");
        $stmt->bindValue(':id', $id_foto, SQLITE3_INTEGER);
        $stmt->execute();
        
        $_SESSION['photo_success'] = "Foto rejeitada.";
        header('Location: admin.php#fotos');
        exit;
    }
    
    if (isset($_POST['delete_photo'])) {
        if (!isset($_POST[CSRF_TOKEN_NAME]) || !verifyCsrfToken($_POST[CSRF_TOKEN_NAME])) die("Erro: Token CSRF inválido.");
        
        $id_foto = $_POST['id_foto'];
        
        $stmt = $db->prepare('SELECT caminho FROM fotos WHERE id = :id');
        $stmt->bindValue(':id', $id_foto, SQLITE3_INTEGER);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        
        if ($result) {
            $caminhoCompleto = __DIR__ . '/' . $result['caminho'];
            
            $stmt = $db->prepare('DELETE FROM fotos WHERE id = :id');
            $stmt->bindValue(':id', $id_foto, SQLITE3_INTEGER);
            $stmt->execute();
            
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

    // Adesão
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
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --burgundy: #5D1F3A;
            --burgundy-dark: #3D0F24;
            --gold: #D4AF37;
            --gold-light: #E8C76F;
            --text-light: #f0f0f0;
            --text-medium: #a0a0a0;
            --border-color: rgba(212, 175, 55, 0.2);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body { 
            background: linear-gradient(135deg, var(--burgundy) 0%, var(--burgundy-dark) 100%);
            color: var(--text-light); 
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
        }
        
        .navbar { 
            background: rgba(61, 15, 36, 0.95);
            backdrop-filter: blur(15px);
            padding: 1.5rem 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }
        
        .navbar-brand img { 
            height: 60px;
            transition: transform 0.3s ease;
        }
        
        .navbar-brand img:hover {
            transform: scale(1.05);
        }
        
        .admin-main { 
            max-width: 1200px;
            margin: 3rem auto;
            padding: 0 1.5rem;
        }
        
        .tab-content { 
            background: rgba(61, 15, 36, 0.85);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 2.5rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
            border: 1px solid var(--border-color);
            margin-top: 2rem;
        }
        
        .nav-tabs { 
            border-bottom: 2px solid var(--border-color);
            margin-bottom: 0;
            gap: 0.5rem;
        }
        
        .nav-tabs .nav-link { 
            color: var(--gold);
            font-weight: 600;
            border-radius: 10px 10px 0 0;
            padding: 0.875rem 1.5rem;
            background: transparent;
            border: none;
            transition: all 0.3s ease;
        }
        
        .nav-tabs .nav-link:hover {
            background: rgba(212, 175, 55, 0.1);
        }
        
        .nav-tabs .nav-link.active { 
            background: var(--gold);
            color: var(--burgundy-dark);
        }
        
        .section-title { 
            font-family: 'Cormorant Garamond', serif;
            color: var(--gold);
            font-size: 2rem;
            margin-bottom: 2rem;
            font-weight: 700;
        }
        
        .form-section { 
            margin-bottom: 3rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .form-control, .custom-select { 
            background: rgba(93, 31, 58, 0.4);
            color: var(--text-light);
            border: 2px solid rgba(212, 175, 55, 0.3);
            border-radius: 8px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .custom-select:focus { 
            border-color: var(--gold);
            box-shadow: 0 0 0 4px rgba(212, 175, 55, 0.1);
            background: rgba(93, 31, 58, 0.6);
            color: #fff;
            outline: none;
        }
        
        .form-control::placeholder {
            color: rgba(240, 240, 240, 0.5);
        }
        
        .btn-primary { 
            background: linear-gradient(135deg, var(--gold), var(--gold-light));
            border: none;
            color: var(--burgundy-dark);
            font-weight: 600;
            padding: 0.75rem 1.75rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(212, 175, 55, 0.3);
        }
        
        .btn-primary:hover { 
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(212, 175, 55, 0.4);
            color: var(--burgundy-dark);
        }
        
        .btn-outline-danger, .btn-outline-warning, .btn-outline-info { 
            border-radius: 6px;
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }
        
        .btn-outline-danger:hover {
            transform: translateY(-2px);
        }
        
        .table { 
            color: var(--text-light);
            background: rgba(93, 31, 58, 0.3);
            border-radius: 12px;
            overflow: hidden;
        }
        
        .table th { 
            color: var(--gold);
            border-top: none;
            font-weight: 600;
            padding: 1rem 1.25rem;
            background: rgba(212, 175, 55, 0.1);
        }
        
        .table td { 
            border-color: var(--border-color);
            padding: 1rem 1.25rem;
        }
        
        .table-hover tbody tr:hover { 
            background: rgba(212, 175, 55, 0.08);
        }
        
        .chart-container { 
            min-height: 350px;
            padding: 1.5rem;
            background: rgba(93, 31, 58, 0.3);
            border-radius: 12px;
        }
        
        .alert { 
            border-radius: 10px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            border: none;
        }
        
        .alert-success { 
            background: rgba(40, 167, 69, 0.15);
            border-left: 4px solid #28a745;
            color: #66d98c;
        }
        
        .alert-danger { 
            background: rgba(220, 53, 69, 0.15);
            border-left: 4px solid #dc3545;
            color: #ff6b7f;
        }
        
        .alert-info { 
            background: rgba(23, 162, 184, 0.15);
            border-left: 4px solid #17a2b8;
            color: #5bc0de;
        }
        
        .card {
            background: rgba(93, 31, 58, 0.4);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }
        
        .badge {
            padding: 0.5rem 0.75rem;
            font-weight: 600;
            border-radius: 6px;
        }
        
        @media (max-width: 768px) {
            .admin-main { 
                padding: 0 1rem;
            }
            .tab-content { 
                padding: 1.5rem;
            }
            .section-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
<nav class="navbar navbar-dark">
    <a class="navbar-brand" href="index.php"><img src="img/tomazia.png" alt="Tomazia"></a>
    <form method="POST" class="m-0">
        <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo generateCsrfToken(); ?>">
        <button type="submit" name="logout" class="btn btn-outline-danger">
            <i class="fas fa-sign-out-alt"></i> Logout
        </button>
    </form>
</nav>

<div class="admin-main">
    <ul class="nav nav-tabs" id="adminTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="tab-dashboard" data-toggle="tab" href="#dashboard" role="tab">
                <i class="fas fa-chart-line"></i> Adesão
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="tab-produtos" data-toggle="tab" href="#produtos" role="tab">
                <i class="fas fa-utensils"></i> Produtos
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="tab-eventos" data-toggle="tab" href="#eventos" role="tab">
                <i class="fas fa-calendar-alt"></i> Eventos
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="tab-fotos" data-toggle="tab" href="#fotos" role="tab">
                <i class="fas fa-images"></i> Fotos
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="tab-sms" data-toggle="tab" href="#sms" role="tab">
                <i class="fas fa-sms"></i> SMS Marketing
            </a>
        </li>
    </ul>
    
    <div class="tab-content">
        <!-- Dashboard Tab -->
        <div class="tab-pane fade show active" id="dashboard" role="tabpanel">
            <div class="form-section">
                <div class="section-title">Adesão de Clientes</div>
                <form method="get" class="mb-4">
                    <label for="yearSelect">Selecionar Ano</label>
                    <select class="custom-select w-auto d-inline-block ml-2" id="yearSelect" name="year" onchange="this.form.submit()">
                        <?php foreach ($years as $availableYear): ?>
                            <option value="<?php echo $availableYear; ?>" <?php echo ($availableYear == $year) ? 'selected' : ''; ?>><?php echo $availableYear; ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
                <div class="chart-container">
                    <canvas id="adherenceChart"></canvas>
                </div>
                <h4 class="mt-4">Total de Adesões em <?php echo $year; ?>: <span style="color: var(--gold);"><?php echo $total_adherence; ?></span></h4>
            </div>
        </div>

        <!-- Produtos Tab -->
        <div class="tab-pane fade" id="produtos" role="tabpanel">
            <div class="form-section">
                <div class="section-title"><?php echo $edit_product ? 'Editar Produto' : 'Novo Produto'; ?></div>
                <form method="POST" action="admin.php#produtos">
                    <input type="hidden" name="form_type" value="produto">
                    <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo generateCsrfToken(); ?>">
                    <input type="hidden" name="id_produto" value="<?php echo $edit_product['id_produto'] ?? ''; ?>">
                    <div class="form-row">
                        <div class="form-group col-md-5">
                            <input type="text" class="form-control" name="nome_prod" placeholder="Nome do Produto" value="<?php echo $edit_product['nome_prod'] ?? ''; ?>" required>
                        </div>
                        <div class="form-group col-md-3">
                            <input type="number" step="0.01" class="form-control" name="preco" placeholder="Preço (€)" value="<?php echo $edit_product['preco'] ?? ''; ?>" required min="0">
                        </div>
                        <div class="form-group col-md-4">
                            <input type="text" class="form-control" name="tipo" placeholder="Tipo/Categoria" value="<?php echo $edit_product['tipo'] ?? ''; ?>" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-<?php echo $edit_product ? 'edit' : 'plus'; ?>"></i> 
                        <?php echo $edit_product ? 'Atualizar' : 'Adicionar'; ?>
                    </button>
                    <?php if ($edit_product): ?>
                        <a href="admin.php#produtos" class="btn btn-outline-warning ml-2">Cancelar</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <div>
                <div class="section-title">Lista de Produtos</div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Preço</th>
                                <th>Tipo</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = $db->query('SELECT * FROM produtos ORDER BY tipo, nome_prod');
                            while ($row = $result->fetchArray(SQLITE3_ASSOC)):
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['nome_prod']); ?></td>
                                <td><?php echo number_format($row['preco'], 2, ',', '.'); ?> €</td>
                                <td><span class="badge badge-secondary"><?php echo htmlspecialchars($row['tipo']); ?></span></td>
                                <td>
                                    <a href="admin.php?edit_product=<?php echo $row['id_produto']; ?>#produtos" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <form method="POST" style="display:inline;" action="admin.php#produtos" onsubmit="return confirm('Eliminar este produto?');">
                                        <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo generateCsrfToken(); ?>">
                                        <input type="hidden" name="id_produto" value="<?php echo $row['id_produto']; ?>">
                                        <button type="submit" name="delete_product" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Eventos Tab -->
        <div class="tab-pane fade" id="eventos" role="tabpanel">
            <div class="form-section">
                <div class="section-title"><?php echo $edit_event ? 'Editar Evento' : 'Novo Evento'; ?></div>
                <form method="POST" action="admin.php#eventos">
                    <input type="hidden" name="form_type" value="evento">
                    <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo generateCsrfToken(); ?>">
                    <input type="hidden" name="id_evento" value="<?php echo $edit_event['id'] ?? ''; ?>">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <input type="text" class="form-control" name="nome_evento" placeholder="Nome do Evento" value="<?php echo $edit_event['nome_evento'] ?? ''; ?>" required>
                        </div>
                        <div class="form-group col-md-3">
                            <input type="date" class="form-control" name="data_evento" value="<?php echo $edit_event['data_evento'] ?? ''; ?>">
                        </div>
                        <div class="form-group col-md-3">
                            <input type="text" class="form-control" name="descricao" placeholder="Descrição" value="<?php echo $edit_event['descricao'] ?? ''; ?>">
                        </div>
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="visivel" name="visivel" <?php 
                            $is_visible = !isset($edit_event) || (isset($edit_event['visivel']) && $edit_event['visivel'] == 1);
                            echo $is_visible ? 'checked' : ''; 
                        ?>>
                        <label class="form-check-label" for="visivel">Visível na página principal</label>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-<?php echo $edit_event ? 'edit' : 'plus'; ?>"></i> 
                        <?php echo $edit_event ? 'Atualizar' : 'Adicionar'; ?>
                    </button>
                    <?php if ($edit_event): ?>
                        <a href="admin.php#eventos" class="btn btn-outline-warning ml-2">Cancelar</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <div>
                <div class="section-title">Lista de Eventos</div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Data</th>
                                <th>Descrição</th>
                                <th>Visível</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
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
                                        <span class="badge badge-success">Sim</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Não</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="admin.php?edit_event=<?php echo $row['id']; ?>#eventos" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <form method="POST" style="display:inline;" action="admin.php#eventos">
                                        <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo generateCsrfToken(); ?>">
                                        <input type="hidden" name="id_evento" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="toggle_event_visibility" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-eye<?php echo $row['visivel'] == 1 ? '-slash' : ''; ?>"></i>
                                            <?php echo $row['visivel'] == 1 ? 'Ocultar' : 'Mostrar'; ?>
                                        </button>
                                    </form>
                                    <form method="POST" style="display:inline;" action="admin.php#eventos" onsubmit="return confirm('Eliminar este evento?');">
                                        <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo generateCsrfToken(); ?>">
                                        <input type="hidden" name="id_evento" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="delete_event" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Fotos Tab -->
        <div class="tab-pane fade" id="fotos" role="tabpanel">
            <div class="form-section">
                <div class="section-title">Upload de Nova Foto</div>
                
                <?php if (isset($_SESSION['photo_success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($_SESSION['photo_success']); ?>
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                    <?php unset($_SESSION['photo_success']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['photo_error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($_SESSION['photo_error']); ?>
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
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
                        <label for="descricao">Descrição</label>
                        <input type="text" class="form-control" id="descricao" name="descricao" placeholder="Descrição da foto (opcional)">
                    </div>
                    
                    <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="visivel_foto" name="visivel" checked>
                        <label class="form-check-label" for="visivel_foto">Visível na galeria</label>
                    </div>
                    
                    <button type="submit" name="upload_photo" class="btn btn-primary">
                        <i class="fas fa-cloud-upload-alt"></i> Upload Foto
                    </button>
                </form>
            </div>
            
            <div>
                <div class="section-title">Moderação de Fotos</div>
                
                <div class="mb-4">
                    <form method="GET" action="admin.php#fotos" class="form-inline">
                        <label for="filter_status" class="mr-2">Filtro:</label>
                        <select name="filter_status" id="filter_status" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                            <option value="todas" <?php echo (!isset($_GET['filter_status']) || $_GET['filter_status'] === 'todas') ? 'selected' : ''; ?>>Todas</option>
                            <option value="aprovado" <?php echo (isset($_GET['filter_status']) && $_GET['filter_status'] === 'aprovado') ? 'selected' : ''; ?>>Aprovadas</option>
                            <option value="pendente" <?php echo (isset($_GET['filter_status']) && $_GET['filter_status'] === 'pendente') ? 'selected' : ''; ?>>Pendentes</option>
                            <option value="rejeitado" <?php echo (isset($_GET['filter_status']) && $_GET['filter_status'] === 'rejeitado') ? 'selected' : ''; ?>>Rejeitadas</option>
                        </select>
                        <?php
                        $pendingResult = $db->query("SELECT COUNT(*) as count FROM fotos WHERE status = 'pendente'");
                        $pendingCount = $pendingResult->fetchArray(SQLITE3_ASSOC)['count'];
                        if ($pendingCount > 0):
                        ?>
                        <span class="badge badge-warning ml-2">Pendentes: <?php echo $pendingCount; ?></span>
                        <?php endif; ?>
                    </form>
                </div>
                
                <?php
                $pendingPhotos = $db->query("SELECT * FROM fotos WHERE status = 'pendente' ORDER BY data_upload DESC");
                $hasPending = false;
                $pendingList = [];
                while ($row = $pendingPhotos->fetchArray(SQLITE3_ASSOC)) {
                    $pendingList[] = $row;
                    $hasPending = true;
                }
                
                if ($hasPending && (!isset($_GET['filter_status']) || $_GET['filter_status'] === 'todas' || $_GET['filter_status'] === 'pendente')):
                ?>
                <div class="alert alert-info mb-4">
                    <h5><i class="fas fa-clock"></i> Fotos Pendentes de Aprovação</h5>
                </div>
                <div class="row mb-4">
                    <?php foreach ($pendingList as $row): ?>
                    <div class="col-md-4 col-lg-3 mb-4">
                        <div class="card">
                            <img src="<?php echo htmlspecialchars($row['caminho']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['nome_foto']); ?>" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h6 class="card-title" style="color: var(--gold); font-size: 0.9rem;"><?php echo htmlspecialchars($row['nome_foto']); ?></h6>
                                <?php if (!empty($row['descricao'])): ?>
                                    <p class="card-text" style="color: var(--text-light); font-size: 0.85rem;"><?php echo htmlspecialchars($row['descricao']); ?></p>
                                <?php endif; ?>
                                <p class="card-text" style="font-size: 0.8rem; color: var(--text-medium);">
                                    <strong>Por:</strong> <?php echo htmlspecialchars($row['uploaded_by'] ?? 'Desconhecido'); ?><br>
                                    <strong>Data:</strong> 
                                    <?php 
                                    if (!empty($row['data_upload'])) {
                                        $data = DateTime::createFromFormat('Y-m-d H:i:s', $row['data_upload']);
                                        echo $data ? $data->format('d/m/Y H:i') : '—';
                                    } else {
                                        echo '—';
                                    }
                                    ?>
                                </p>
                                <div class="btn-group btn-group-sm d-flex" role="group">
                                    <form method="POST" style="flex: 1; display:inline;" action="admin.php#fotos">
                                        <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo generateCsrfToken(); ?>">
                                        <input type="hidden" name="id_foto" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="approve_photo" class="btn btn-sm btn-success btn-block">
                                            <i class="fas fa-check"></i> Aprovar
                                        </button>
                                    </form>
                                    <form method="POST" style="flex: 1; display:inline;" action="admin.php#fotos" onsubmit="return confirm('Rejeitar esta foto?');">
                                        <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo generateCsrfToken(); ?>">
                                        <input type="hidden" name="id_foto" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="reject_photo" class="btn btn-sm btn-danger btn-block">
                                            <i class="fas fa-times"></i> Rejeitar
                                        </button>
                                    </form>
                                </div>
                                <span class="badge badge-warning mt-2 d-block">Pendente</span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <div class="section-title mt-4">Galeria de Fotos</div>
                <div class="row">
                    <?php
                    $filter = $_GET['filter_status'] ?? 'todas';
                    if ($filter === 'todas') {
                        $result = $db->query("SELECT * FROM fotos ORDER BY data_upload DESC");
                    } else {
                        $stmt = $db->prepare("SELECT * FROM fotos WHERE status = :status ORDER BY data_upload DESC");
                        $stmt->bindValue(':status', $filter, SQLITE3_TEXT);
                        $result = $stmt->execute();
                    }
                    
                    $count = 0;
                    while ($row = $result->fetchArray(SQLITE3_ASSOC)):
                        $count++;
                        if ($row['status'] === 'pendente' && $hasPending && ($filter === 'todas' || $filter === 'pendente')) {
                            continue;
                        }
                    ?>
                    <div class="col-md-4 col-lg-3 mb-4">
                        <div class="card">
                            <img src="<?php echo htmlspecialchars($row['caminho']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['nome_foto']); ?>" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h6 class="card-title" style="color: var(--gold); font-size: 0.9rem;"><?php echo htmlspecialchars($row['nome_foto']); ?></h6>
                                <?php if (!empty($row['descricao'])): ?>
                                    <p class="card-text" style="color: var(--text-light); font-size: 0.85rem;"><?php echo htmlspecialchars($row['descricao']); ?></p>
                                <?php endif; ?>
                                <p class="card-text" style="font-size: 0.8rem; color: var(--text-medium);">
                                    <strong>Por:</strong> <?php echo htmlspecialchars($row['uploaded_by'] ?? 'Admin'); ?><br>
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
                                            <i class="fas fa-eye<?php echo $row['visivel'] == 1 ? '-slash' : ''; ?>"></i>
                                            <?php echo $row['visivel'] == 1 ? 'Ocultar' : 'Mostrar'; ?>
                                        </button>
                                    </form>
                                    <form method="POST" style="display:inline;" action="admin.php#fotos" onsubmit="return confirm('Deletar esta foto?');">
                                        <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo generateCsrfToken(); ?>">
                                        <input type="hidden" name="id_foto" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="delete_photo" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </button>
                                    </form>
                                </div>
                                <div class="mt-2">
                                    <span class="badge badge-<?php echo $row['visivel'] == 1 ? 'success' : 'secondary'; ?>">
                                        <?php echo $row['visivel'] == 1 ? 'Visível' : 'Oculta'; ?>
                                    </span>
                                    <?php
                                    $statusBadgeClass = [
                                        'aprovado' => 'success',
                                        'pendente' => 'warning',
                                        'rejeitado' => 'danger'
                                    ];
                                    $status = $row['status'] ?? 'aprovado';
                                    ?>
                                    <span class="badge badge-<?php echo $statusBadgeClass[$status] ?? 'secondary'; ?>">
                                        <?php echo ucfirst($status); ?>
                                    </span>
                                </div>
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
            </div>
        </div>

        <!-- SMS Marketing Tab -->
        <div class="tab-pane fade" id="sms" role="tabpanel">
            <div class="form-section">
                <div class="section-title">Envio de SMS Marketing</div>
                
                <?php if (isset($_SESSION['sms_success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($_SESSION['sms_success']); ?>
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
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
                    ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['sms_error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($_SESSION['sms_error']); ?>
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                    <?php unset($_SESSION['sms_error']); ?>
                <?php endif; ?>
                
                <p class="alert alert-info">
                    <i class="fas fa-info-circle"></i> <strong>Nota:</strong>Não está a funcionar, sistema opera em modo de simulação.
                </p>
                
                <form method="POST" action="admin.php#sms">
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
                        <i class="fas fa-paper-plane"></i> Enviar SMS
                    </button>
                </form>
            </div>
            
            <div>
                <div class="section-title">Números de Telefone Registados</div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Telefone</th>
                                <th>Email</th>
                                <th>Data de Registo</th>
                            </tr>
                        </thead>
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
                <p class="mt-3" style="color: var(--gold);">
                    <i class="fas fa-users"></i> Total de números registados: <strong><?php echo $count; ?></strong>
                </p>
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
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { 
                        beginAtZero: true,
                        ticks: { color: 'rgba(240, 240, 240, 0.8)' },
                        grid: { color: 'rgba(212, 175, 55, 0.1)' }
                    },
                    x: { 
                        ticks: { color: 'rgba(240, 240, 240, 0.8)' },
                        grid: { color: 'rgba(212, 175, 55, 0.1)' }
                    }
                },
                plugins: { 
                    legend: { 
                        display: false 
                    }
                }
            }
        });
        
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