<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();
$page_title = 'Localizações - Detalhes';
include __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3"><h2 class="fw-bold mb-0"><i class="fa-solid fa-location-dot me-2"></i>Urgência / Sala 204</h2><div class="d-flex gap-2"><a href="editar.php" class="btn btn-outline-primary"><i class="fa-solid fa-pen me-1"></i> Editar</a><a href="lista.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Voltar</a></div></div><hr>
<div class="row g-4"><div class="col-md-5"><div class="card border-0 shadow-sm h-100"><div class="card-header fw-bold bg-primary text-white"><i class="fa-solid fa-location-dot me-1"></i>Informação geral</div><div class="card-body"><dl class="row mb-0"><dt class="col-5">Edifício</dt><dd class="col-7">Bloco Central</dd><dt class="col-5">Piso</dt><dd class="col-7">Piso 2</dd><dt class="col-5">Serviço</dt><dd class="col-7">Urgência</dd><dt class="col-5">Sala</dt><dd class="col-7">Sala 204</dd></dl><hr><p class="mb-0 text-muted small">Zona com prioridade para equipamentos críticos.</p></div></div></div><div class="col-md-7"><div class="card border-0 shadow-sm h-100"><div class="card-header fw-bold bg-primary text-white"><i class="fa-solid fa-stethoscope me-1"></i>Equipamentos nesta localização</div><div class="card-body"><table class="table table-sm table-hover mb-0"><thead class="table-light"><tr><th>Código</th><th>Designação</th><th>Estado</th><th>Criticidade</th></tr></thead><tbody><tr><td>EQ-001</td><td>Monitor multiparamétrico</td><td>Ativo</td><td>Média</td></tr><tr><td>EQ-002</td><td>Bomba de infusão</td><td>Ativo</td><td>Alta</td></tr></tbody></table></div></div></div></div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
