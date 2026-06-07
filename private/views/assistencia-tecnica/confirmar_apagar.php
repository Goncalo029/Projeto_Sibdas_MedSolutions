<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: lista.php'); exit; }

$id = (int)($_POST['id_enc'] ?? 0);
if (!$id) { $_SESSION['error_message'] = 'ID inválido.'; header('Location: lista.php'); exit; }

try {
    mhs_pdo()->prepare("UPDATE assistencia_tecnica SET deleted_at=NOW(), updated_at=NOW() WHERE id=?")->execute([$id]);
    $_SESSION['success_message'] = 'Contacto apagado com sucesso.';
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Erro ao apagar.';
}

header('Location: lista.php'); exit;
