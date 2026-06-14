<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo '<script>window.location.href = "' . BASE_URL . '/private/views/documentos/lista.php";</script>';
    exit;
}

try {
    $pdo = new PDO(
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8mb4",
        MYSQL_USERNAME,
        MYSQL_PASSWORD,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $del_id = (int)($_POST['id_enc'] ?? 0);
    $d_stmt = $pdo->prepare("SELECT nome_documento, tipo_documento FROM documentos WHERE id = ?");
    $d_stmt->execute([$del_id]);
    $doc = $d_stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("DELETE FROM documentos WHERE id = ?");
    $stmt->execute([$del_id]);

    if ($doc) { mhs_historico('documento', $del_id, ($doc['nome_documento'] ?: $doc['tipo_documento']), 'apagar'); }

    $_SESSION['success_message'] = 'Documento apagado com sucesso!';
    echo '<script>window.location.href = "' . BASE_URL . '/private/views/documentos/lista.php";</script>';
    exit;

} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Erro ao apagar documento: ' . $e->getMessage();
    echo '<script>window.location.href = "' . BASE_URL . '/private/views/documentos/lista.php";</script>';
    exit;
}
?>
