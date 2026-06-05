<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();
$page_title = 'Documentos - Detalhes';
include __DIR__ . '/../../includes/header.php';
?>
<div class="mhs-page-header">
  <div><span class="mhs-page-kicker"><i class="fa-solid fa-file-lines fa-fw"></i></span><h1 class="mhs-page-title">Manual do utilizador</h1></div>
  <div class="mhs-page-actions">
    <a href="editar.php" class="btn btn-outline-primary"><i class="fa-solid fa-pen me-2"></i>Editar</a>
    <a href="lista.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
  </div>
</div>
<div class="mhs-detail-summary card mhs-data-card mb-4">
  <div class="mhs-detail-summary-inner">
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Equipamento</span><span class="mhs-detail-summary-val">EQ-001</span></div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Tipo</span><span class="mhs-detail-summary-val">Manual</span></div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Data</span><span class="mhs-detail-summary-val">12/04/2026</span></div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Estado</span><span class="mhs-detail-summary-val mhs-detail-summary-val--ok">Ativo</span></div>
  </div>
</div>
<div class="card mhs-data-card">
  <div class="mhs-detail-tabs">
    <button class="mhs-detail-tab active" data-tab="info"><i class="fa-solid fa-file-lines"></i> Informação</button>
    <button class="mhs-detail-tab" data-tab="ficheiro"><i class="fa-solid fa-download"></i> Ficheiro</button>
  </div>
  <div class="mhs-tab-pane active" id="tab-info">
    <div class="mhs-tab-body"><div class="row g-4"><div class="col-md-5">
      <div class="mhs-info-group">
        <div class="mhs-info-group-title"><i class="fa-solid fa-file-lines"></i> Dados do documento</div>
        <dl class="mhs-info-dl">
          <dt>Equipamento</dt><dd>EQ-001 — Monitor multiparamétrico</dd>
          <dt>Tipo</dt><dd>Manual</dd>
          <dt>Nome</dt><dd>Manual do utilizador</dd>
          <dt>Data</dt><dd>12/04/2026</dd>
          <dt>Validade</dt><dd>—</dd>
        </dl>
      </div>
    </div></div></div>
  </div>
  <div class="mhs-tab-pane" id="tab-ficheiro">
    <div class="mhs-tab-body"><div class="mhs-info-group" style="max-width:380px">
      <div class="mhs-info-group-title"><i class="fa-solid fa-download"></i> Download</div>
      <a href="#" class="btn btn-primary w-100 mt-2"><i class="fa-solid fa-file-pdf me-2"></i>Descarregar PDF</a>
    </div></div>
  </div>
</div>
<script>document.querySelectorAll('.mhs-detail-tab').forEach(function(b){b.addEventListener('click',function(){document.querySelectorAll('.mhs-detail-tab').forEach(x=>x.classList.remove('active'));document.querySelectorAll('.mhs-tab-pane').forEach(x=>x.classList.remove('active'));b.classList.add('active');document.getElementById('tab-'+b.dataset.tab).classList.add('active');});});</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
