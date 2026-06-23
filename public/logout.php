<?php
// termina a sessao do utilizador e volta para o login
session_start();

// guarda os dados da sessao antes de a apagar (para registar no historico)
$user_id    = $_SESSION['user_id']    ?? null;
$user_email = $_SESSION['user_email'] ?? null;

// regista o logout na base de dados
if ($user_id && $user_email) {
    try {
        require_once __DIR__ . '/../config/config.php';
        $pdo = new PDO(
            "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8mb4",
            MYSQL_USERNAME,
            MYSQL_PASSWORD,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $pdo->prepare(
            "INSERT INTO historico_alteracoes (entidade, entidade_id, entidade_nome, acao, detalhe, utilizador, criado_em)
             VALUES ('autenticacao', ?, ?, 'logout', ?, ?, NOW())"
        )->execute([
            $user_id,
            $user_email,
            'Sessão terminada — IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'desconhecido'),
            $user_email
        ]);
    } catch (Exception $e) {
        // se o registo falhar, faz na mesma o logout
    }
}

// apaga a sessao
session_destroy();

// volta para a pagina de login
header('Location: login.php');
exit;
?>
