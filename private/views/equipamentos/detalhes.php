<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();
$page_title = 'Equipamentos - Detalhes';
include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header">
  <div>
    <span class="mhs-page-kicker">Ficha técnica</span>
    <h1 class="mhs-page-title">Monitor multiparamétrico</h1>
  </div>
  <div class="mhs-page-actions">
    <a href="#" class="btn btn-outline-dark"><i class="fa-solid fa-print me-2"></i>Etiqueta / QR</a>
    <a href="editar.php" class="btn btn-outline-primary"><i class="fa-solid fa-pen me-2"></i>Editar</a>
    <a href="lista.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
  </div>
</div>

<div class="row g-4 mb-4">
  <div class="col-md-8">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header fw-bold bg-primary text-white"><i class="fa-solid fa-barcode me-1"></i>Dados gerais</div>
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-sm-4">Código inventário</dt><dd class="col-sm-8">EQ-001</dd>
          <dt class="col-sm-4">Designação</dt><dd class="col-sm-8">Monitor multiparamétrico</dd>
          <dt class="col-sm-4">Categoria</dt><dd class="col-sm-8">Monitorização</dd>
          <dt class="col-sm-4">Marca / Modelo</dt><dd class="col-sm-8">Philips / IntelliVue MX450</dd>
          <dt class="col-sm-4">Número de série</dt><dd class="col-sm-8">SN-450-2026</dd>
          <dt class="col-sm-4">Fabricante</dt><dd class="col-sm-8">Philips Healthcare</dd>
        </dl>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header fw-bold bg-primary text-white"><i class="fa-solid fa-heart-pulse me-1"></i>Estado operacional</div>
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-5">Estado</dt><dd class="col-7"><span class="badge bg-success">Ativo</span></dd>
          <dt class="col-5">Criticidade</dt><dd class="col-7">Média</dd>
          <dt class="col-5">Localização</dt><dd class="col-7">Urgência / Sala 204</dd>
          <dt class="col-5">Entrada</dt><dd class="col-7">Compra</dd>
        </dl>
      </div>
    </div>
  </div>
</div>

<div class="row g-4 mb-4">
  <div class="col-md-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header fw-bold bg-primary text-white"><i class="fa-solid fa-receipt me-1"></i>Aquisição</div>
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-5">Data aquisição</dt><dd class="col-7">15/01/2026</dd>
          <dt class="col-5">Ano fabrico</dt><dd class="col-7">2025</dd>
          <dt class="col-5">Custo</dt><dd class="col-7">12 500,00 €</dd>
          <dt class="col-5">Tipo entrada</dt><dd class="col-7">Compra</dd>
        </dl>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header fw-bold bg-primary text-white"><i class="fa-solid fa-shield-halved me-1"></i>Garantia / contrato</div>
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-5">Data início</dt><dd class="col-7">10/01/2026</dd>
          <dt class="col-5">Data fim</dt><dd class="col-7">10/01/2028</dd>
          <dt class="col-5">Contrato</dt><dd class="col-7">Sim</dd>
          <dt class="col-5">Entidade</dt><dd class="col-7">MedTech Portugal</dd>
        </dl>
      </div>
    </div>
  </div>
</div>

<div class="card border-0 shadow-sm mb-4">
  <div class="card-header fw-bold bg-primary text-white"><i class="fa-solid fa-truck me-1"></i>Fornecedores associados</div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-sm table-hover mb-0">
        <thead class="table-light"><tr><th>Fornecedor</th><th>Tipo relação</th><th>Telefone</th></tr></thead>
        <tbody>
          <tr><td>MedTech Portugal</td><td>Fabricante</td><td>210 000 000</td></tr>
          <tr><td>Clinicare Equipamentos</td><td>Assistência técnica</td><td>211 000 000</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="card border-0 shadow-sm mb-4">
  <div class="card-header fw-bold bg-primary text-white"><i class="fa-solid fa-comment me-1"></i>Observações</div>
  <div class="card-body">
    <p class="mb-0">Equipamento operacional e associado ao serviço de urgência.</p>
  </div>
</div>

<div class="row g-4 mb-4">
  <div class="col-xl-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Histórico de movimentações</span>
        <span class="mhs-card-meta">6 registo(s)</span>
      </div>
      <div class="card-body">
        <div class="mhs-history-list">
          <article class="mhs-history-item">
            <div class="mhs-history-dot"></div>
            <div class="mhs-history-copy">
              <div class="mhs-history-head"><strong>Manutenção preventiva</strong><small>24/03/2026 19:38</small></div>
              <p>Última 25/03/2026 | próxima 27/03/2026 | periodicidade semanal | estado em curso <i class="fa-solid fa-arrow-right-long mx-2"></i> concluída</p>
              <small>Alterado por tecnico@hospital.pt</small>
            </div>
          </article>
          <article class="mhs-history-item">
            <div class="mhs-history-dot"></div>
            <div class="mhs-history-copy">
              <div class="mhs-history-head"><strong>Empréstimo</strong><small>24/03/2026 19:37</small></div>
              <p>Emprestado para Laboratório de Análises <i class="fa-solid fa-arrow-right-long mx-2"></i> devolvido a Fisioterapia em 24/03/2026</p>
              <small>Alterado por tecnico@hospital.pt</small>
            </div>
          </article>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-list-check me-2 text-primary"></i>Histórico de empréstimos</span>
        <span class="mhs-card-meta">1 registo(s)</span>
      </div>
      <div class="card-body">
        <div class="mhs-loan-history-list">
          <article class="mhs-loan-history-item">
            <div>
              <strong>Fisioterapia - Edifício B / Piso 0 / Sala de Tratamentos</strong>
              <p><i class="fa-solid fa-arrow-right-long me-2 text-muted"></i>Laboratório de Análises - Edifício C / Piso -1 / Lab. Hematologia</p>
              <div class="mhs-loan-history-meta">
                <span>Saída: 24/03/2026</span>
                <span>Prevista: 26/03/2026</span>
                <span>Devolução: 24/03/2026</span>
              </div>
            </div>
            <span class="badge bg-success-subtle text-success px-3 py-2">Concluído</span>
          </article>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
