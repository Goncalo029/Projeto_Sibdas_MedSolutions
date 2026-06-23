<?php
// carrega as configuracoes e funcoes e verifica a sessao
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();
require_admin(); // apenas admin pode apagar

// so aceita pedidos por POST (vem do formulario de confirmacao)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo '<script>window.location.href = "' . BASE_URL . '/private/views/categorias/lista.php";</script>';
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

    // vai buscar o nome da categoria antes de apagar (para o historico)
    $del_id = (int)($_POST['id_enc'] ?? 0);
    $nome_row = $pdo->prepare("SELECT nome FROM categorias WHERE id = ?");
    $nome_row->execute([$del_id]);
    $cat = $nome_row->fetch(PDO::FETCH_ASSOC);

    // apaga mesmo a categoria
    $stmt = $pdo->prepare("DELETE FROM categorias WHERE id = ?");
    $stmt->execute([$del_id]);

    // guarda no historico que foi apagada
    if ($cat) { mhs_historico('categoria', $del_id, $cat['nome'], 'apagar'); }

    // mensagem de sucesso e volta para a lista
    $_SESSION['success_message'] = 'Categoria apagada com sucesso!';
    echo '<script>window.location.href = "' . BASE_URL . '/private/views/categorias/lista.php";</script>';
    exit;

} catch (PDOException $e) {
    // se algo correr mal mostra o erro e volta para a lista
    $_SESSION['error_message'] = 'Erro ao apagar categoria: ' . $e->getMessage();
    echo '<script>window.location.href = "' . BASE_URL . '/private/views/categorias/lista.php";</script>';
    exit;
}
?>
