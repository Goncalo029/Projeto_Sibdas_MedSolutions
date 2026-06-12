<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();

try {
    $pdo = new PDO(
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8mb4",
        MYSQL_USERNAME,
        MYSQL_PASSWORD,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $id = $_GET['id'] ?? 0;
    $stmt = $pdo->prepare("SELECT * FROM equipamentos WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $equipamento = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$equipamento) {
        $_SESSION['error_message'] = 'Equipamento não encontrado!';
        echo '<script>window.location.href = "lista.php";</script>';
        exit;
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = 'Erro ao carregar equipamento: ' . $e->getMessage();
    echo '<script>window.location.href = "lista.php";</script>';
    exit;
}

$page_title = 'Equipamentos - Apagar';
include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header mhs-page-header--dashboard">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-trash fa-fw"></i></span>
    <h1 class="mhs-page-title">Equipamentos - Apagar</h1>
  </div>
  <div class="mhs-page-actions">
    
  </div>
</div><div class="card mhs-data-card"><div class="card-body">
  <h5 class="text-danger mb-3">Confirmação de remoção</h5>
  <p>Tem a certeza que pretende apagar este registo?</p>
  <form method="POST" action="confirmar_apagar.php">
    <input type="hidden" name="id_enc" value="<?php echo htmlspecialchars($equipamento['id']); ?>">
    <div class="d-flex gap-2 mt-3">
      <button class="btn btn-danger" type="submit">Apagar</button>
      <a href="lista.php" class="btn btn-outline-secondary">Cancelar</a>
    </div>
  </form>
</div></div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
