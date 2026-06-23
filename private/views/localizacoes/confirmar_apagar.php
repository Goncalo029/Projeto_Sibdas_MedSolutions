<?php
// carrega as configuracoes e funcoes e verifica a sessao
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();
require_admin(); // apenas admin pode apagar

// so aceita pedidos por POST (vem do formulario de confirmacao)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo '<script>window.location.href = "' . BASE_URL . '/private/views/localizacoes/lista.php";</script>';
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

    // vai buscar o nome da localizacao antes de apagar (para o historico)
    $del_id = (int)($_POST['id_enc'] ?? 0);
    $loc_stmt = $pdo->prepare("SELECT servico, sala FROM localizacoes WHERE id = ?");
    $loc_stmt->execute([$del_id]);
    $loc = $loc_stmt->fetch(PDO::FETCH_ASSOC);

    // apaga mesmo a localizacao
    $stmt = $pdo->prepare("DELETE FROM localizacoes WHERE id = ?");
    $stmt->execute([$del_id]);

    // guarda no historico que foi apagada
    if ($loc) { mhs_historico('localizacao', $del_id, $loc['servico'] . ($loc['sala'] ? ' · ' . $loc['sala'] : ''), 'apagar'); }

    // mensagem de sucesso e volta para a lista
    $_SESSION['success_message'] = 'Localização apagada com sucesso!';
    echo '<script>window.location.href = "' . BASE_URL . '/private/views/localizacoes/lista.php";</script>';
    exit;

} catch (PDOException $e) {
    // se algo correr mal mostra o erro e volta para a lista
    $_SESSION['error_message'] = 'Erro ao apagar localização: ' . $e->getMessage();
    echo '<script>window.location.href = "' . BASE_URL . '/private/views/localizacoes/lista.php";</script>';
    exit;
}
?>
