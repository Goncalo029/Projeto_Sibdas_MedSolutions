<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();
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
  <div class="d-flex gap-2 mt-3">
    <button class="btn btn-danger" type="button">Apagar</button>
    <a href="lista.php" class="btn btn-outline-secondary">Cancelar</a>
  </div>
</div></div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
