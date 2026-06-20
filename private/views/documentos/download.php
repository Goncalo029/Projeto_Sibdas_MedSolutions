<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: lista.php'); exit; }

$stmt = mhs_pdo()->prepare("SELECT nome_ficheiro, ficheiro_conteudo, ficheiro_mime FROM documentos WHERE id = ? AND eliminado_em IS NULL");
$stmt->execute([$id]);
$doc = $stmt->fetch();

if (!$doc || $doc->ficheiro_conteudo === null) {
    $_SESSION['error_message'] = 'Este documento não tem ficheiro associado.';
    header('Location: lista.php'); exit;
}

$nome = $doc->nome_ficheiro ?: ('documento_' . $id . '.pdf');
header('Content-Type: ' . ($doc->ficheiro_mime ?: 'application/pdf'));
header('Content-Disposition: attachment; filename="' . $nome . '"');
header('Content-Length: ' . strlen($doc->ficheiro_conteudo));
echo $doc->ficheiro_conteudo;
exit;
