<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo '<script>window.location.href = "' . BASE_URL . '/private/views/categorias/lista.php";</script>';
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
    $nome_row = $pdo->prepare("SELECT nome FROM categorias WHERE id = ?");
    $nome_row->execute([$del_id]);
    $cat = $nome_row->fetch(PDO::FETCH_ASSOC);

    // Fazer DELETE
    $stmt = $pdo->prepare("DELETE FROM categorias WHERE id = ?");
    $stmt->execute([$del_id]);

    if ($cat) { mhs_historico('categoria', $del_id, $cat['nome'], 'apagar'); }

    $_SESSION['success_message'] = 'Categoria apagada com sucesso!';
    echo '<script>window.location.href = "' . BASE_URL . '/private/views/categorias/lista.php";</script>';
    exit;

} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Erro ao apagar categoria: ' . $e->getMessage();
    echo '<script>window.location.href = "' . BASE_URL . '/private/views/categorias/lista.php";</script>';
    exit;
}
?>
