<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();
$page_title = 'Pesquisa Avançada';
include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header mhs-page-header--dashboard">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-magnifying-glass fa-fw"></i></span>
    <h1 class="mhs-page-title">Pesquisa Avançada</h1>
  </div>
  <div class="mhs-page-actions">
    
  </div>
</div><div class="card mhs-data-card mb-4"><div class="card-body">
  <div class="row g-3">
    <div class="col-md-4"><label class="form-label">Código</label><input class="form-control" placeholder="Ex: EQ-001" /></div>
    <div class="col-md-4"><label class="form-label">Estado</label><select class="form-select"><option>Todos</option><option>Ativo</option><option>Em manutenção</option></select></div>
    <div class="col-md-4"><label class="form-label">Criticidade</label><select class="form-select"><option>Todas</option><option>Alta</option><option>Média</option><option>Baixa</option></select></div>
  </div>
  <div class="d-flex gap-2 mt-3">
    <button class="btn btn-primary" type="button">Pesquisar</button>
    <button class="btn btn-outline-secondary" type="button">Limpar</button>
  </div>
</div></div>
<div class="card mhs-data-card"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mhs-datatable mb-0"><thead class="mhs-thead"><tr><th>Código</th><th>Designação</th><th>Estado</th><th>Serviço</th><th>Ações</th></tr></thead><tbody></tbody></table></div></div></div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
