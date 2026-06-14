<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: lista.php'); exit; }

$stmt = mhs_pdo()->prepare("SELECT nome_documento, nome_ficheiro FROM documentos WHERE id = ? AND deleted_at IS NULL");
$stmt->execute([$id]);
$doc = $stmt->fetch();

if (!$doc || !$doc->nome_ficheiro) {
    $_SESSION['error_message'] = 'Este documento não tem ficheiro associado.';
    header('Location: lista.php'); exit;
}

$dir  = __DIR__ . '/../../uploads/documentos';
$path = $dir . '/' . basename($doc->nome_ficheiro);

if (!is_file($path)) {
    $_SESSION['error_message'] = 'Ficheiro não encontrado no servidor.';
    header('Location: lista.php'); exit;
}

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . basename($doc->nome_ficheiro) . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
exit;
