<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: lista.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome      = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    if (!$nome) {
        $_SESSION['error_message'] = 'O campo Nome é obrigatório.';
        header("Location: editar.php?id=$id"); exit;
    }
    try {
        mhs_pdo()->prepare("UPDATE categorias SET nome=?, descricao=?, atualizado_em=NOW() WHERE id=?")
            ->execute([$nome, $descricao ?: null, $id]);
        mhs_historico('categoria', $id, $nome, 'editar');
        $_SESSION['success_message'] = 'Categoria atualizada com sucesso.';
        header('Location: lista.php'); exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Erro ao guardar: ' . $e->getMessage();
        header("Location: editar.php?id=$id"); exit;
    }
}

$stmt = mhs_pdo()->prepare("SELECT * FROM categorias WHERE id=?");
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) { header('Location: lista.php'); exit; }

$page_title = 'Categorias - Editar';
include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-tags fa-fw"></i></span>
    <h1 class="mhs-page-title">Editar Categoria</h1>
  </div>
  <div class="mhs-page-actions">
    <a href="detalhes.php?id=<?= $row->id ?>" class="btn btn-outline-secondary"><i class="fa-solid fa-eye me-2"></i>Ver</a>
    <a href="lista.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
  </div>
</div>

<form method="POST" action="">
  <input type="hidden" name="id" value="<?= $row->id ?>">
  <div class="card mhs-data-card">
    <div class="mhs-tab-body">
      <div class="mhs-info-group">
        <div class="mhs-info-group-title"><i class="fa-solid fa-tags"></i> Informação da categoria</div>
        <div class="row g-3 mt-1">
          <div class="col-12">
            <label class="form-label fw-semibold">Nome <span class="text-danger">*</span></label>
            <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($row->nome) ?>" required maxlength="100" />
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold">Descrição</label>
            <textarea name="descricao" class="form-control" rows="4" maxlength="500"><?= htmlspecialchars($row->descricao ?? '') ?></textarea>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="d-flex gap-2 my-4">
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Guardar Categoria</button>
    <a href="lista.php" class="btn btn-secondary">Cancelar</a>
  </div>
</form>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
