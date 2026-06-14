<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo '<script>window.location.href = "' . BASE_URL . '/private/views/garantias-contrato/lista.php";</script>';
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
    $g_stmt = $pdo->prepare("SELECT e.codigo_inventario FROM garantias_contratos g LEFT JOIN equipamentos e ON e.id = g.id_equipamento WHERE g.id = ?");
    $g_stmt->execute([$del_id]);
    $gc = $g_stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("DELETE FROM garantias_contratos WHERE id = ?");
    $stmt->execute([$del_id]);

    mhs_historico('garantia-contrato', $del_id, 'Equipamento ' . ($gc['codigo_inventario'] ?? $del_id), 'apagar');

    $_SESSION['success_message'] = 'Garantia/Contrato apagada com sucesso!';
    echo '<script>window.location.href = "' . BASE_URL . '/private/views/garantias-contrato/lista.php";</script>';
    exit;

} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Erro ao apagar Garantia/Contrato: ' . $e->getMessage();
    echo '<script>window.location.href = "' . BASE_URL . '/private/views/garantias-contrato/lista.php";</script>';
    exit;
}
?>
