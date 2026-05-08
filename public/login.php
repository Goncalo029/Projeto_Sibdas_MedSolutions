<?php
session_start();

// Verificar se form foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['text_username'] ?? '';
    $password = $_POST['text_password'] ?? '';
    
    // Validação básica
    if (!empty($email) && !empty($password)) {
        // Aqui você adiciona a lógica de autenticação com a BD
        // Por enquanto é apenas um placeholder
        
        // Exemplo: Validação simples (SUBSTITUIR COM BD)
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Simulação de login bem-sucedido
            $_SESSION['user_email'] = $email;
            $_SESSION['user_logged_in'] = true;
            
            // Redirecionar para painel privado
            header('Location: ../private/index.php');
            exit;
        } else {
            $error_msg = 'E-mail inválido.';
        }
    } else {
        $error_msg = 'Por favor preencha todos os campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | MedSolutions</title>
    <link rel="shortcut icon" href="assets/images/logo-medsoft.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/fontawesome/all.min.css">
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>
<body class="mhs-auth-page">

    <div class="mhs-auth-blob mhs-auth-blob--1"></div>
    <div class="mhs-auth-blob mhs-auth-blob--2"></div>
    <div class="mhs-auth-blob mhs-auth-blob--3"></div>

    <main class="mhs-auth-main">
        <section class="mhs-auth-card">
            <div class="mhs-auth-brand">
                <span class="mhs-auth-brand-mark">
                    <img src="assets/images/logo-medsoft.svg" alt="MedSolutions">
                </span>
                <div>
                    <strong>MedSolutions</strong>
                    <span>Plataforma hospitalar centralizada</span>
                </div>
            </div>

            <div class="mhs-auth-card-head">
                <div class="mhs-auth-icon-circle">
                    <i class="fa-solid fa-lock"></i>
                </div>
                <h2>Bem-vindo de volta</h2>
                <p class="mhs-auth-subtitle">Introduza as suas credenciais para aceder ao painel.</p>
            </div>

            <?php if (!empty($error_msg)): ?>
            <div class="mhs-auth-alert mhs-auth-alert--danger">
                <i class="fa-solid fa-circle-exclamation"></i>
                <p><?php echo htmlspecialchars($error_msg); ?></p>
            </div>
            <?php endif; ?>

            <form name="mhs_login_form" action="login.php" method="post" class="mhs-auth-form" autocomplete="off">
                <div class="mhs-input-group">
                    <label for="text_username">E-mail</label>
                    <div class="mhs-input-wrap">
                        <i class="fa-solid fa-envelope"></i>
                        <input type="email" id="text_username" name="text_username" placeholder="Introduza o seu e-mail" required>
                    </div>
                </div>

                <div class="mhs-input-group">
                    <label for="text_password">Password</label>
                    <div class="mhs-input-wrap">
                        <i class="fa-solid fa-key"></i>
                        <input type="password" id="text_password" name="text_password" placeholder="Introduza a sua password" required>
                        <button type="button" class="mhs-toggle-pw" aria-label="Mostrar password" onclick="togglePw()">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="mhs-btn-primary mhs-auth-submit">
                    Entrar no Painel <i class="fa-solid fa-arrow-right-to-bracket"></i>
                </button>
            </form>

            <div class="mhs-auth-footer-link">
                <a class="mhs-auth-back" href="index.php">
                    <i class="fa-solid fa-arrow-left"></i> Voltar ao website
                </a>
            </div>
        </section>
    </main>

    <script>
    function togglePw() {
        const inp = document.getElementById('text_password');
        const icon = document.querySelector('.mhs-toggle-pw i');
        if (inp.type === 'password') {
            inp.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            inp.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
    </script>
</body>
</html>
