<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();
$page_title = 'Fornecedores - Detalhes';
include __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
  <h2 class="fw-bold mb-0"><i class="fa-solid fa-truck me-2"></i>MedTech Portugal</h2>
  <div class="d-flex gap-2">
    <a href="editar.php" class="btn btn-outline-primary"><i class="fa-solid fa-pen me-1"></i> Editar</a>
    <a href="lista.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Voltar</a>
  </div>
</div>
<hr>

<div class="row g-4 mb-4">
  <div class="col-md-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header fw-bold bg-primary text-white"><i class="fa-solid fa-address-card me-1"></i>Informação geral</div>
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-5">Nome</dt><dd class="col-7">MedTech Portugal</dd>
          <dt class="col-5">NIF</dt><dd class="col-7">509123456</dd>
          <dt class="col-5">Tipo</dt><dd class="col-7">Fabricante</dd>
          <dt class="col-5">Telefone</dt><dd class="col-7">210 000 000</dd>
          <dt class="col-5">Email</dt><dd class="col-7">suporte@medtech.pt</dd>
          <dt class="col-5">Website</dt><dd class="col-7">https://www.medtech.pt</dd>
        </dl>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header fw-bold bg-primary text-white"><i class="fa-solid fa-user me-1"></i>Pessoa de contacto</div>
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-5">Nome</dt><dd class="col-7">Ana Ribeiro</dd>
          <dt class="col-5">Telemóvel</dt><dd class="col-7">910 000 000</dd>
          <dt class="col-5">Morada</dt><dd class="col-7">Rua da Saúde, Lisboa</dd>
        </dl>
        <hr>
        <p class="mb-0 text-muted small">Fornecedor principal de equipamentos e consumíveis.</p>
      </div>
    </div>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-header fw-bold bg-primary text-white"><i class="fa-solid fa-stethoscope me-1"></i>Equipamentos associados</div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-sm table-hover mb-0">
        <thead class="table-light"><tr><th>Código</th><th>Designação</th><th>Marca</th><th>Modelo</th><th>Tipo relação</th></tr></thead>
        <tbody>
          <tr><td>EQ-001</td><td>Monitor multiparamétrico</td><td>Philips</td><td>IntelliVue MX450</td><td>Fabricante</td></tr>
          <tr><td>EQ-003</td><td>Ventilador pulmonar</td><td>Hamilton</td><td>C6</td><td>Assistência técnica</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
