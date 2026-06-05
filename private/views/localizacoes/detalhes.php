<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();
$page_title = 'Localizações - Detalhes';
include __DIR__ . '/../../includes/header.php';
?>
<div class="mhs-page-header">
  <div><span class="mhs-page-kicker"><i class="fa-solid fa-location-dot fa-fw"></i></span><h1 class="mhs-page-title">Urgência / Sala 204</h1></div>
  <div class="mhs-page-actions">
    <a href="editar.php" class="btn btn-outline-primary"><i class="fa-solid fa-pen me-2"></i>Editar</a>
    <a href="lista.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
  </div>
</div>
<div class="mhs-detail-summary card mhs-data-card mb-4">
  <div class="mhs-detail-summary-inner">
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Edifício</span><span class="mhs-detail-summary-val">Bloco Central</span></div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Piso</span><span class="mhs-detail-summary-val">Piso 2</span></div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Serviço</span><span class="mhs-detail-summary-val">Urgência</span></div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Sala</span><span class="mhs-detail-summary-val">Sala 204</span></div>
  </div>
</div>
<div class="card mhs-data-card">
  <div class="mhs-detail-tabs">
    <button class="mhs-detail-tab active" data-tab="info"><i class="fa-solid fa-location-dot"></i> Informação</button>
    <button class="mhs-detail-tab" data-tab="equipamentos"><i class="fa-solid fa-stethoscope"></i> Equipamentos</button>
  </div>
  <div class="mhs-tab-pane active" id="tab-info">
    <div class="mhs-tab-body"><div class="row g-4"><div class="col-md-5">
      <div class="mhs-info-group">
        <div class="mhs-info-group-title"><i class="fa-solid fa-location-dot"></i> Dados da localização</div>
        <dl class="mhs-info-dl">
          <dt>Edifício</dt><dd>Bloco Central</dd>
          <dt>Piso</dt><dd>Piso 2</dd>
          <dt>Serviço</dt><dd>Urgência</dd>
          <dt>Sala</dt><dd>Sala 204</dd>
        </dl>
      </div>
      <div class="mhs-info-group mt-3">
        <div class="mhs-info-group-title"><i class="fa-solid fa-comment"></i> Observações</div>
        <p class="mhs-info-obs">Zona com prioridade para equipamentos críticos.</p>
      </div>
    </div></div></div>
  </div>
  <div class="mhs-tab-pane" id="tab-equipamentos">
    <div class="mhs-tab-body">
      <table class="table mhs-datatable mb-0"><thead><tr><th>Código</th><th>Designação</th><th>Estado</th><th>Criticidade</th></tr></thead>
      <tbody>
        <tr><td><span class="mhs-code">EQ-001</span></td><td class="mhs-td-primary">Monitor multiparamétrico</td><td>Ativo</td><td>Média</td></tr>
        <tr><td><span class="mhs-code">EQ-002</span></td><td class="mhs-td-primary">Bomba de infusão</td><td>Ativo</td><td>Alta</td></tr>
      </tbody></table>
    </div>
  </div>
</div>
<script>document.querySelectorAll('.mhs-detail-tab').forEach(function(b){b.addEventListener('click',function(){document.querySelectorAll('.mhs-detail-tab').forEach(x=>x.classList.remove('active'));document.querySelectorAll('.mhs-tab-pane').forEach(x=>x.classList.remove('active'));b.classList.add('active');document.getElementById('tab-'+b.dataset.tab).classList.add('active');});});</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
