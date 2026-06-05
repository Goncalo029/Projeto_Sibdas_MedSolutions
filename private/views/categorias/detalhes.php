<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();
$page_title = 'Categorias - Detalhes';
include __DIR__ . '/../../includes/header.php';
?>
<div class="mhs-page-header">
  <div><span class="mhs-page-kicker"><i class="fa-solid fa-tags fa-fw"></i></span><h1 class="mhs-page-title">Monitorização</h1></div>
  <div class="mhs-page-actions">
    <a href="editar.php" class="btn btn-outline-primary"><i class="fa-solid fa-pen me-2"></i>Editar</a>
    <a href="lista.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
  </div>
</div>
<div class="card mhs-data-card">
  <div class="mhs-detail-tabs">
    <button class="mhs-detail-tab active" data-tab="info"><i class="fa-solid fa-tags"></i> Informação</button>
    <button class="mhs-detail-tab" data-tab="equipamentos"><i class="fa-solid fa-stethoscope"></i> Equipamentos</button>
  </div>
  <div class="mhs-tab-pane active" id="tab-info">
    <div class="mhs-tab-body"><div class="row g-4"><div class="col-md-5">
      <div class="mhs-info-group">
        <div class="mhs-info-group-title"><i class="fa-solid fa-tags"></i> Dados da categoria</div>
        <dl class="mhs-info-dl">
          <dt>Nome</dt><dd>Monitorização</dd>
          <dt>Descrição</dt><dd>Categoria usada para equipamentos de monitorização clínica.</dd>
          <dt>Estado</dt><dd>Ativa</dd>
        </dl>
      </div>
    </div></div></div>
  </div>
  <div class="mhs-tab-pane" id="tab-equipamentos">
    <div class="mhs-tab-body">
      <table class="table mhs-datatable mb-0"><thead><tr><th>Código</th><th>Designação</th><th>Estado</th></tr></thead>
      <tbody>
        <tr><td><span class="mhs-code">EQ-001</span></td><td class="mhs-td-primary">Monitor multiparamétrico</td><td>Ativo</td></tr>
        <tr><td><span class="mhs-code">EQ-004</span></td><td class="mhs-td-primary">Monitor fetal</td><td>Em calibração</td></tr>
      </tbody></table>
    </div>
  </div>
</div>
<script>document.querySelectorAll('.mhs-detail-tab').forEach(function(b){b.addEventListener('click',function(){document.querySelectorAll('.mhs-detail-tab').forEach(x=>x.classList.remove('active'));document.querySelectorAll('.mhs-tab-pane').forEach(x=>x.classList.remove('active'));b.classList.add('active');document.getElementById('tab-'+b.dataset.tab).classList.add('active');});});</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
