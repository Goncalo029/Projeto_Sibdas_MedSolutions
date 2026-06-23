<?php
// apaga uma mensagem de contacto (so admin)
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();

// so o admin pode apagar
if (!is_admin()) {
    http_response_code(403);
    exit;
}

// so aceita pedidos por POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: lista.php');
    exit;
}

// le o id da mensagem a apagar
$id_raw = $_POST['id_enc'] ?? '';
$id = (int)$id_raw;

// se o id nao for valido, volta com erro
if ($id <= 0) {
    $_SESSION['error_message'] = 'ID inválido.';
    header('Location: lista.php');
    exit;
}

try {
    // vai buscar o nome de quem enviou (para o historico)
    $nome_stmt = mhs_pdo()->prepare("SELECT nome FROM mensagens_contacto WHERE id = ?");
    $nome_stmt->execute([$id]);
    $msg = $nome_stmt->fetch();

    // marca a mensagem como eliminada (apaga "logico", fica na BD mas escondida)
    $stmt = mhs_pdo()->prepare("
        UPDATE mensagens_contacto SET eliminado_em = NOW(), atualizado_em = NOW() WHERE id = ?
    ");
    $stmt->execute([$id]);

    // guarda no historico
    mhs_historico('mensagem', $id, 'Mensagem de ' . ($msg->nome ?? ('#' . $id)), 'apagar');

    $_SESSION['success_message'] = 'Mensagem apagada com sucesso.';
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Não foi possível apagar a mensagem.';
}

// volta sempre para a lista
header('Location: lista.php');
exit;
