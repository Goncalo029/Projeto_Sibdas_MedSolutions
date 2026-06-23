<?php
// pagina para editar uma localizacao que ja existe
require_once __DIR__ . '/../../includes/funcoes.php';

// so entra com sessao iniciada
redirect_if_not_logged();

// vai buscar o id ao link, se nao tiver volta para a lista
$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: lista.php'); exit; }

// quando o formulario e enviado, valida e grava
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // le os campos do formulario
    $edificio    = trim($_POST['edificio'] ?? '');
    $piso        = trim($_POST['piso'] ?? '');
    $servico     = trim($_POST['servico'] ?? '');
    $sala        = trim($_POST['sala'] ?? '');
    $observacoes = trim($_POST['observacoes'] ?? '');
    // o servico e obrigatorio
    if (!$servico) {
        $_SESSION['error_message'] = 'O campo Serviço é obrigatório.';
        header("Location: editar.php?id=$id"); exit;
    }
    try {
        // atualiza a localizacao na base de dados
        mhs_pdo()->prepare("UPDATE localizacoes SET edificio=?, piso=?, servico=?, sala=?, observacoes=?, atualizado_em=NOW() WHERE id=?")
            ->execute([$edificio ?: null, $piso ?: null, $servico, $sala ?: null, $observacoes ?: null, $id]);
        // guarda no historico
        mhs_historico('localizacao', $id, $servico . ($sala ? ' · ' . $sala : ''), 'editar');
        $_SESSION['success_message'] = 'Localização atualizada com sucesso.';
        header('Location: lista.php'); exit;
    } catch (PDOException $e) {
        // se a gravacao falhar mostra o erro
        $_SESSION['error_message'] = 'Erro ao guardar: ' . $e->getMessage();
        header("Location: editar.php?id=$id"); exit;
    }
}

// vai buscar a localizacao atual para preencher o formulario
$stmt = mhs_pdo()->prepare("SELECT * FROM localizacoes WHERE id=?");
$stmt->execute([$id]);
$row = $stmt->fetch();
// se nao existir volta para a lista
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
    <a href="lista.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
  </div>
</div>

<form method="POST" action="">
  <input type="hidden" name="id" value="<?= $row->id ?>">
  <div class="card mhs-data-card">
    <div class="mhs-tab-body">
      <div class="mhs-form-section">
        <div class="mhs-form-section-title"><i class="fa-solid fa-location-dot"></i> Informação da localização</div>
        <div class="row g-3">
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
