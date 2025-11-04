<?php
session_start();
require_once 'config.php';

try {
    $db = getDbConnection();
    if (!isset($_COOKIE['user_id'])) {
        $userId = bin2hex(random_bytes(16)); 
        setSecureCookie('user_id', $userId, time() + (10 * 365 * 24 * 60 * 60));
    } else {
        $userId = $_COOKIE['user_id']; 
    }
    $query = $db->prepare('SELECT nome FROM tomazia_clientes WHERE user_id = :user_id');
    $query->bindValue(':user_id', $userId, SQLITE3_TEXT);
    $result = $query->execute()->fetchArray(SQLITE3_ASSOC);
    if ($result) {
        $_SESSION['nome'] = htmlspecialchars($result['nome']);
        header("Location: bemvindo.php");
        exit;
    }
} catch (Exception $e) {
    error_log("Error in index.php: " . $e->getMessage());
    echo "Erro: Ocorreu um problema. Por favor, tente novamente.";
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bar da Tomazia</title>
    <link rel="icon" href="img/tomazia.png" type="image/png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css"> 
    <style>
        body {
            position: relative;
            min-height: 100vh;
            background-color: #1a1a1a;
            color: #f0f0f0;
            font-family: 'Montserrat', sans-serif;
        }
        .video-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -2;
        }
        .video-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: -1;
        }
        .navbar {
            background-color: rgba(26, 26, 26, 0.9) !important;
            border-bottom: 1px solid rgba(139, 69, 19, 0.2);
        }
        .form-container {
            max-width: 450px;
            margin: 5% auto;
            background-color: rgba(26, 26, 26, 0.95);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(139, 69, 19, 0.3);
        }
        .form-container h2 {
            color: #8B4513;
            text-align: center;
            margin-bottom: 30px;
            font-family: 'Playfair Display', serif;
        }
        .form-control {
            background-color: rgba(0,0,0,0.3);
            border: 1px solid rgba(139, 69, 19, 0.4);
            color: #f0f0f0;
        }
        .form-control:focus {
            background-color: rgba(0,0,0,0.5);
            border-color: #8B4513;
            color: #ffffff;
            box-shadow: none;
        }
        .btn-primary {
            background-color: #8B4513;
            border-color: #8B4513;
            color: #1a1a1a;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: transparent;
            border-color: #8B4513;
            color: #8B4513;
        }
        .form-check-label a {
            color: #8B4513;
            cursor: pointer;
            text-decoration: none;
        }
        .form-check-label a:hover { text-decoration: underline; }
        
        .modal-content {
            background-color: #1e1e1e;
            color: #f0f0f0;
            border: 1px solid rgba(139, 69, 19, 0.3);
        }
        .modal-header {
            border-bottom: 1px solid rgba(139, 69, 19, 0.3);
        }
        .modal-header .close {
            color: #f0f0f0;
            text-shadow: none;
        }
        .modal-title {
            color: #8B4513;
            font-family: 'Playfair Display', serif;
        }
    </style>
</head>
<body>
    <video class="video-background" autoplay loop muted playsinline>
        <source src="img/3772392-hd_1920_1080_25fps.mp4" type="video/mp4">
    </video>
    <div class="video-overlay"></div>
    
    <nav class="navbar navbar-expand-lg navbar-dark">
        <a class="navbar-brand" href="index.php"><img src="img/tomazia.png" height="100"></a>
    </nav>

    <div class="container">
        <div class="form-container">
            <h2>Bem-vindo ao Bar da Tomazia</h2>
            <form method="POST" action="form.php" id="registrationForm">
                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo generateCsrfToken(); ?>">
                <div class="form-group">
                    <label for="nome">Nome</label>
                    <input type="text" class="form-control" id="nome" name="nome" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="telefone">Telefone</label>
                    <input type="text" class="form-control" id="telefone" name="telefone" required>
                </div>
                <div class="form-group form-check">
                    <input type="checkbox" class="form-check-input" id="termos" name="termos" required>
                    <label class="form-check-label" for="termos">
                        Eu li e aceito os <a data-toggle="modal" data-target="#termsModal">Termos e Condições</a>
                    </label>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Enviar</button>
            </form>
        </div>
    </div>

    <div class="modal fade" id="termsModal" tabindex="-1" role="dialog" aria-labelledby="termsModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="termsModalLabel">Termos e Condições</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
                <p class="last-updated" style="color:#888; font-size:0.9rem;">Última atualização: <?php echo date('d/m/Y'); ?></p>
                <p>Bem-vindo ao Bar da Tomazia. Ao utilizar os nossos serviços e registar-se no nosso sistema, você concorda com os seguintes termos e condições. Por favor, leia atentamente antes de prosseguir.</p>

                <h6 style="color:#8B4513;">1. Aceitação dos Termos</h6>
                <p>Ao aceder e utilizar este website e os serviços do Bar da Tomazia, você aceita estar vinculado a estes Termos e Condições, todas as leis e regulamentos aplicáveis. Se não concordar com algum destes termos, está proibido de usar ou aceder a este site.</p>

                <h6 style="color:#8B4513;">2. Recolha e Uso de Dados Pessoais</h6>
                <p>Ao registar-se no nosso sistema, você concorda em fornecer informações pessoais verídicas, incluindo:</p>
                <ul>
                    <li>Nome completo</li>
                    <li>Endereço de email válido</li>
                    <li>Número de telefone</li>
                </ul>
                <p>Estes dados serão utilizados exclusivamente para:</p>
                <ul>
                    <li>Comunicações relacionadas com os serviços do Bar da Tomazia</li>
                    <li>Ofertas especiais e promoções</li>
                    <li>Melhorar a experiência do cliente</li>
                </ul>

                <h6 style="color:#8B4513;">3. Proteção de Dados</h6>
                <p>O Bar da Tomazia compromete-se a proteger a sua privacidade e os seus dados pessoais de acordo com o Regulamento Geral de Proteção de Dados (RGPD). Os seus dados não serão partilhados com terceiros sem o seu consentimento explícito, exceto quando exigido por lei.</p>

                <h6 style="color:#8B4513;">4. Cookies e Tecnologias de Rastreamento</h6>
                <p>Este website utiliza cookies para melhorar a experiência do utilizador. Um cookie é um identificador único armazenado no seu dispositivo que nos permite reconhecê-lo em visitas futuras. Pode desativar os cookies nas configurações do seu navegador, mas algumas funcionalidades do site podem não funcionar corretamente.</p>

                <h6 style="color:#8B4513;">5. Responsabilidade do Utilizador</h6>
                <p>Ao utilizar os nossos serviços, você concorda em:</p>
                <ul>
                    <li>Fornecer informações verdadeiras e atualizadas</li>
                    <li>Manter a confidencialidade das suas credenciais de acesso</li>
                    <li>Não utilizar o serviço para atividades ilegais ou não autorizadas</li>
                    <li>Respeitar os direitos de propriedade intelectual do Bar da Tomazia</li>
                </ul>

                <h6 style="color:#8B4513;">6. Limitação de Responsabilidade</h6>
                <p>O Bar da Tomazia não se responsabiliza por quaisquer danos diretos, indiretos, incidentais ou consequenciais resultantes do uso ou incapacidade de usar os nossos serviços.</p>

                <h6 style="color:#8B4513;">7. Modificações aos Termos</h6>
                <p>Reservamo-nos o direito de modificar estes Termos e Condições a qualquer momento. As alterações entrarão em vigor imediatamente após a sua publicação no website. É da sua responsabilidade rever periodicamente estes termos.</p>

                <h6 style="color:#8B4513;">8. Lei Aplicável</h6>
                <p>Estes Termos e Condições são regidos pelas leis de Portugal. Qualquer disputa relacionada com estes termos será submetida à jurisdição exclusiva dos tribunais portugueses.</p>

                <h6 style="color:#8B4513;">9. Contacto</h6>
                <p>Para questões relacionadas com estes Termos e Condições ou com a proteção dos seus dados pessoais, por favor contacte-nos através do Bar da Tomazia.</p>
            </div>
          <div class="modal-footer" style="border-top: 1px solid rgba(139, 69, 19, 0.3);">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
          </div>
        </div>
      </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>