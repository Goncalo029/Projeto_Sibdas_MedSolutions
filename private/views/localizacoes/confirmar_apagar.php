<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo '<script>window.location.href = "' . BASE_URL . '/private/views/localizacoes/lista.php";</script>';
    exit;
}

try {
    $pdo = new PDO(
        "mysql:host=" . MYSQL_HOST . ";dbname=" . MYSQL_DATABASE . ";charset=utf8mb4",
        MYSQL_USERNAME,
        MYSQL_PASSWORD,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $stmt = $pdo->prepare("DELETE FROM localizacoes WHERE id = ?");
    $stmt->execute([$_POST['id_enc'] ?? 0]);

    $_SESSION['success_message'] = 'Localização apagada com sucesso!';
    echo '<script>window.location.href = "' . BASE_URL . '/private/views/localizacoes/lista.php";</script>';
    exit;

} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Erro ao apagar localização: ' . $e->getMessage();
    echo '<script>window.location.href = "' . BASE_URL . '/private/views/localizacoes/lista.php";</script>';
    exit;
}
?>
