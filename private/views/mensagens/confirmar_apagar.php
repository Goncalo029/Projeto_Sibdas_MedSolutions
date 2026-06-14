<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();

if (!is_admin()) {
    http_response_code(403);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: lista.php');
    exit;
}

$id_raw = $_POST['id_enc'] ?? '';
$id = (int)$id_raw;

if ($id <= 0) {
    $_SESSION['error_message'] = 'ID inválido.';
    header('Location: lista.php');
    exit;
}

try {
    $nome_stmt = mhs_pdo()->prepare("SELECT nome FROM mensagens_contacto WHERE id = ?");
    $nome_stmt->execute([$id]);
    $msg = $nome_stmt->fetch();

    $stmt = mhs_pdo()->prepare("
        UPDATE mensagens_contacto SET deleted_at = NOW(), updated_at = NOW() WHERE id = ?
    ");
    $stmt->execute([$id]);

    mhs_historico('mensagem', $id, 'Mensagem de ' . ($msg->nome ?? ('#' . $id)), 'apagar');

    $_SESSION['success_message'] = 'Mensagem apagada com sucesso.';
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Não foi possível apagar a mensagem.';
}

header('Location: lista.php');
exit;
