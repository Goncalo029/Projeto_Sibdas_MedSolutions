<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();
$page_title = 'Equipamentos - Detalhes';
include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-stethoscope fa-fw"></i></span>
    <h1 class="mhs-page-title">Monitor multiparamétrico</h1>
  </div>
  <div class="mhs-page-actions">
    <a href="editar.php" class="btn btn-outline-primary"><i class="fa-solid fa-pen me-2"></i>Editar</a>
    <a href="lista.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
  </div>
</div>

<!-- Resumo rápido -->
<div class="mhs-detail-summary card mhs-data-card mb-4">
  <div class="mhs-detail-summary-inner">
    <div class="mhs-detail-summary-item">
      <span class="mhs-detail-summary-label">Código</span>
      <span class="mhs-code">EQ-001</span>
    </div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item">
      <span class="mhs-detail-summary-label">Categoria</span>
      <span class="mhs-detail-summary-val">Monitorização</span>
    </div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item">
      <span class="mhs-detail-summary-label">Estado</span>
      <span class="mhs-detail-summary-val mhs-detail-summary-val--ok">Ativo</span>
    </div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item">
      <span class="mhs-detail-summary-label">Criticidade</span>
      <span class="mhs-detail-summary-val">Média</span>
    </div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item">
      <span class="mhs-detail-summary-label">Localização</span>
      <span class="mhs-detail-summary-val">Urgência / Sala 204</span>
    </div>
  </div>
</div>

<!-- Tabs -->
<div class="card mhs-data-card">
  <div class="mhs-detail-tabs">
    <button class="mhs-detail-tab active" data-tab="ficha">
      <i class="fa-solid fa-barcode"></i> Ficha Técnica
    </button>
    <button class="mhs-detail-tab" data-tab="aquisicao">
      <i class="fa-solid fa-receipt"></i> Aquisição
    </button>
    <button class="mhs-detail-tab" data-tab="garantia">
      <i class="fa-solid fa-shield-halved"></i> Garantia / Contrato
    </button>
    <button class="mhs-detail-tab" data-tab="fornecedores">
      <i class="fa-solid fa-truck"></i> Fornecedores
    </button>
    <button class="mhs-detail-tab" data-tab="documentos">
      <i class="fa-solid fa-file-lines"></i> Documentos
    </button>
    <button class="mhs-detail-tab" data-tab="manutencoes">
      <i class="fa-solid fa-wrench"></i> Manutenções
    </button>
    <button class="mhs-detail-tab" data-tab="emprestimos">
      <i class="fa-solid fa-boxes-packing"></i> Empréstimos
    </button>
    <button class="mhs-detail-tab" data-tab="movimentacoes">
      <i class="fa-solid fa-clock-rotate-left"></i> Movimentações
    </button>
  </div>

  <!-- Ficha Técnica -->
  <div class="mhs-tab-pane active" id="tab-ficha">
    <div class="mhs-tab-body">
      <div class="row g-4">
        <div class="col-md-6">
          <div class="mhs-info-group">
            <div class="mhs-info-group-title"><i class="fa-solid fa-barcode"></i> Identificação</div>
            <dl class="mhs-info-dl">
              <dt>Código de inventário</dt><dd>EQ-001</dd>
              <dt>Designação</dt><dd>Monitor multiparamétrico</dd>
              <dt>Marca / Modelo</dt><dd>Philips / IntelliVue MX450</dd>
              <dt>Número de série</dt><dd>SN-450-2026</dd>
              <dt>Fabricante</dt><dd>Philips Healthcare</dd>
            </dl>
          </div>
        </div>
        <div class="col-md-6">
          <div class="mhs-info-group">
            <div class="mhs-info-group-title"><i class="fa-solid fa-heart-pulse"></i> Estado operacional</div>
            <dl class="mhs-info-dl">
              <dt>Estado</dt><dd>Ativo</dd>
              <dt>Criticidade</dt><dd>Média</dd>
              <dt>Localização</dt><dd>Urgência / Sala 204</dd>
              <dt>Tipo de entrada</dt><dd>Compra</dd>
            </dl>
          </div>
          <div class="mhs-info-group mt-3">
            <div class="mhs-info-group-title"><i class="fa-solid fa-comment"></i> Observações</div>
            <p class="mhs-info-obs">Equipamento operacional e associado ao serviço de urgência.</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Aquisição -->
  <div class="mhs-tab-pane" id="tab-aquisicao">
    <div class="mhs-tab-body">
      <div class="row g-4">
        <div class="col-md-5">
          <div class="mhs-info-group">
            <div class="mhs-info-group-title"><i class="fa-solid fa-receipt"></i> Dados de aquisição</div>
            <dl class="mhs-info-dl">
              <dt>Data de aquisição</dt><dd>15/01/2026</dd>
              <dt>Ano de fabrico</dt><dd>2025</dd>
              <dt>Custo</dt><dd>12 500,00 €</dd>
              <dt>Tipo de entrada</dt><dd>Compra</dd>
            </dl>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Garantia / Contrato -->
  <div class="mhs-tab-pane" id="tab-garantia">
    <div class="mhs-tab-body">
      <div class="row g-4">
        <div class="col-md-5">
          <div class="mhs-info-group">
            <div class="mhs-info-group-title"><i class="fa-solid fa-shield-halved"></i> Garantia e contrato</div>
            <dl class="mhs-info-dl">
              <dt>Data de início</dt><dd>10/01/2026</dd>
              <dt>Data de fim</dt><dd>10/01/2028</dd>
              <dt>Tem contrato</dt><dd>Sim</dd>
              <dt>Entidade responsável</dt><dd>MedTech Portugal</dd>
            </dl>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Fornecedores -->
  <div class="mhs-tab-pane" id="tab-fornecedores">
    <div class="mhs-tab-body">
      <table class="table mhs-datatable mb-0">
        <thead>
          <tr><th>Fornecedor</th><th>Tipo de relação</th><th>Telefone</th><th>Email</th></tr>
        </thead>
        <tbody>
          <tr>
            <td class="mhs-td-primary">MedTech Portugal</td>
            <td>Fabricante</td>
            <td>210 000 000</td>
            <td>geral@medtech.pt</td>
          </tr>
          <tr>
            <td class="mhs-td-primary">Clinicare Equipamentos</td>
            <td>Assistência técnica</td>
            <td>211 000 000</td>
            <td>suporte@clinicare.pt</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Documentos -->
  <div class="mhs-tab-pane" id="tab-documentos">
    <div class="mhs-tab-body">
      <div class="mhs-empty-state">
        <i class="fa-solid fa-file-circle-xmark"></i>
        <p>Sem documentos associados a este equipamento.</p>
        <a href="#" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus me-1"></i>Adicionar documento</a>
      </div>
    </div>
  </div>

  <!-- Manutenções -->
  <div class="mhs-tab-pane" id="tab-manutencoes">
    <div class="mhs-tab-body">
      <div class="mhs-empty-state">
        <i class="fa-solid fa-wrench"></i>
        <p>Sem manutenções registadas para este equipamento.</p>
        <a href="#" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus me-1"></i>Registar manutenção</a>
      </div>
    </div>
  </div>

  <!-- Empréstimos -->
  <div class="mhs-tab-pane" id="tab-emprestimos">
    <div class="mhs-tab-body">
      <div class="mhs-history-list">
        <article class="mhs-history-item">
          <div class="mhs-history-dot"></div>
          <div class="mhs-history-copy">
            <div class="mhs-history-head">
              <strong>Empréstimo — Laboratório de Análises</strong>
              <small>24/03/2026</small>
            </div>
            <p>Fisioterapia <i class="fa-solid fa-arrow-right-long mx-2"></i> Laboratório de Análises — Devolvido em 24/03/2026</p>
            <small>Por tecnico@hospital.pt</small>
          </div>
        </article>
      </div>
    </div>
  </div>

  <!-- Movimentações -->
  <div class="mhs-tab-pane" id="tab-movimentacoes">
    <div class="mhs-tab-body">
      <div class="mhs-history-list">
        <article class="mhs-history-item">
          <div class="mhs-history-dot"></div>
          <div class="mhs-history-copy">
            <div class="mhs-history-head">
              <strong>Manutenção preventiva</strong>
              <small>24/03/2026 19:38</small>
            </div>
            <p>Estado <i class="fa-solid fa-arrow-right-long mx-2"></i> Em curso → Concluída | Próxima: 27/03/2026</p>
            <small>Por tecnico@hospital.pt</small>
          </div>
        </article>
        <article class="mhs-history-item">
          <div class="mhs-history-dot"></div>
          <div class="mhs-history-copy">
            <div class="mhs-history-head">
              <strong>Empréstimo concluído</strong>
              <small>24/03/2026 19:37</small>
            </div>
            <p>Devolvido a Fisioterapia <i class="fa-solid fa-arrow-right-long mx-2"></i> proveniente de Laboratório de Análises</p>
            <small>Por tecnico@hospital.pt</small>
          </div>
        </article>
      </div>
    </div>
  </div>

</div><!-- card -->

<script>
document.querySelectorAll('.mhs-detail-tab').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.mhs-detail-tab').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.mhs-tab-pane').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
    });
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
