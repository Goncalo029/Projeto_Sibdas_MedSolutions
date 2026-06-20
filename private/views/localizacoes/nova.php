<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $edificio    = trim($_POST['edificio'] ?? '');
    $piso        = trim($_POST['piso'] ?? '');
    $servico     = trim($_POST['servico'] ?? '');
    $sala        = trim($_POST['sala'] ?? '');
    $observacoes = trim($_POST['observacoes'] ?? '');
    if (!$servico) {
        $_SESSION['error_message'] = 'O campo Serviço é obrigatório.';
        header('Location: nova.php'); exit;
    }
    try {
        mhs_pdo()->prepare("INSERT INTO localizacoes (edificio, piso, servico, sala, observacoes, criado_em) VALUES (?,?,?,?,?,NOW())")
            ->execute([$edificio ?: null, $piso ?: null, $servico, $sala ?: null, $observacoes ?: null]);
        mhs_historico('localizacao', (int)mhs_pdo()->lastInsertId(), $servico . ($sala ? ' · ' . $sala : ''), 'criar');
        $_SESSION['success_message'] = 'Localização criada com sucesso.';
        header('Location: lista.php'); exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Erro ao guardar: ' . $e->getMessage();
        header('Location: nova.php'); exit;
    }
}

$page_title = 'Localizações - Nova';
include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-location-dot fa-fw"></i></span>
    <h1 class="mhs-page-title">Nova Localização</h1>
  </div>
  <div class="mhs-page-actions">
    <a href="lista.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
  </div>
</div>

<form method="POST" action="">
  <div class="card mhs-data-card">
    <div class="mhs-tab-body">
      <div class="mhs-info-group">
        <div class="mhs-info-group-title d-flex align-items-center justify-content-between">
          <span><i class="fa-solid fa-location-dot"></i> Informação da localização</span>
          <button type="button" class="btn btn-sm btn-outline-secondary" onclick="mhsAutoFillLocalizacao()"><i class="fa-solid fa-wand-magic-sparkles me-1"></i>Auto-preencher (demo)</button>
        </div>
        <div class="row g-3 mt-1">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Edifício</label>
            <input type="text" name="edificio" class="form-control" placeholder="Ex.: Bloco Central" maxlength="100" />
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Piso</label>
            <input type="text" name="piso" class="form-control" placeholder="Ex.: Piso 2" maxlength="50" />
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Serviço <span class="text-danger">*</span></label>
            <input type="text" name="servico" class="form-control" placeholder="Ex.: Urgência" required maxlength="100" />
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Sala</label>
            <input type="text" name="sala" class="form-control" placeholder="Ex.: Sala 204" maxlength="100" />
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold">Observações</label>
            <textarea name="observacoes" class="form-control" rows="3" placeholder="Notas sobre a localização"></textarea>
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
