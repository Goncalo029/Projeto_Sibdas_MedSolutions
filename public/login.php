<?php
// pagina de login
session_start();

// vai buscar as mensagens de erro que ficaram guardadas na sessao
$validation_errors = $_SESSION['validation_errors'] ?? [];
$server_error = $_SESSION['server_error'] ?? '';
unset($_SESSION['validation_errors']);
unset($_SESSION['server_error']);

// junta os erros todos numa so mensagem para mostrar
$error_msg = $server_error;
if (!empty($validation_errors)) {
    $error_msg = implode(' ', $validation_errors);
}
// versao do CSS (com base na data do ficheiro) para forcar o browser a recarregar
$css_ver = @filemtime(__DIR__ . '/assets/css/medsolutions.css') ?: time();
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
    <link rel="stylesheet" href="assets/css/medsolutions.css?v=<?= $css_ver ?>">
</head>
<body class="mhs-auth-page">

    <div class="mhs-auth-blob mhs-auth-blob--1"></div>
    <div class="mhs-auth-blob mhs-auth-blob--2"></div>
    <div class="mhs-auth-blob mhs-auth-blob--3"></div>

    <main class="mhs-auth-main">
        <div class="mhs-auth-back-top">
            <a class="mhs-auth-back-link" href="index.php">
                <i class="fa-solid fa-arrow-left"></i> Voltar ao website
            </a>
        </div>
        <section class="mhs-auth-card">
            <div class="mhs-auth-brand">
                <span class="mhs-auth-brand-mark">
                    <img src="assets/images/logo-medsoft.svg" alt="MedSolutions">
                </span>
                <div>
                    <strong>MedSolutions</strong>
                    <span>Gestão de equipamentos médicos</span>
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

            <form name="mhs_login_form" action="/sibdas/1220673/MedSolutions/private/processa_login.php" method="post" class="mhs-auth-form" autocomplete="off">
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

            <div class="mhs-auth-demo">
                <div class="mhs-auth-demo-header">
                    <span class="mhs-auth-demo-line"></span>
                    <span class="mhs-auth-demo-label">Acesso Demo</span>
                    <span class="mhs-auth-demo-line"></span>
                </div>
                <div class="mhs-auth-demo-btns">
                    <button type="button" onclick="fillDemo('admin@hospital.pt','admin123')" class="mhs-auth-demo-btn mhs-auth-demo-btn--admin">
                        <i class="fa-solid fa-user-shield"></i> Admin
                    </button>
                    <button type="button" onclick="fillDemo('tecnico@hospital.pt','tecnico123')" class="mhs-auth-demo-btn mhs-auth-demo-btn--tecnico">
                        <i class="fa-solid fa-user-gear"></i> Técnico
                    </button>
                </div>
            </div>

        </section>
    </main>

    <script>
    function fillDemo(email, password) {
        document.getElementById('text_username').value = email;
        document.getElementById('text_password').value = password;
    }
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
