<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: lista.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $edificio    = trim($_POST['edificio'] ?? '');
    $piso        = trim($_POST['piso'] ?? '');
    $servico     = trim($_POST['servico'] ?? '');
    $sala        = trim($_POST['sala'] ?? '');
    $observacoes = trim($_POST['observacoes'] ?? '');
    if (!$servico) {
        $_SESSION['error_message'] = 'O campo Serviço é obrigatório.';
        header("Location: editar.php?id=$id"); exit;
    }
    try {
        mhs_pdo()->prepare("UPDATE localizacoes SET edificio=?, piso=?, servico=?, sala=?, observacoes=?, atualizado_em=NOW() WHERE id=?")
            ->execute([$edificio ?: null, $piso ?: null, $servico, $sala ?: null, $observacoes ?: null, $id]);
        mhs_historico('localizacao', $id, $servico . ($sala ? ' · ' . $sala : ''), 'editar');
        $_SESSION['success_message'] = 'Localização atualizada com sucesso.';
        header('Location: lista.php'); exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Erro ao guardar: ' . $e->getMessage();
        header("Location: editar.php?id=$id"); exit;
    }
}

$stmt = mhs_pdo()->prepare("SELECT * FROM localizacoes WHERE id=?");
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) { header('Location: lista.php'); exit; }

$page_title = 'Localizações - Editar';
include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-location-dot fa-fw"></i></span>
    <h1 class="mhs-page-title">Editar Localização</h1>
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
        <div class="mhs-info-group-title"><i class="fa-solid fa-location-dot"></i> Informação da localização</div>
        <div class="row g-3 mt-1">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Edifício</label>
            <input type="text" name="edificio" class="form-control" value="<?= htmlspecialchars($row->edificio ?? '') ?>" maxlength="100" />
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Piso</label>
            <input type="text" name="piso" class="form-control" value="<?= htmlspecialchars($row->piso ?? '') ?>" maxlength="50" />
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Serviço <span class="text-danger">*</span></label>
            <input type="text" name="servico" class="form-control" value="<?= htmlspecialchars($row->servico) ?>" required maxlength="100" />
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Sala</label>
            <input type="text" name="sala" class="form-control" value="<?= htmlspecialchars($row->sala ?? '') ?>" maxlength="100" />
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold">Observações</label>
            <textarea name="observacoes" class="form-control" rows="3"><?= htmlspecialchars($row->observacoes ?? '') ?></textarea>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="d-flex gap-2 my-4">
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Guardar Localização</button>
    <a href="lista.php" class="btn btn-secondary">Cancelar</a>
  </div>
</form>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
