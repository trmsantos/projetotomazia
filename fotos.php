<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['nome'])) {
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
        
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = $_FILES['foto']['type'];
        
        if (!in_array($fileType, $allowedTypes)) {
            $_SESSION['upload_error'] = "Tipo de arquivo não permitido. Use apenas JPEG, PNG, GIF ou WEBP.";
            header('Location: fotos.php');
            exit();
        }
        
        if ($_FILES['foto']['size'] > 5 * 1024 * 1024) {
            $_SESSION['upload_error'] = "Arquivo muito grande. Tamanho máximo: 5MB.";
            header('Location: fotos.php');
            exit();
        }
        
        $extensao = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $nomeArquivo = 'foto_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $extensao;
        $caminhoDestino = __DIR__ . '/img/uploads/' . $nomeArquivo;
        $caminhoRelativo = 'img/uploads/' . $nomeArquivo;
        
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $caminhoDestino)) {
            try {
                $db = getDbConnection();
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
    <title>Enviar Fotos - Bar da Tomazia</title>
    <link rel="icon" href="img/tomazia.png" type="image/png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --burgundy: #5D1F3A;
            --burgundy-dark: #3D0F24;
            --gold: #D4AF37;
            --gold-light: #E8C76F;
            --text-light: #f0f0f0;
            --text-secondary: #cccccc;
            --text-tertiary: #a0a0a0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: linear-gradient(135deg, var(--burgundy) 0%, var(--burgundy-dark) 100%);
            color: var(--text-light);
            min-height: 100vh;
        }

        .header-section {
            background: rgba(61, 15, 36, 0.95);
            backdrop-filter: blur(15px);
            padding: 2rem 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid rgba(212, 175, 55, 0.25);
        }

        .header-content {
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .back-btn {
            position: absolute;
            left: 1.5rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--gold);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem;
            border-radius: 8px;
        }

        .back-btn:hover {
            background: rgba(212, 175, 55, 0.15);
            color: var(--gold-light);
            text-decoration: none;
            transform: translateX(-4px);
        }

        .header-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(2rem, 5vw, 2.5rem);
            font-weight: 700;
            color: var(--gold);
            margin: 0;
        }

        .main-container {
            max-width: 1000px;
            margin: 3rem auto;
            padding: 0 1.5rem;
        }

        .content-card {
            background: rgba(61, 15, 36, 0.85);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 2.5rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(212, 175, 55, 0.2);
            margin-bottom: 2.5rem;
        }

        .card-title {
            font-family: 'Cormorant Garamond', serif;
            color: var(--gold);
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }

        .welcome-text {
            color: var(--text-secondary);
            margin-bottom: 2rem;
        }

        .welcome-text strong {
            color: var(--gold);
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
            background: rgba(212, 175, 55, 0.12);
            border-left: 4px solid var(--gold);
            color: var(--gold-light);
            margin-top: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.75rem;
        }

        .form-group label {
            color: var(--gold);
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-control, .form-control-file {
            background: rgba(93, 31, 58, 0.4);
            border: 2px solid rgba(212, 175, 55, 0.3);
            color: var(--text-light);
            border-radius: 8px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 4px rgba(212, 175, 55, 0.1);
            background: rgba(93, 31, 58, 0.6);
            color: #fff;
            outline: none;
        }

        .form-control::placeholder {
            color: rgba(240, 240, 240, 0.5);
        }

        .form-text {
            color: rgba(240, 240, 240, 0.6);
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--gold), var(--gold-light));
            border: none;
            color: var(--burgundy-dark);
            font-weight: 600;
            padding: 0.875rem 2rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(212, 175, 55, 0.3);
            width: 100%;
            font-size: 1.0625rem;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(212, 175, 55, 0.4);
            color: var(--burgundy-dark);
        }

        .photo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .photo-card {
            background: rgba(93, 31, 58, 0.4);
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .photo-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.4);
            border-color: rgba(212, 175, 55, 0.35);
        }

        .photo-card img {
            width: 100%;
            height: 220px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .photo-card:hover img {
            transform: scale(1.05);
        }

        .photo-card-body {
            padding: 1.25rem;
        }

        .photo-description {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 0.75rem;
        }

        .photo-meta {
            color: rgba(240, 240, 240, 0.6);
            font-size: 0.8rem;
            margin-bottom: 0.75rem;
        }

        .badge {
            padding: 0.5rem 0.75rem;
            font-weight: 600;
            border-radius: 6px;
        }

        .badge-success { background-color: #28a745; }
        .badge-warning { background-color: #ffc107; color: #000; }
        .badge-danger { background-color: #dc3545; }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-state p {
            color: rgba(240, 240, 240, 0.6);
            font-size: 1.0625rem;
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
            }

            .back-btn {
                position: static;
            }

            .content-card {
                padding: 1.75rem;
            }

            .photo-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="header-section">
        <div class="header-content">
            <a href="bemvindo.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
            <h1 class="header-title">Enviar Fotos</h1>
        </div>
    </header>

    <div class="main-container">
        <div class="content-card">
            <h2 class="card-title">Upload de Foto</h2>
            <p class="welcome-text">Bem-vindo, <strong><?php echo htmlspecialchars($nome); ?></strong></p>
            
            <?php if (isset($_SESSION['upload_success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_SESSION['upload_success']); ?>
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['upload_success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['upload_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_SESSION['upload_error']); ?>
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['upload_error']); ?>
            <?php endif; ?>
            
            <form method="POST" action="fotos.php" enctype="multipart/form-data">
                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo generateCsrfToken(); ?>">
                
                <div class="form-group">
                    <label for="foto"><i class="fas fa-image"></i> Selecionar Foto</label>
                    <input type="file" class="form-control-file" id="foto" name="foto" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" required>
                    <small class="form-text">
                        Formatos aceitos: JPEG, PNG, GIF, WEBP. Tamanho máximo: 5MB
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="descricao"><i class="fas fa-align-left"></i> Descrição</label>
                    <textarea class="form-control" id="descricao" name="descricao" rows="3" maxlength="255" placeholder="Adicione uma descrição para a sua foto (opcional)"></textarea>
                </div>
                
                <button type="submit" name="upload_photo" class="btn btn-primary">
                    <i class="fas fa-cloud-upload-alt"></i> Enviar Foto
                </button>
            </form>
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> <strong>Informação:</strong> A tua foto será analisada por um administrador antes de aparecer na galeria pública.
            </div>
        </div>
        
        <div class="content-card">
            <h2 class="card-title">As Minhas Fotos</h2>
            
            <?php if (count($userPhotos) > 0): ?>
                <div class="photo-grid">
                    <?php foreach ($userPhotos as $foto): ?>
                        <div class="photo-card">
                            <img src="<?php echo htmlspecialchars($foto['caminho']); ?>" alt="<?php echo htmlspecialchars($foto['nome_foto']); ?>">
                            <div class="photo-card-body">
                                <?php if (!empty($foto['descricao'])): ?>
                                    <p class="photo-description">
                                        <?php echo htmlspecialchars($foto['descricao']); ?>
                                    </p>
                                <?php endif; ?>
                                <p class="photo-meta">
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
                                        'aprovado' => 'Aprovado',
                                        'pendente' => 'Pendente',
                                        'rejeitado' => 'Rejeitado'
                                    ];
                                    echo $statusMap[$foto['status']] ?? $foto['status'];
                                    ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-images" style="font-size: 4rem; opacity: 0.3; margin-bottom: 1rem;"></i>
                    <p>
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