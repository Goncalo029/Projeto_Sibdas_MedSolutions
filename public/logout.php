<?php
/**
 * Logout
 * Termina a sessão do utilizador e redireciona para o login.
 * Regista o evento de logout na base de dados.
 */

session_start();

// Guardar os dados da sessão antes de a destruir (para o registo de log)
$user_id    = $_SESSION['user_id']    ?? null;
$user_email = $_SESSION['user_email'] ?? null;

// ─── Registar o logout na base de dados ──────────────────────────────────────
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
        // Se falhar o log, continuar na mesma com o logout
    }
}

// ─── Destruir a sessão ───────────────────────────────────────────────────────
session_destroy();

// Redirecionar para a página de login
header('Location: login.php');
exit;
?>
