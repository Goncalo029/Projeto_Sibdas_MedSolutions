<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();
$page_title = 'Garantias-Contrato - Detalhes';
include __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3"><h2 class="fw-bold mb-0"><i class="fa-solid fa-shield-halved me-2"></i>Garantia / Contrato - EQ-001</h2><div class="d-flex gap-2"><a href="editar.php" class="btn btn-outline-primary"><i class="fa-solid fa-pen me-1"></i> Editar</a><a href="lista.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Voltar</a></div></div><hr>
<div class="row g-4"><div class="col-md-7"><div class="card border-0 shadow-sm h-100"><div class="card-header fw-bold bg-primary text-white"><i class="fa-solid fa-shield-halved me-1"></i>Informação do contrato</div><div class="card-body"><dl class="row mb-0"><dt class="col-md-4">Equipamento</dt><dd class="col-md-8">EQ-001 - Monitor multiparamétrico</dd><dt class="col-md-4">Data início</dt><dd class="col-md-8">10/01/2026</dd><dt class="col-md-4">Data fim</dt><dd class="col-md-8">10/01/2028</dd><dt class="col-md-4">Tem contrato</dt><dd class="col-md-8">Sim</dd><dt class="col-md-4">Tipo</dt><dd class="col-md-8">Manutenção preventiva</dd><dt class="col-md-4">Entidade</dt><dd class="col-md-8">MedTech Portugal</dd><dt class="col-md-4">Periodicidade</dt><dd class="col-md-8">Trimestral</dd></dl></div></div></div><div class="col-md-5"><div class="card border-0 shadow-sm h-100"><div class="card-header fw-bold bg-primary text-white"><i class="fa-solid fa-comment me-1"></i>Observações</div><div class="card-body"><p class="mb-0">Contrato inclui visitas preventivas e suporte técnico prioritario.</p></div></div></div></div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
