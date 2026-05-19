<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();
$page_title = 'Utilizadores - Detalhes';
include __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3"><h2 class="fw-bold mb-0"><i class="fa-solid fa-user me-2"></i>Técnico Hospitalar</h2><div class="d-flex gap-2"><a href="editar.php" class="btn btn-outline-primary"><i class="fa-solid fa-pen me-1"></i> Editar</a><a href="lista.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Voltar</a></div></div><hr>
<div class="row g-4"><div class="col-md-6"><div class="card border-0 shadow-sm h-100"><div class="card-header fw-bold bg-primary text-white"><i class="fa-solid fa-user me-1"></i>Conta</div><div class="card-body"><dl class="row mb-0"><dt class="col-5">Email</dt><dd class="col-7">tecnico@hospital.pt</dd><dt class="col-5">Perfil</dt><dd class="col-7">Técnico</dd><dt class="col-5">Estado</dt><dd class="col-7"><span class="badge bg-success">Ativo</span></dd><dt class="col-5">Último acesso</dt><dd class="col-7">12/04/2026 10:35</dd></dl></div></div></div><div class="col-md-6"><div class="card border-0 shadow-sm h-100"><div class="card-header fw-bold bg-primary text-white"><i class="fa-solid fa-shield-halved me-1"></i>Permissões</div><div class="card-body"><p class="mb-3">Acesso operacional ao inventário, documentos e pesquisa.</p><ul class="mb-0"><li>Consultar equipamentos</li><li>Registar documentos</li><li>Atualizar localizações e contratos</li></ul></div></div></div></div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
