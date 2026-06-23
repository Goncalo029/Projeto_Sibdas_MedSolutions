<?php
// apaga um utilizador (so admin)
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();
require_admin(); // apenas admin pode apagar

// so aceita pedidos por POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo '<script>window.location.href = "' . BASE_URL . '/private/views/utilizadores/lista.php";</script>';
    exit;
}

try {
    // liga a base de dados
    $pdo = new PDO(
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8mb4",
        MYSQL_USERNAME,
        MYSQL_PASSWORD,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // vai buscar o email (desencriptado) antes de apagar, para o historico
    $del_id = (int)($_POST['id_enc'] ?? 0);
    $u_stmt = $pdo->prepare("SELECT AES_DECRYPT(nome, ?) AS email, perfil AS profile FROM utilizadores WHERE id = ?");
    $u_stmt->execute([MYSQL_AES_KEY, $del_id]);
    $user = $u_stmt->fetch(PDO::FETCH_ASSOC);

    // apaga mesmo o utilizador
    $stmt = $pdo->prepare("DELETE FROM utilizadores WHERE id = ?");
    $stmt->execute([$del_id]);

    if ($user) {
        mhs_historico('utilizador', $del_id, ($user['email'] ?: ('#' . $del_id)) . ' (' . $user['profile'] . ')', 'apagar');
    }

    $_SESSION['success_message'] = 'Utilizador apagado com sucesso!';
    echo '<script>window.location.href = "' . BASE_URL . '/private/views/utilizadores/lista.php";</script>';
    exit;

} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Erro ao apagar utilizador: ' . $e->getMessage();
    echo '<script>window.location.href = "' . BASE_URL . '/private/views/utilizadores/lista.php";</script>';
    exit;
}
?>
