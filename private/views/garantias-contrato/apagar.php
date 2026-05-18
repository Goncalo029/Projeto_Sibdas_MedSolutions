<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();
$page_title = 'Garantias-Contrato - Apagar';
include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header mhs-page-header--dashboard">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-trash fa-fw"></i></span>
    <h1 class="mhs-page-title">Garantias-Contrato - Apagar</h1>
  </div>
  <div class="mhs-page-actions">
    
  </div>
</div><div class="card mhs-data-card">
  <div class="card-body">
    <div class="alert alert-danger mb-4">
      <strong>Confirmação de remoção</strong><br />
      Tem a certeza que pretende apagar este garantia/contrato. Esta ação não pode ser revertida.
    </div>
    <div class="row g-3 mb-4">
      <div class="col-md-6">
        <label class="form-label fw-semibold">Registo selecionado</label>
        <input class="form-control" value="garantia/contrato selecionado" readonly />
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">Estado após confirmação</label>
        <input class="form-control" value="Removido da lista" readonly />
      </div>
    </div>
    <div class="d-flex gap-2 mt-3">
      <a href="lista.php" class="btn btn-danger" onclick="alert('Registo apagado com sucesso!')"><i class="fa-solid fa-trash me-1"></i>Confirmar Apagar</a>
      <a href="lista.php" class="btn btn-secondary">Cancelar</a>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
