<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo '<script>window.location.href = "' . BASE_URL . '/private/views/fornecedores/lista.php";</script>';
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
    $f_stmt = $pdo->prepare("SELECT nome FROM fornecedores WHERE id = ?");
    $f_stmt->execute([$del_id]);
    $forn = $f_stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("DELETE FROM fornecedores WHERE id = ?");
    $stmt->execute([$del_id]);

    if ($forn) { mhs_historico('fornecedor', $del_id, $forn['nome'], 'apagar'); }

    $_SESSION['success_message'] = 'Fornecedor apagado com sucesso!';
    echo '<script>window.location.href = "' . BASE_URL . '/private/views/fornecedores/lista.php";</script>';
    exit;

} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Erro ao apagar fornecedor: ' . $e->getMessage();
    echo '<script>window.location.href = "' . BASE_URL . '/private/views/fornecedores/lista.php";</script>';
    exit;
}
?>
