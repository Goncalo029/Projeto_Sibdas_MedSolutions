<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();
$page_title = 'Garantias-Contrato - Detalhes';
include __DIR__ . '/../../includes/header.php';
?>
<div class="mhs-page-header">
  <div><span class="mhs-page-kicker"><i class="fa-solid fa-shield-halved fa-fw"></i></span><h1 class="mhs-page-title">Garantia — EQ-001</h1></div>
  <div class="mhs-page-actions">
    <a href="editar.php" class="btn btn-outline-primary"><i class="fa-solid fa-pen me-2"></i>Editar</a>
    <a href="lista.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
  </div>
</div>
<div class="mhs-detail-summary card mhs-data-card mb-4">
  <div class="mhs-detail-summary-inner">
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Equipamento</span><span class="mhs-detail-summary-val">EQ-001</span></div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Início</span><span class="mhs-detail-summary-val">10/01/2026</span></div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Fim</span><span class="mhs-detail-summary-val">10/01/2028</span></div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Entidade</span><span class="mhs-detail-summary-val">MedTech Portugal</span></div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Contrato</span><span class="mhs-detail-summary-val mhs-detail-summary-val--ok">Sim</span></div>
  </div>
</div>
<div class="card mhs-data-card">
  <div class="mhs-detail-tabs">
    <button class="mhs-detail-tab active" data-tab="contrato"><i class="fa-solid fa-shield-halved"></i> Contrato</button>
    <button class="mhs-detail-tab" data-tab="obs"><i class="fa-solid fa-comment"></i> Observações</button>
  </div>
  <div class="mhs-tab-pane active" id="tab-contrato">
    <div class="mhs-tab-body"><div class="row g-4"><div class="col-md-6">
      <div class="mhs-info-group">
        <div class="mhs-info-group-title"><i class="fa-solid fa-shield-halved"></i> Dados do contrato</div>
        <dl class="mhs-info-dl">
          <dt>Equipamento</dt><dd>EQ-001 — Monitor multiparamétrico</dd>
          <dt>Data de início</dt><dd>10/01/2026</dd>
          <dt>Data de fim</dt><dd>10/01/2028</dd>
          <dt>Tem contrato</dt><dd>Sim</dd>
          <dt>Tipo</dt><dd>Manutenção preventiva</dd>
          <dt>Entidade responsável</dt><dd>MedTech Portugal</dd>
          <dt>Periodicidade</dt><dd>Trimestral</dd>
        </dl>
      </div>
    </div></div></div>
  </div>
  <div class="mhs-tab-pane" id="tab-obs">
    <div class="mhs-tab-body"><div class="row g-4"><div class="col-md-6">
      <div class="mhs-info-group">
        <div class="mhs-info-group-title"><i class="fa-solid fa-comment"></i> Observações</div>
        <p class="mhs-info-obs">Contrato inclui visitas preventivas e suporte técnico prioritário.</p>
      </div>
    </div></div></div>
  </div>
</div>
<script>document.querySelectorAll('.mhs-detail-tab').forEach(function(b){b.addEventListener('click',function(){document.querySelectorAll('.mhs-detail-tab').forEach(x=>x.classList.remove('active'));document.querySelectorAll('.mhs-tab-pane').forEach(x=>x.classList.remove('active'));b.classList.add('active');document.getElementById('tab-'+b.dataset.tab).classList.add('active');});});</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
