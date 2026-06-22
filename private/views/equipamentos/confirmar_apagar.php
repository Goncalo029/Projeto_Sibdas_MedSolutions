<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();
require_admin(); // apenas admin pode apagar

// Manter os filtros ativos: voltar à lista com a mesma query string (validada)
$ret = $_POST['return_qs'] ?? '';
$ret = preg_match('#^\?[\w\-=&%.+/ ]*$#', $ret) ? $ret : '';
$voltar = BASE_URL . '/private/views/equipamentos/lista.php' . $ret;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo '<script>window.location.href = "' . $voltar . '";</script>';
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
    $nome_stmt = $pdo->prepare("SELECT codigo_inventario, designacao FROM equipamentos WHERE id = ?");
    $nome_stmt->execute([$del_id]);
    $nome_row = $nome_stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("DELETE FROM equipamentos WHERE id = ?");
    $stmt->execute([$del_id]);

    if ($nome_row) {
        mhs_historico('equipamento', $del_id, $nome_row['codigo_inventario'] . ' — ' . $nome_row['designacao'], 'apagar');
    }

    $_SESSION['success_message'] = 'Equipamento apagado com sucesso!';
    echo '<script>window.location.href = "' . $voltar . '";</script>';
    exit;

} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Erro ao apagar equipamento: ' . $e->getMessage();
    echo '<script>window.location.href = "' . $voltar . '";</script>';
    exit;
}
?>
