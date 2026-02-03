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

// Handle photo upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_photo'])) {
    if (!isset($_POST[CSRF_TOKEN_NAME]) || !verifyCsrfToken($_POST[CSRF_TOKEN_NAME])) {
        $_SESSION['upload_error'] = "Erro: Token CSRF inválido.";
        header('Location: fotos.php');
        exit();
    }
    
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $descricao = trim($_POST['descricao'] ?? '');
        
        // Validar tipo de arquivo
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = $_FILES['foto']['type'];
        
        if (!in_array($fileType, $allowedTypes)) {
            $_SESSION['upload_error'] = "Tipo de arquivo não permitido. Use apenas JPEG, PNG, GIF ou WEBP.";
            header('Location: fotos.php');
            exit();
        }
        
        // Validar tamanho (máximo 5MB)
        if ($_FILES['foto']['size'] > 5 * 1024 * 1024) {
            $_SESSION['upload_error'] = "Arquivo muito grande. Tamanho máximo: 5MB.";
            header('Location: fotos.php');
            exit();
        }
        
        // Gerar nome único para o arquivo
        $extensao = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $nomeArquivo = 'foto_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $extensao;
        $caminhoDestino = __DIR__ . '/img/uploads/' . $nomeArquivo;
        $caminhoRelativo = 'img/uploads/' . $nomeArquivo;
        
        // Mover arquivo para diretório de uploads
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $caminhoDestino)) {
            try {
                $db = getDbConnection();
                // Salvar no banco de dados com status pendente
                $stmt = $db->prepare('INSERT INTO fotos (nome_foto, caminho, descricao, visivel, status, uploaded_by, is_admin_upload) VALUES (:nome_foto, :caminho, :descricao, 0, :status, :uploaded_by, 0)');
                $stmt->bindValue(':nome_foto', $_FILES['foto']['name'], SQLITE3_TEXT);
                $stmt->bindValue(':caminho', $caminhoRelativo, SQLITE3_TEXT);
                $stmt->bindValue(':descricao', $descricao, SQLITE3_TEXT);
                $stmt->bindValue(':status', 'pendente', SQLITE3_TEXT);
                $stmt->bindValue(':uploaded_by', $nome, SQLITE3_TEXT);
                $stmt->execute();
                $db->close();
                
                $_SESSION['upload_success'] = "Foto enviada com sucesso! Será analisada antes de aparecer na galeria.";
            } catch (Exception $e) {
                // Se falhar ao salvar no BD, apagar o arquivo
                if (file_exists($caminhoDestino)) {
                    unlink($caminhoDestino);
                }
                $_SESSION['upload_error'] = "Erro ao salvar foto na base de dados.";
                error_log("Error saving photo: " . $e->getMessage());
            }
        } else {
            $_SESSION['upload_error'] = "Erro ao fazer upload da foto.";
        }
    } else {
        $_SESSION['upload_error'] = "Nenhuma foto foi selecionada ou ocorreu um erro no upload.";
    }
    
    header('Location: fotos.php');
    exit();
}

// Get user's photos
$userPhotos = [];
try {
    $db = getDbConnection();
    $stmt = $db->prepare('SELECT * FROM fotos WHERE uploaded_by = :uploaded_by ORDER BY data_upload DESC');
    $stmt->bindValue(':uploaded_by', $nome, SQLITE3_TEXT);
    $result = $stmt->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $userPhotos[] = $row;
    }
    $db->close();
} catch (Exception $e) {
    error_log("Error loading user photos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Foto - Bar da Tomazia</title>
    <link rel="icon" href="img/pngico.png" type="image/png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Inter:wght@400;500;600;700&family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', 'Montserrat', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #5D1F3A;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            color: #f0f0f0;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        .navbar {
            background-color: rgba(93, 31, 58, 0.95) !important;
            border-bottom: 2px solid rgba(212, 175, 55, 0.25);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        .navbar-brand img {
            display: block;
            margin: 0 auto;
            transition: opacity 0.3s ease;
        }
        .navbar-brand img:hover {
            opacity: 0.7;
        }
        .main-container {
            max-width: 950px;
            margin: 48px auto;
            padding: 24px;
        }
        .upload-section, .photos-section {
            background-color: rgba(61, 15, 36, 0.95);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.5), 0 4px 12px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(212, 175, 55, 0.3);
            margin-bottom: 40px;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        h1, h2 {
            color: #D4AF37;
            font-family: 'Playfair Display', serif;
            font-weight: 900;
            margin-bottom: 24px;
            letter-spacing: -0.02em;
        }
        h1 {
            font-size: 2.75rem;
        }
        h2 {
            font-size: 2.25rem;
        }
        .info-message {
            background-color: rgba(212, 175, 55, 0.12);
            border-left: 4px solid #D4AF37;
            padding: 18px;
            margin-top: 24px;
            border-radius: 8px;
            color: #D4AF37;
            font-weight: 500;
            line-height: 1.6;
        }
        .form-control, .form-control-file {
            background-color: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(212, 175, 55, 0.3);
            color: #f0f0f0;
            border-radius: 10px;
            padding: 14px 16px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 1rem;
        }
        .form-control:focus, .form-control-file:focus {
            background-color: rgba(255, 255, 255, 0.15);
            border-color: #D4AF37;
            color: #f0f0f0;
            box-shadow: 0 0 0 4px rgba(212, 175, 55, 0.15);
            outline: none;
        }
        .form-control::placeholder {
            color: rgba(240, 240, 240, 0.5);
        }
        .btn-primary {
            background-color: #D4AF37;
            border: 2px solid #D4AF37;
            color: #3D0F24;
            font-weight: 600;
            padding: 14px 32px;
            border-radius: 10px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 1.05rem;
            letter-spacing: 0.02em;
            box-shadow: 0 4px 12px rgba(212, 175, 55, 0.3);
        }
        .btn-primary:hover {
            background-color: #C19B2E;
            border-color: #C19B2E;
            color: #3D0F24;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(212, 175, 55, 0.4);
        }
        .btn-primary:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(212, 175, 55, 0.3);
        }
        .photo-card {
            background-color: rgba(93, 31, 58, 0.5);
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 24px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }
        .photo-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.4), 0 4px 12px rgba(212, 175, 55, 0.2);
            border-color: rgba(212, 175, 55, 0.35);
        }
        .photo-card img {
            width: 100%;
            height: 220px;
            object-fit: cover;
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .photo-card:hover img {
            transform: scale(1.05);
        }
        .photo-card-body {
            padding: 20px;
        }
        .badge-aprovado {
            background-color: #28a745;
        }
        .badge-pendente {
            background-color: #ffc107;
            color: #000;
        }
        .badge-rejeitado {
            background-color: #dc3545;
        }
        label {
            color: #D4AF37;
            font-weight: 600;
            font-size: 0.95rem;
            letter-spacing: 0.01em;
            margin-bottom: 10px;
        }
        small {
            color: rgba(240, 240, 240, 0.7);
            line-height: 1.6;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .alert {
            border-radius: 10px;
            padding: 16px 20px;
            border: none;
            font-weight: 500;
        }
        .alert-success {
            background-color: rgba(40, 167, 69, 0.2);
            color: #5cb85c;
            border-left: 4px solid #5cb85c;
        }
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.2);
            color: #f5c2c7;
            border-left: 4px solid #dc3545;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
    <a class="navbar-brand" href="bemvindo.php"><img src="img/tomazia.png" height="100" width="100"></a>
</nav>

<div class="main-container">
    <!-- Upload Section -->
    <div class="upload-section">
        <h1>📸 Upload de Foto</h1>
        <p>Bem-vindo, <strong style="color: #D4AF37;"><?php echo htmlspecialchars($nome); ?></strong>!</p>
        
        <?php if (isset($_SESSION['upload_success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_SESSION['upload_success']); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php unset($_SESSION['upload_success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['upload_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_SESSION['upload_error']); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php unset($_SESSION['upload_error']); ?>
        <?php endif; ?>
        
        <form method="POST" action="fotos.php" enctype="multipart/form-data">
            <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo generateCsrfToken(); ?>">
            
            <div class="form-group">
                <label for="foto">Selecionar Foto *</label>
                <input type="file" class="form-control-file" id="foto" name="foto" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" required>
                <small class="form-text">
                    Formatos aceitos: JPEG, PNG, GIF, WEBP. Tamanho máximo: 5MB
                </small>
            </div>
            
            <div class="form-group">
                <label for="descricao">Descrição (opcional)</label>
                <textarea class="form-control" id="descricao" name="descricao" rows="3" maxlength="255" placeholder="Adicione uma descrição para a sua foto..."></textarea>
            </div>
            
            <button type="submit" name="upload_photo" class="btn btn-primary btn-block">Enviar Foto</button>
        </form>
        
        <div class="info-message">
            <strong>ℹ️ Informação:</strong> A tua foto será analisada por um administrador antes de aparecer na galeria pública.
        </div>
    </div>
    
    <!-- User's Photos Section -->
    <div class="photos-section">
        <h2>As Minhas Fotos</h2>
        
        <?php if (count($userPhotos) > 0): ?>
            <div class="row">
                <?php foreach ($userPhotos as $foto): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="photo-card">
                            <img src="<?php echo htmlspecialchars($foto['caminho']); ?>" alt="<?php echo htmlspecialchars($foto['nome_foto']); ?>">
                            <div class="photo-card-body">
                                <?php if (!empty($foto['descricao'])): ?>
                                    <p style="font-size: 0.9rem; margin-bottom: 10px; color: #f0f0f0;">
                                        <?php echo htmlspecialchars($foto['descricao']); ?>
                                    </p>
                                <?php endif; ?>
                                <p style="font-size: 0.8rem; color: rgba(240, 240, 240, 0.7); margin-bottom: 10px;">
                                    <?php 
                                    if (!empty($foto['data_upload'])) {
                                        $data = DateTime::createFromFormat('Y-m-d H:i:s', $foto['data_upload']);
                                        echo $data ? $data->format('d/m/Y H:i') : '—';
                                    } else {
                                        echo '—';
                                    }
                                    ?>
                                </p>
                                <span class="badge badge-<?php echo htmlspecialchars($foto['status']); ?>">
                                    <?php 
                                    $statusMap = [
                                        'aprovado' => '✓ Aprovado',
                                        'pendente' => '⏳ Pendente',
                                        'rejeitado' => '✗ Rejeitado'
                                    ];
                                    echo $statusMap[$foto['status']] ?? $foto['status'];
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center" style="padding: 40px;">
                <p style="color: rgba(240, 240, 240, 0.7); font-size: 1.1rem;">
                    Ainda não enviaste nenhuma foto. Usa o formulário acima para enviar a tua primeira foto!
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>