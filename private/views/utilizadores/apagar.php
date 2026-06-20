<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();
require_admin(); // apenas admin pode apagar

try {
    $pdo = new PDO(
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8mb4",
        MYSQL_USERNAME,
        MYSQL_PASSWORD,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $id = $_GET['id'] ?? 0;
    $stmt = $pdo->prepare("SELECT id, AES_DECRYPT(nome, :chave) as name, perfil AS profile FROM utilizadores WHERE id = ? LIMIT 1");
    $stmt->execute([':chave' => MYSQL_AES_KEY, $id]);
    $utilizador = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$utilizador) {
        $_SESSION['error_message'] = 'Utilizador não encontrado!';
        echo '<script>window.location.href = "lista.php";</script>';
        exit;
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = 'Erro ao carregar utilizador: ' . $e->getMessage();
    echo '<script>window.location.href = "lista.php";</script>';
    exit;
}

$page_title = 'Utilizadores - Apagar';
include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header mhs-page-header--dashboard">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-trash fa-fw"></i></span>
    <h1 class="mhs-page-title">Utilizadores - Apagar</h1>
  </div>
  <div class="mhs-page-actions">
    
  </div>
</div><div class="card mhs-data-card">
  <div class="card-body">
    <div class="alert alert-danger mb-4">
      <strong>Confirmação de remoção</strong><br />
      Tem a certeza que pretende apagar este utilizador. Esta ação não pode ser revertida.
    </div>
    <div class="row g-3 mb-4">
      <div class="col-md-6">
        <label class="form-label fw-semibold">Registo selecionado</label>
        <input class="form-control" value="utilizador selecionado" readonly />
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">Estado após confirmação</label>
        <input class="form-control" value="Removido da lista" readonly />
      </div>
    </div>
    <div class="d-flex gap-2 mt-3">
      <form method="POST" action="confirmar_apagar.php" style="flex: 0 0 auto;">
        <input type="hidden" name="id_enc" value="<?php echo htmlspecialchars($utilizador['id']); ?>">
        <button type="submit" class="btn btn-danger"><i class="fa-solid fa-trash me-1"></i>Confirmar Apagar</button>
      </form>
      <a href="lista.php" class="btn btn-secondary">Cancelar</a>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
