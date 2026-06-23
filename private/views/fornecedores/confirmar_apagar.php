<?php
// carrega as configuracoes e funcoes e verifica a sessao
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();
require_admin(); // apenas admin pode apagar

// so aceita pedidos por POST (vem do formulario de confirmacao)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo '<script>window.location.href = "' . BASE_URL . '/private/views/fornecedores/lista.php";</script>';
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

    // vai buscar o nome do fornecedor antes de apagar (para o historico)
    $del_id = (int)($_POST['id_enc'] ?? 0);
    $f_stmt = $pdo->prepare("SELECT nome FROM fornecedores WHERE id = ?");
    $f_stmt->execute([$del_id]);
    $forn = $f_stmt->fetch(PDO::FETCH_ASSOC);

    // apaga mesmo o fornecedor
    $stmt = $pdo->prepare("DELETE FROM fornecedores WHERE id = ?");
    $stmt->execute([$del_id]);

    // guarda no historico que foi apagado
    if ($forn) { mhs_historico('fornecedor', $del_id, $forn['nome'], 'apagar'); }

    // mensagem de sucesso e volta para a lista
    $_SESSION['success_message'] = 'Fornecedor apagado com sucesso!';
    echo '<script>window.location.href = "' . BASE_URL . '/private/views/fornecedores/lista.php";</script>';
    exit;

} catch (PDOException $e) {
    // se algo correr mal mostra o erro e volta para a lista
    $_SESSION['error_message'] = 'Erro ao apagar fornecedor: ' . $e->getMessage();
    echo '<script>window.location.href = "' . BASE_URL . '/private/views/fornecedores/lista.php";</script>';
    exit;
}
?>
